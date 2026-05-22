<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ClassEnrollment extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_id', 'member_id', 'session_date', 'payment_id', 'status', 'cancelled_at', 'cancel_reason'];

    protected function casts(): array
    {
        return ['session_date' => 'date', 'cancelled_at' => 'datetime'];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function attendance(): HasOne
    {
        return $this->hasOne(ClassAttendance::class, 'enrollment_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
