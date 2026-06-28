<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['package_id', 'title', 'slug', 'description', 'starts_at', 'ends_at', 'discount_type', 'discount_value', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'discount_value' => 'decimal:2', 'is_published' => 'boolean'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
