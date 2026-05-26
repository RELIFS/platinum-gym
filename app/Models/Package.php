<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'package_kind', 'type', 'category', 'gender_restriction', 'max_age', 'price', 'promo_price', 'promo_starts_at', 'promo_ends_at', 'duration_days', 'session_count', 'requires_active_membership', 'description', 'benefits', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'promo_price' => 'decimal:2', 'promo_starts_at' => 'datetime', 'promo_ends_at' => 'datetime', 'benefits' => 'array', 'requires_active_membership' => 'boolean', 'is_active' => 'boolean'];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function packageSessions(): HasMany
    {
        return $this->hasMany(MemberPackageSession::class);
    }
}
