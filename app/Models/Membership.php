<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'package_id', 'code', 'start_date', 'end_date', 'price', 'status', 'approved_by', 'approved_at', 'notes'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'price' => 'decimal:2', 'approved_at' => 'datetime'];
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
}
