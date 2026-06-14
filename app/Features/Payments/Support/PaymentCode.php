<?php

namespace App\Features\Payments\Support;

use App\Models\Invoice;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentCode
{
    public static function payment(): string
    {
        return self::unique(Payment::class, 'payment_code', 'PAY');
    }

    public static function midtransOrder(string $paymentCode): string
    {
        return $paymentCode.'-'.now()->format('His');
    }

    public static function membership(): string
    {
        return self::unique(Membership::class, 'code', 'MBR');
    }

    public static function packageSession(): string
    {
        return self::unique(MemberPackageSession::class, 'code', 'MPS');
    }

    public static function invoice(): string
    {
        return self::unique(Invoice::class, 'invoice_number', 'INV');
    }

    private static function unique(string $model, string $column, string $prefix): string
    {
        do {
            $code = $prefix.'-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while ($model::query()->where($column, $code)->exists());

        return $code;
    }
}
