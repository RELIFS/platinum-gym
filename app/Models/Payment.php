<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['payment_code', 'member_id', 'payable_type', 'payable_id', 'method', 'amount', 'status', 'paid_at', 'verified_by', 'verified_at', 'rejected_reason', 'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_payment_type', 'midtrans_snap_token', 'midtrans_redirect_url', 'midtrans_raw_response', 'expires_at', 'failure_reason', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'verified_at' => 'datetime', 'midtrans_raw_response' => 'array', 'expires_at' => 'datetime'];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
