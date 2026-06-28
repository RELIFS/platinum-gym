<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class ImportGymmiKnowledgeCommand extends Command
{
    protected $signature = 'gymmi:import-knowledge
        {source? : Path workbook data_AI_Chatbot.xlsx}
        {--output= : Output JSON path}';

    protected $description = 'Import dan validasi knowledge base Gymmi dari workbook resmi.';

    /** @var array<string, string> */
    private const CATALOG_SHEETS = [
        'Membership' => 'membership',
        'Fasilitas' => 'fasilitas',
        'Alat_Gym' => 'alat_gym',
        'Coach' => 'coach',
        'Personal_Trainer' => 'personal_trainer',
        'Class_Senam' => 'class_senam',
        'Class_Terpisah' => 'class_terpisah',
        'Makanan' => 'makanan',
        'Minuman' => 'minuman',
        'Produk_Lainnya' => 'produk_lainnya',
        'Kebijakan' => 'kebijakan',
    ];

    public function handle(): int
    {
        $source = (string) ($this->argument('source') ?: config('gymmi.knowledge_source_path'));
        $output = (string) ($this->option('output') ?: config('gymmi.knowledge_base_path'));

        if (! is_file($source)) {
            $this->error('Workbook Gymmi tidak ditemukan: '.$source);

            return self::FAILURE;
        }

        $spreadsheet = IOFactory::createReaderForFile($source);
        $spreadsheet->setReadDataOnly(true);
        $document = $spreadsheet->load($source);

        $payload = [
            'metadata' => [
                'available' => true,
                'source' => basename($source),
                'source_sha256' => hash_file('sha256', $source),
                'source_modified_at' => date(DATE_ATOM, (int) filemtime($source)),
                'imported_at' => now()->toAtomString(),
            ],
            'config' => $this->configRows($this->sheet($document, 'Config')),
            'faq' => $this->faqRows($this->sheet($document, 'FAQ')),
            'aliases' => $this->aliasRows($this->sheet($document, 'Alias')),
            'catalog' => [],
            'validation' => $this->validationRows($this->sheet($document, 'Validasi_Data')),
        ];

        foreach (self::CATALOG_SHEETS as $sheet => $key) {
            $payload['catalog'][$key] = $this->activeRows($this->sheet($document, $sheet), ['Tersedia', 'Aktif']);
        }

        $payload['metadata']['counts'] = [
            'faq' => count($payload['faq']),
            'aliases' => count($payload['aliases']),
            'config' => count($payload['config']),
            'catalog' => collect($payload['catalog'])->map(fn (array $rows): int => count($rows))->all(),
            'validation' => count($payload['validation']),
        ];

        $directory = dirname($output);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Tidak bisa membuat folder output Gymmi: '.$directory);
        }

        file_put_contents(
            $output,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL
        );

        $this->info('Knowledge base Gymmi berhasil diimport.');
        $this->line('Output: '.$output);
        $this->line('FAQ: '.count($payload['faq']).' | Alias: '.count($payload['aliases']));

        return self::SUCCESS;
    }

    private function sheet(Spreadsheet $document, string $name): Worksheet
    {
        $sheet = $document->getSheetByName($name);

        if (! $sheet) {
            throw new RuntimeException('Sheet wajib tidak ditemukan: '.$name);
        }

        return $sheet;
    }

    /**
     * @param  array<int, string>  $allowedStatuses
     * @return array<int, array<string, string>>
     */
    private function activeRows(Worksheet $sheet, array $allowedStatuses): array
    {
        return collect($this->rows($sheet))
            ->filter(function (array $row) use ($allowedStatuses): bool {
                $status = Str::lower((string) ($row['status'] ?? ''));

                return in_array($status, collect($allowedStatuses)->map(fn (string $value): string => Str::lower($value))->all(), true);
            })
            ->map(fn (array $row): array => $this->withoutEmpty($row))
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{label: string, value: string}>
     */
    private function configRows(Worksheet $sheet): array
    {
        return collect($this->rows($sheet))
            ->mapWithKeys(function (array $row): array {
                $label = trim((string) ($row['config_key'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));

                if ($label === '' || $value === '' || Str::lower($value) === 'tidak tersedia') {
                    return [];
                }

                return [$this->key($label) => ['label' => $label, 'value' => $value]];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function faqRows(Worksheet $sheet): array
    {
        return collect($this->activeRows($sheet, ['Aktif']))
            ->filter(fn (array $row): bool => filled($row['pertanyaan'] ?? null) && filled($row['jawaban'] ?? null))
            ->map(fn (array $row): array => [
                'id' => (string) ($row['id'] ?? ''),
                'category' => (string) ($row['kategori'] ?? ''),
                'question' => (string) ($row['pertanyaan'] ?? ''),
                'answer' => (string) ($row['jawaban'] ?? ''),
                'source_sheet' => (string) ($row['sumber_sheet'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function aliasRows(Worksheet $sheet): array
    {
        return collect($this->activeRows($sheet, ['Aktif']))
            ->filter(fn (array $row): bool => filled($row['kata_user'] ?? null) && filled($row['maksud'] ?? null))
            ->unique(fn (array $row): string => Str::lower((string) $row['kata_user']))
            ->map(fn (array $row): array => [
                'id' => (string) ($row['id'] ?? ''),
                'category' => (string) ($row['kategori'] ?? ''),
                'phrase' => (string) ($row['kata_user'] ?? ''),
                'intent' => (string) ($row['maksud'] ?? ''),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function validationRows(Worksheet $sheet): array
    {
        return collect($this->activeRows($sheet, ['Selesai']))
            ->map(fn (array $row): array => $this->withoutEmpty($row))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function rows(Worksheet $sheet): array
    {
        $data = $sheet->toArray('', true, true, true);
        $headers = array_map(fn (string $header): string => $this->key($header), array_shift($data) ?: []);

        return collect($data)
            ->map(function (array $row) use ($headers): array {
                $mapped = [];

                foreach ($headers as $column => $header) {
                    if ($header === '') {
                        continue;
                    }

                    $mapped[$header] = trim((string) ($row[$column] ?? ''));
                }

                return $mapped;
            })
            ->filter(fn (array $row): bool => collect($row)->filter(fn (string $value): bool => $value !== '')->isNotEmpty())
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    private function withoutEmpty(array $row): array
    {
        return collect($row)
            ->reject(fn (string $value): bool => $value === '')
            ->all();
    }

    private function key(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }
}
