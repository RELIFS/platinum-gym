<?php

namespace App\Console\Commands;

use App\Features\Gymmi\Support\GeminiApiKeyPool;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

class SyncGeminiKeysCommand extends Command
{
    protected $signature = 'gymmi:sync-gemini-keys
        {source? : Path file private berisi Gemini API keys}
        {--write-env : Tulis GEMINI_API_KEYS ke .env lokal}
        {--status : Tampilkan status key pool dari config aktif}
        {--env= : Path .env target untuk write-env}
        {--force : Izinkan write-env saat APP_ENV=production}';

    protected $description = 'Validasi dan sinkronisasi Gemini API keys Gymmi tanpa mencetak nilai key.';

    public function __construct(
        private readonly GeminiApiKeyPool $keyPool,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('status')) {
            return $this->showStatus();
        }

        $source = $this->sourcePath();

        if (! is_file($source)) {
            $this->error('File key Gemini tidak ditemukan.');
            $this->line('Path: '.$source);

            return self::FAILURE;
        }

        $result = $this->parseKeyFile($source);
        $this->printParseSummary($result);

        if ($result['valid'] === []) {
            $this->error('Tidak ada Gemini API key valid yang bisa disinkronkan.');

            return self::FAILURE;
        }

        if (! $this->option('write-env')) {
            $this->line('Dry-run aktif. Tidak ada file yang diubah.');

            return self::SUCCESS;
        }

        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('APP_ENV=production terdeteksi. Gunakan secret manager/hosting env UI, atau jalankan dengan --force jika benar-benar perlu.');

            return self::FAILURE;
        }

        $this->writeEnv($this->envPath(), $result['valid']);
        $this->info('GEMINI_API_KEYS berhasil disimpan ke env target.');
        $this->line('Nilai key tidak ditampilkan di output.');

        return self::SUCCESS;
    }

    private function showStatus(): int
    {
        $status = $this->keyPool->status($this->model());

        $this->info('Status Gemini key pool Gymmi');
        $this->line('Configured keys: '.$status['configured']);
        $this->line('Available keys: '.$status['available']);
        $this->line('Invalid temporary: '.$status['invalid']);
        $this->line('Cooldown: '.$status['cooldown']);
        $this->line('Max attempts per request: '.$status['max_attempts']);
        $this->line('Model: '.$status['model']);
        $this->line('Model circuit open: '.($status['model_circuit_open'] ? 'yes' : 'no'));

        return self::SUCCESS;
    }

    private function sourcePath(): string
    {
        return (string) ($this->argument('source') ?: base_path('platinumgym-figma/docs/source-data/Gymmi API Key.txt'));
    }

    private function envPath(): string
    {
        return (string) ($this->option('env') ?: base_path('.env'));
    }

    private function model(): string
    {
        return Str::of((string) config('services.gemini.model', 'gemini-2.0-flash'))
            ->after('models/')
            ->trim()
            ->toString();
    }

    /**
     * @return array{valid: array<int, string>, total: int, blank: int, duplicate: int, invalid: int, fingerprint: string}
     */
    private function parseKeyFile(string $source): array
    {
        $contents = file_get_contents($source);

        if ($contents === false) {
            throw new RuntimeException('Tidak bisa membaca file key Gemini.');
        }

        $tokens = preg_split('/[\r\n,\s]+/', $contents) ?: [];
        $valid = [];
        $seen = [];
        $blank = 0;
        $duplicate = 0;
        $invalid = 0;
        $total = 0;

        foreach ($tokens as $token) {
            $candidate = $this->normalizeToken($token);

            if ($candidate === '') {
                $blank++;

                continue;
            }

            $total++;

            if (! $this->isGeminiKeyLike($candidate)) {
                $invalid++;

                continue;
            }

            $fingerprint = hash('sha256', $candidate);

            if (isset($seen[$fingerprint])) {
                $duplicate++;

                continue;
            }

            $seen[$fingerprint] = true;
            $valid[] = $candidate;
        }

        return [
            'valid' => $valid,
            'total' => $total,
            'blank' => $blank,
            'duplicate' => $duplicate,
            'invalid' => $invalid,
            'fingerprint' => hash('sha256', implode('|', array_map(fn (string $key): string => hash('sha256', $key), $valid))),
        ];
    }

    private function normalizeToken(string $token): string
    {
        $token = trim($token, " \t\n\r\0\x0B\"'");

        if (str_contains($token, '=')) {
            $token = trim((string) Str::of($token)->after('='), " \t\n\r\0\x0B\"'");
        }

        return $token;
    }

    private function isGeminiKeyLike(string $value): bool
    {
        return preg_match('/^AIza[0-9A-Za-z_-]{30,}$/', $value) === 1;
    }

    /**
     * @param  array{valid: array<int, string>, total: int, blank: int, duplicate: int, invalid: int, fingerprint: string}  $result
     */
    private function printParseSummary(array $result): void
    {
        $this->info('Validasi Gemini API keys selesai.');
        $this->line('Input tokens: '.$result['total']);
        $this->line('Valid unique keys: '.count($result['valid']));
        $this->line('Duplicate keys: '.$result['duplicate']);
        $this->line('Invalid tokens: '.$result['invalid']);
        $this->line('Blank tokens: '.$result['blank']);
        $this->line('Safe fingerprint: '.substr($result['fingerprint'], 0, 16));
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function writeEnv(string $path, array $keys): void
    {
        $contents = is_file($path) ? file_get_contents($path) : '';

        if ($contents === false) {
            throw new RuntimeException('Tidak bisa membaca env target.');
        }

        $line = 'GEMINI_API_KEYS="'.$this->escapeEnvValue(implode(',', $keys)).'"';

        if (preg_match('/^GEMINI_API_KEYS=.*$/m', $contents) === 1) {
            $contents = preg_replace('/^GEMINI_API_KEYS=.*$/m', $line, $contents);

            if ($contents === null) {
                throw new RuntimeException('Tidak bisa memperbarui env target.');
            }
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Tidak bisa menulis env target.');
        }
    }

    private function escapeEnvValue(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
