<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'class_type', 'access_type', 'required_package_type', 'capacity', 'member_price', 'non_member_price', 'promo_price', 'is_active'];

    protected function casts(): array
    {
        return ['member_price' => 'decimal:2', 'non_member_price' => 'decimal:2', 'promo_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
