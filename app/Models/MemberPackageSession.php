<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MemberPackageSession extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'package_id', 'trainer_id', 'code', 'total_sessions', 'used_sessions', 'remaining_sessions', 'price', 'started_at', 'expired_at', 'status', 'approved_by', 'approved_at', 'notes'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'started_at' => 'date', 'expired_at' => 'date', 'approved_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
