<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['member_id', 'name', 'role', 'content', 'rating', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
