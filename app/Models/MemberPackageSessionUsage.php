<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPackageSessionUsage extends Model
{
    use HasFactory;

    protected $fillable = ['member_package_session_id', 'member_id', 'gym_check_in_id', 'class_enrollment_id', 'usage_date', 'used_at', 'method', 'recorded_by', 'request_key', 'notes'];

    protected function casts(): array
    {
        return ['usage_date' => 'date', 'used_at' => 'datetime'];
    }

    public function packageSession(): BelongsTo
    {
        return $this->belongsTo(MemberPackageSession::class, 'member_package_session_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function gymCheckIn(): BelongsTo
    {
        return $this->belongsTo(GymCheckIn::class);
    }

    public function classEnrollment(): BelongsTo
    {
        return $this->belongsTo(ClassEnrollment::class, 'class_enrollment_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
