<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'package_kind', 'type', 'category', 'gender_restriction', 'max_age', 'price', 'promo_price', 'promo_starts_at', 'promo_ends_at', 'duration_days', 'base_duration_days', 'bonus_duration_days', 'bonus_label', 'session_count', 'requires_active_membership', 'description', 'benefits', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'promo_price' => 'decimal:2', 'promo_starts_at' => 'datetime', 'promo_ends_at' => 'datetime', 'duration_days' => 'integer', 'base_duration_days' => 'integer', 'bonus_duration_days' => 'integer', 'benefits' => 'array', 'requires_active_membership' => 'boolean', 'is_active' => 'boolean'];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function packageSessions(): HasMany
    {
        return $this->hasMany(MemberPackageSession::class);
    }

    public function promos(): HasMany
    {
        return $this->hasMany(Promo::class);
    }

    public function effectiveDurationDays(): ?int
    {
        $baseDuration = $this->baseDurationDays();

        if ($baseDuration === null) {
            return filled($this->duration_days) ? max((int) $this->duration_days, 1) : null;
        }

        return max($baseDuration + $this->bonusDurationDays(), 1);
    }

    public function baseDurationDays(): ?int
    {
        if (filled($this->base_duration_days)) {
            return max((int) $this->base_duration_days, 1);
        }

        if (filled($this->duration_days) && $this->bonusDurationDays() > 0) {
            return max((int) $this->duration_days - $this->bonusDurationDays(), 1);
        }

        return filled($this->duration_days) ? max((int) $this->duration_days, 1) : null;
    }

    public function bonusDurationDays(): int
    {
        return max((int) ($this->bonus_duration_days ?? 0), 0);
    }

    public function durationBonusLabel(): ?string
    {
        $bonusDuration = $this->bonusDurationDays();

        if ($bonusDuration < 1) {
            return null;
        }

        if (filled($this->bonus_label)) {
            return (string) $this->bonus_label;
        }

        return 'Gratis '.$this->humanBonusDuration($bonusDuration);
    }

    public function durationMarketingLabel(): ?string
    {
        $effectiveDuration = $this->effectiveDurationDays();

        if ($effectiveDuration === null) {
            return null;
        }

        $bonusLabel = $this->durationBonusLabel();
        if ($bonusLabel) {
            return $this->humanBaseDuration($this->baseDurationDays() ?? $effectiveDuration).' + '.str($bonusLabel)->lower()->toString();
        }

        return $effectiveDuration.' hari';
    }

    private function humanBaseDuration(int $days): string
    {
        if ($days % 30 === 0) {
            return ($days / 30).' bulan';
        }

        return $days.' hari';
    }

    private function humanBonusDuration(int $days): string
    {
        if ($days % 30 === 0) {
            return ($days / 30).' bulan';
        }

        return $days.' hari';
    }
}
