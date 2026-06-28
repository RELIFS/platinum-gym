<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymCheckIn extends Model
{
    use HasFactory;

    protected $fillable = ['member_id', 'membership_id', 'check_in_date', 'check_in_at', 'check_out_at', 'method', 'scanned_by'];

    protected function casts(): array
    {
        return ['check_in_date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function packageSessionUsages(): HasMany
    {
        return $this->hasMany(MemberPackageSessionUsage::class);
    }
}
