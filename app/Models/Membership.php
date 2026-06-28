<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'package_id', 'code', 'start_date', 'end_date', 'price', 'duration_days_snapshot', 'status', 'activated_at', 'approved_by', 'approved_at', 'notes'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'duration_days_snapshot' => 'integer',
            'activated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function scopeStartedAndCurrent(Builder $query, CarbonInterface|string|null $date = null): Builder
    {
        $date = $this->scopeDate($date);

        return $query
            ->where('status', 'active')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);
    }

    public function scopeAwaitingFirstCheckIn(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->whereNull('start_date')
            ->whereNull('end_date');
    }

    public function scopeActiveForAccess(Builder $query, CarbonInterface|string|null $date = null): Builder
    {
        $date = $this->scopeDate($date);

        return $query
            ->where('status', 'active')
            ->where(function (Builder $query) use ($date): void {
                $query
                    ->where(function (Builder $query) use ($date): void {
                        $query
                            ->whereNotNull('start_date')
                            ->whereNotNull('end_date')
                            ->whereDate('start_date', '<=', $date)
                            ->whereDate('end_date', '>=', $date);
                    })
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('start_date')->whereNull('end_date');
                    });
            });
    }

    public function startDurationOn(CarbonInterface|string|null $date = null): self
    {
        if ($this->start_date && $this->end_date) {
            return $this;
        }

        $date = CarbonImmutable::parse($date ?? now())->startOfDay();
        $durationDays = $this->durationDaysForActivation();

        $this->forceFill([
            'start_date' => $date->toDateString(),
            'end_date' => $date->addDays($durationDays - 1)->toDateString(),
        ])->save();

        return $this;
    }

    public function isAwaitingFirstCheckIn(): bool
    {
        return $this->status === 'active' && ! $this->start_date && ! $this->end_date;
    }

    public function validityLabel(): string
    {
        if ($this->isAwaitingFirstCheckIn()) {
            return 'Mulai saat check-in pertama';
        }

        if ($this->start_date && $this->end_date) {
            return 'Berlaku '.$this->start_date->translatedFormat('d M Y').' sampai '.$this->end_date->translatedFormat('d M Y');
        }

        if ($this->end_date) {
            return 'Aktif sampai '.$this->end_date->translatedFormat('d M Y');
        }

        return 'Masa aktif belum tersedia';
    }

    private function scopeDate(CarbonInterface|string|null $date): string
    {
        return CarbonImmutable::parse($date ?? now())->toDateString();
    }

    private function durationDaysForActivation(): int
    {
        return max((int) ($this->duration_days_snapshot ?: $this->package?->duration_days ?: 30), 1);
    }
}
