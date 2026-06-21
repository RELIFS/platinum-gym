<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'member_code', 'gender', 'birth_date', 'address', 'emergency_contact', 'is_student', 'student_id_number', 'student_verification_status', 'student_verified_at', 'student_verification_source', 'student_verification_note', 'joined_at', 'status'];

    protected $attributes = [
        'student_verification_status' => 'unverified',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'is_student' => 'boolean', 'student_verified_at' => 'datetime', 'joined_at' => 'date'];
    }

    public static function generateMemberCode(): string
    {
        $prefix = 'PG-'.now()->format('Ymd').'-';
        $sequence = static::withTrashed()
            ->where('member_code', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $code = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::withTrashed()->where('member_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function packageSessions(): HasMany
    {
        return $this->hasMany(MemberPackageSession::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function classEnrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    public function classAttendances(): HasMany
    {
        return $this->hasMany(ClassAttendance::class);
    }

    public function gymCheckIns(): HasMany
    {
        return $this->hasMany(GymCheckIn::class);
    }

    public function packageSessionUsages(): HasMany
    {
        return $this->hasMany(MemberPackageSessionUsage::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
}
