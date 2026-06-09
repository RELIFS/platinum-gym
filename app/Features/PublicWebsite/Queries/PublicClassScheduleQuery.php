<?php

namespace App\Features\PublicWebsite\Queries;

use App\Models\ClassSchedule;
use App\Models\GymClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicClassScheduleQuery
{
    public const DAY_OPTIONS = [
        'senin' => 1,
        'selasa' => 2,
        'rabu' => 3,
        'kamis' => 4,
        'jumat' => 5,
        'sabtu' => 6,
        'minggu' => 7,
    ];

    public const DAY_LABELS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function __construct(private readonly PublicSettingsQuery $settings) {}

    public function forIndex(Request $request): array
    {
        $selectedDay = $this->selectedDay($request);
        $classTypeOptions = $this->classTypeOptions();
        $selectedType = $this->selectedClassType($request, $classTypeOptions->keys()->all());

        $schedules = $this->activeSchedulesQuery()
            ->when($selectedDay, fn (Builder $query) => $query->where('day_of_week', self::DAY_OPTIONS[$selectedDay]))
            ->when($selectedType, function (Builder $query) use ($selectedType): void {
                $query->whereHas('gymClass', fn (Builder $classQuery) => $classQuery->where('class_type', $selectedType));
            })
            ->get();

        return [
            'settings' => $this->settings->get(),
            'schedules' => $schedules,
            'dayOptions' => self::DAY_OPTIONS,
            'dayLabels' => self::DAY_LABELS,
            'classTypeOptions' => $classTypeOptions,
            'selectedDay' => $selectedDay,
            'selectedType' => $selectedType,
        ];
    }

    public function preview(int $limit = 8): Collection
    {
        return $this->activeSchedulesQuery()->limit($limit)->get();
    }

    public function dayLabels(): array
    {
        return self::DAY_LABELS;
    }

    private function activeSchedulesQuery(): Builder
    {
        return ClassSchedule::query()
            ->with(['gymClass', 'trainer'])
            ->where('is_active', true)
            ->whereHas('gymClass', fn (Builder $query) => $query->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('start_time');
    }

    private function selectedDay(Request $request): ?string
    {
        $day = Str::lower((string) $request->query('hari'));

        return array_key_exists($day, self::DAY_OPTIONS) ? $day : null;
    }

    private function selectedClassType(Request $request, array $allowedTypes): ?string
    {
        $type = Str::lower((string) $request->query('jenis'));

        return in_array($type, $allowedTypes, true) ? $type : null;
    }

    private function classTypeOptions(): Collection
    {
        return GymClass::query()
            ->where('is_active', true)
            ->distinct()
            ->orderBy('class_type')
            ->pluck('class_type')
            ->filter()
            ->mapWithKeys(fn ($type) => [$type => Str::headline((string) $type)]);
    }
}
