<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['gym_class_id', 'trainer_id', 'day_of_week', 'start_time', 'end_time', 'room', 'capacity', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'schedule_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class, 'schedule_id');
    }
}
