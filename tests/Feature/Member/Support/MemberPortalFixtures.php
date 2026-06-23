<?php

namespace Tests\Feature\Member\Support;

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
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class MemberPortalFixtures
{
    /**
     * @return array{0: User, 1: Member}
     */
    public static function member(string $code, array $userOverrides = [], array $memberOverrides = []): array
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Member '.$code,
            'email' => Str::lower($code).'@example.test',
            'phone' => '08'.fake()->numerify('##########'),
            'avatar' => 'storage/member/avatars/'.$code.'.jpg',
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

    /**
     * @return array{0: User, 1: Member}
     */
    public static function portalMember(string $code = 'PG-PORTAL-0001', ?string $email = null, ?string $phone = null): array
    {
        $user = User::factory()->create([
            'name' => 'Andi Portal',
            'email' => $email ?? 'andi.portal@example.com',
            'phone' => $phone ?? '081234567890',
        ]);
        $user->assignRole('member');

        $member = Member::create([
            'user_id' => $user->id,
            'member_code' => $code,
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'joined_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        return [$user, $member];
    }

    public static function makeCheckoutEligible(User $user): void
    {
        $user->forceFill(['avatar' => 'storage/member/avatars/test-avatar.jpg'])->save();
    }

    /**
     * @return array{0: User, 1: Member}
     */
    public static function improvementsMember(string $code, ?string $email = null, ?string $phone = null): array
    {
        return self::portalMember(
            $code,
            $email ?? ('member.improvements.'.Str::lower(Str::random(6)).'@example.com'),
            $phone ?? ('0812'.random_int(10000000, 99999999))
        );
    }

    public static function incompleteMember(string $email = 'incomplete.member@example.test'): User
    {
        $user = User::factory()->create([
            'name' => 'Member Belum Lengkap',
            'email' => $email,
            'phone' => '081299990001',
        ]);
        $user->assignRole('member');

        return $user;
    }

    public static function roleUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public static function package(array $overrides = []): ServicePackage
    {
        $name = $overrides['name'] ?? 'Paket Member '.Str::upper(Str::random(5));

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

    public static function activeMembership(Member $member, ?ServicePackage $package = null, array $overrides = []): Membership
    {
        $package ??= self::package();

        return Membership::create(array_merge([
            'member_id' => $member->id,
            'package_id' => $package->id,
            'code' => 'MBR-'.Str::upper(Str::random(8)),
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price' => $package->price,
            'status' => 'active',
        ], $overrides));
    }

    public static function payment(Member $member, ?object $payable = null, array $overrides = []): Payment
    {
        $payable ??= self::activeMembership($member);

        return Payment::create(array_merge([
            'payment_code' => 'PAY-'.Str::upper(Str::random(10)),
            'member_id' => $member->id,
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
            'method' => 'midtrans',
            'amount' => 250000,
            'status' => 'paid',
            'paid_at' => now(),
        ], $overrides));
    }

    public static function sensitivePayment(Member $member): Payment
    {
        return self::payment($member, null, [
            'payment_code' => 'PAY-SENSITIVE-'.Str::upper(Str::random(5)),
            'midtrans_snap_token' => 'secret-snap-token-member',
            'midtrans_redirect_url' => 'https://payment.example.test/member-secret',
            'midtrans_raw_response' => ['token' => 'raw-secret-member'],
            'note' => 'Catatan internal member',
        ]);
    }

    public static function invoice(Payment $payment, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'payment_id' => $payment->id,
            'invoice_number' => 'INV-'.Str::upper(Str::random(10)),
            'issued_at' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'subtotal' => $payment->amount,
            'discount' => 0,
            'tax' => 0,
            'total' => $payment->amount,
            'status' => 'paid',
        ], $overrides));
    }

    public static function schedule(array $classOverrides = [], array $scheduleOverrides = []): ClassSchedule
    {
        $className = $classOverrides['name'] ?? 'Kelas Member '.Str::upper(Str::random(5));
        $sessionDate = CarbonImmutable::today()->next(CarbonImmutable::MONDAY);

        $gymClass = GymClass::create(array_merge([
            'name' => $className,
            'slug' => Str::slug($className).'-'.Str::lower(Str::random(6)),
            'class_type' => 'senam',
            'access_type' => 'included',
            'required_package_type' => 'senam',
            'capacity' => 20,
            'is_active' => true,
        ], $classOverrides));

        return ClassSchedule::create(array_merge([
            'gym_class_id' => $gymClass->id,
            'day_of_week' => $sessionDate->dayOfWeekIso,
            'start_time' => '17:00:00',
            'end_time' => '18:00:00',
            'capacity' => 20,
            'is_active' => true,
        ], $scheduleOverrides));
    }

    public static function enrollment(Member $member, ?ClassSchedule $schedule = null, array $overrides = []): ClassEnrollment
    {
        $schedule ??= self::schedule();

        return ClassEnrollment::create(array_merge([
            'schedule_id' => $schedule->id,
            'member_id' => $member->id,
            'session_date' => CarbonImmutable::today()->next(CarbonImmutable::MONDAY)->toDateString(),
            'status' => 'booked',
        ], $overrides));
    }

    public static function qrToken(Member $member, array $overrides = []): QrToken
    {
        return QrToken::create(array_merge([
            'tokenable_type' => Member::class,
            'tokenable_id' => $member->id,
            'token' => Str::random(64),
            'purpose' => 'member',
            'expires_at' => null,
            'is_revoked' => false,
        ], $overrides));
    }

    public static function notification(User $user, array $data = [], ?\DateTimeInterface $readAt = null): DatabaseNotification
    {
        return DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'member-test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => array_merge([
                'title' => 'Notifikasi Member',
                'body' => 'Pesan aman untuk member.',
                'action_url' => route('member.dashboard'),
            ], $data),
            'read_at' => $readAt,
        ]);
    }
}
