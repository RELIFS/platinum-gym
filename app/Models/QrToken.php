<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QrToken extends Model
{
    use HasFactory;

    protected $fillable = ['tokenable_type', 'tokenable_id', 'token', 'purpose', 'expires_at', 'last_used_at', 'is_revoked', 'created_by'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'last_used_at' => 'datetime', 'is_revoked' => 'boolean'];
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
