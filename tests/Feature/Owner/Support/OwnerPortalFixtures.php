<?php

namespace Tests\Feature\Owner\Support;

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\QrToken;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OwnerPortalFixtures
{
    public const SENSITIVE_SNAP_TOKEN = 'owner-secret-snap-token';

    public const SENSITIVE_REDIRECT_URL = 'https://payment.example.test/owner-secret';

    public const SENSITIVE_RAW_VALUE = 'owner-raw-gateway-secret';

    public const SENSITIVE_QR_TOKEN = 'owner-raw-qr-token-secret';

    public const SENSITIVE_INTERNAL_NOTE = 'Catatan internal owner';

    public static function owner(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Owner QA',
            'email' => 'owner.qa.'.Str::lower(Str::random(8)).'@example.test',
        ], $overrides));

        $user->assignRole('owner');

        return $user;
    }

    public static function roleUser(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'email' => $role.'.owner.qa.'.Str::lower(Str::random(8)).'@example.test',
        ], $overrides));

        $user->assignRole($role);

        return $user;
    }

    public static function revokeOwnerPermission(string $permission): void
    {
        Role::findByName('owner')->revokePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: User, 1: Member} */
    public static function member(?string $code = null, array $userOverrides = [], array $memberOverrides = []): array
    {
        $code ??= 'PG-OWN-'.Str::upper(Str::random(8));

        $user = User::factory()->create(array_merge([
            'name' => 'Member Owner QA '.$code,
            'email' => Str::lower($code).'@example.test',
            'phone' => '08'.fake()->numerify('##########'),
        ], $userOverrides));
        $user->assignRole('member');

        $member = Member::create(array_merge([
            'user_id' => $user->id,
            'member_code' => $code,
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'joined_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ], $memberOverrides));

        return [$user, $member];
    }

    public static function package(array $overrides = []): ServicePackage
    {
        $name = $overrides['name'] ?? 'Paket Owner QA '.Str::upper(Str::random(5));

        return ServicePackage::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'package_kind' => 'membership',
            'type' => 'gym',
            'category' => 'umum',
            'gender_restriction' => 'all',
            'price' => 250000,
            'duration_days' => 30,
            'is_active' => true,
        ], $overrides));
    }

    public static function membership(Member $member, ?ServicePackage $package = null, array $overrides = []): Membership
    {
        $package ??= self::package();

        return Membership::create(array_merge([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'code' => 'MBR-OWN-'.Str::upper(Str::random(8)),
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price' => $package->price,
            'status' => 'active',
        ], $overrides));
    }

    public static function payment(Member $member, ?object $payable = null, array $overrides = []): Payment
    {
        $payable ??= self::membership($member);

        return Payment::create(array_merge([
            'payment_code' => 'PAY-OWN-'.Str::upper(Str::random(8)),
            'member_id' => $member->id,
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
            'method' => 'cash',
            'amount' => 250000,
            'status' => 'paid',
            'paid_at' => now(),
        ], $overrides));
    }

    public static function sensitivePayment(Member $member): Payment
    {
        $payment = self::payment($member, null, [
            'payment_code' => 'PAY-OWN-SENSITIVE-'.Str::upper(Str::random(5)),
            'midtrans_snap_token' => self::SENSITIVE_SNAP_TOKEN,
            'midtrans_redirect_url' => self::SENSITIVE_REDIRECT_URL,
            'midtrans_raw_response' => ['token' => self::SENSITIVE_RAW_VALUE],
            'note' => self::SENSITIVE_INTERNAL_NOTE,
        ]);

        QrToken::create([
            'tokenable_type' => Member::class,
            'tokenable_id' => $member->id,
            'token' => self::SENSITIVE_QR_TOKEN,
            'purpose' => 'member',
            'is_revoked' => false,
        ]);

        return $payment;
    }

    public static function invoice(Payment $payment, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'payment_id' => $payment->id,
            'invoice_number' => 'INV-OWN-'.Str::upper(Str::random(8)),
            'issued_at' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'subtotal' => $payment->amount,
            'discount' => 0,
            'tax' => 0,
            'total' => $payment->amount,
            'status' => $payment->status === 'paid' ? 'paid' : 'issued',
        ], $overrides));
    }

    public static function invoiceForMember(Member $member): Invoice
    {
        $package = self::package([
            'name' => 'Owner Invoice Package',
            'slug' => 'owner-invoice-package-'.$member->id,
            'price' => 180000,
        ]);

        $membership = self::membership($member, $package, [
            'code' => 'MBR-OWNER-INVOICE-'.$member->id,
            'price' => 180000,
        ]);

        $payment = self::payment($member, $membership, [
            'payment_code' => 'PAY-OWNER-INVOICE-'.$member->id,
            'amount' => 180000,
        ]);

        return self::invoice($payment, [
            'invoice_number' => 'INV-OWNER-INVOICE-'.$member->id,
            'subtotal' => 180000,
            'total' => 180000,
        ]);
    }

    public static function assertNavigationActive(TestResponse $response, string $route): void
    {
        $content = $response->getContent();

        expect(substr_count($content, 'data-owner-nav-active="true"'))->toBe(2);
        expect(preg_match_all('/data-owner-nav-route="'.preg_quote($route, '/').'"[^>]*data-owner-nav-active="true"/', $content))->toBe(2);
    }

    /** @return array{0: GymClass, 1: ClassSchedule} */
    public static function schedule(?string $sessionDate = null, array $classOverrides = [], array $scheduleOverrides = []): array
    {
        $date = CarbonImmutable::parse($sessionDate ?? now()->toDateString());
        $name = $classOverrides['name'] ?? 'Kelas Owner QA '.Str::upper(Str::random(5));

        $gymClass = GymClass::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'class_type' => 'senam',
            'access_type' => 'included',
            'required_package_type' => 'senam',
            'capacity' => 20,
            'is_active' => true,
        ], $classOverrides));

        $schedule = ClassSchedule::create(array_merge([
            'gym_class_id' => $gymClass->id,
            'day_of_week' => $date->dayOfWeekIso,
            'start_time' => '17:00:00',
            'end_time' => '18:00:00',
            'capacity' => 20,
            'is_active' => true,
        ], $scheduleOverrides));

        return [$gymClass, $schedule];
    }

    public static function enrollment(Member $member, ?ClassSchedule $schedule = null, array $overrides = []): ClassEnrollment
    {
        $date = $overrides['session_date'] ?? now()->toDateString();
        [, $schedule] = $schedule ? [null, $schedule] : self::schedule($date);

        return ClassEnrollment::create(array_merge([
            'schedule_id' => $schedule->id,
            'member_id' => $member->id,
            'session_date' => $date,
            'status' => 'booked',
        ], $overrides));
    }
}
