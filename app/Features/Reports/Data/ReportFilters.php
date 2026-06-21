<?php

namespace App\Features\Reports\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

final class ReportFilters
{
    public const TYPES = [
        'finance' => 'Keuangan',
        'members' => 'Member & Membership',
        'classes' => 'Booking & Kelas',
    ];

    public function __construct(
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly string $reportType,
        public readonly string $status = '',
        public readonly string $method = '',
        public readonly ?int $packageId = null,
        public readonly ?int $classId = null,
        public readonly string $search = '',
    ) {}

    public static function fromRequest(Request $request, string $defaultType = 'finance'): self
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'report_type' => ['nullable', Rule::in(array_keys(self::TYPES))],
            'status' => ['nullable', 'string', 'max:40'],
            'method' => ['nullable', 'string', 'max:40'],
            'package_id' => ['nullable', 'integer', 'min:1'],
            'class_id' => ['nullable', 'integer', 'min:1'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $from = filled($validated['date_from'] ?? null)
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->startOfMonth();
        $to = filled($validated['date_to'] ?? null)
            ? Carbon::parse($validated['date_to'])->startOfDay()
            : now()->startOfDay();

        if ($from->diffInDays($to) > 366) {
            $to = $from->copy()->addDays(366);
        }

        $reportType = (string) ($validated['report_type'] ?? $defaultType);
        if (! array_key_exists($reportType, self::TYPES)) {
            $reportType = array_key_exists($defaultType, self::TYPES) ? $defaultType : 'finance';
        }

        return new self(
            from: $from,
            to: $to,
            reportType: $reportType,
            status: str((string) ($validated['status'] ?? ''))->squish()->limit(40, '')->toString(),
            method: str((string) ($validated['method'] ?? ''))->squish()->limit(40, '')->toString(),
            packageId: filled($validated['package_id'] ?? null) ? (int) $validated['package_id'] : null,
            classId: filled($validated['class_id'] ?? null) ? (int) $validated['class_id'] : null,
            search: str((string) ($validated['q'] ?? ''))->squish()->limit(100, '')->toString(),
        );
    }

    /** @return array<string, string> */
    public function query(): array
    {
        return collect([
            'date_from' => $this->from->toDateString(),
            'date_to' => $this->to->toDateString(),
            'report_type' => $this->reportType,
            'status' => $this->status,
            'method' => $this->method,
            'package_id' => $this->packageId ? (string) $this->packageId : '',
            'class_id' => $this->classId ? (string) $this->classId : '',
            'q' => $this->search,
        ])->filter(fn (string $value): bool => filled($value))->all();
    }

    public function periodLabel(): string
    {
        return $this->from->translatedFormat('d M Y').' - '.$this->to->translatedFormat('d M Y');
    }
}
