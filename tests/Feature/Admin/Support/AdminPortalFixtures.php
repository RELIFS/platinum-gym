<?php

namespace Tests\Feature\Admin\Support;

use App\Models\ClassAttendance;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymClass;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package as ServicePackage;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promo;
use App\Models\QrToken;
use App\Models\Setting;
use App\Models\Trainer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPortalFixtures
{
    public static function admin(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Admin QA',
            'email' => 'admin.qa.'.Str::lower(Str::random(8)).'@example.test',
        ], $overrides));

        $user->assignRole('admin');

        return $user;
    }

    public static function improvementsAdmin(): User
    {
        return self::admin([
            'name' => 'Admin Improvements',
            'email' => 'admin.improvements@example.com',
        ]);
    }

    public static function roleUser(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'email' => $role.'.qa.'.Str::lower(Str::random(8)).'@example.test',
        ], $overrides));

        $user->assignRole($role);

        return $user;
    }

    public static function revokeAdminPermission(string $permission): void
    {
        Role::findByName('admin')->revokePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** @return array{0: User, 1: Member} */
    public static function member(?string $code = null, array $userOverrides = [], array $memberOverrides = []): array
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Member QA',
            'email' => 'member.qa.'.Str::lower(Str::random(8)).'@example.test',
            'phone' => '0812'.random_int(10000000, 99999999),
        ], $userOverrides));
        $user->assignRole('member');

        $member = Member::create(array_merge([
            'user_id' => $user->id,
            'member_code' => $code ?? 'PG-ADM-'.Str::upper(Str::random(8)),
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'joined_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ], $memberOverrides));

        return [$user, $member];
    }

    /** @return array{0: User, 1: Member} */
    public static function improvementsMember(string $code = 'PG-ADMIN-IMP-0001'): array
    {
        return self::member($code, [
            'name' => 'Member Improvements Admin',
            'phone' => '0812'.fake()->unique()->numerify('########'),
        ]);
    }

    public static function package(array $overrides = []): ServicePackage
    {
        $name = $overrides['name'] ?? 'Paket Admin QA '.Str::upper(Str::random(5));

        return ServicePackage::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'package_kind' => 'membership',
            'type' => 'gym',
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
            'code' => 'MBR-ADM-'.Str::upper(Str::random(8)),
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'price' => $package->price,
            'status' => 'active',
        ], $overrides));
    }

    public static function payment(Member $member, ?object $payable = null, array $overrides = []): Payment
    {
        $payable ??= self::membership($member, overrides: ['status' => 'pending_payment']);

        return Payment::create(array_merge([
            'payment_code' => 'PAY-ADM-'.Str::upper(Str::random(8)),
            'member_id' => $member->id,
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
            'method' => 'midtrans',
            'amount' => 250000,
            'status' => 'waiting_confirmation',
        ], $overrides));
    }

    public static function invoice(Payment $payment, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'payment_id' => $payment->id,
            'invoice_number' => 'INV-ADM-'.Str::upper(Str::random(8)),
            'issued_at' => now()->toDateString(),
            'due_date' => now()->addDays(2)->toDateString(),
            'subtotal' => $payment->amount,
            'discount' => 0,
            'tax' => 0,
            'total' => $payment->amount,
            'status' => $payment->status === 'paid' ? 'paid' : 'issued',
        ], $overrides));
    }

    /** @return array{0: GymClass, 1: ClassSchedule} */
    public static function schedule(?string $sessionDate = null, array $classOverrides = [], array $scheduleOverrides = []): array
    {
        $date = CarbonImmutable::parse($sessionDate ?? now()->toDateString());
        $name = $classOverrides['name'] ?? 'Kelas Admin QA '.Str::upper(Str::random(5));

        $gymClass = GymClass::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
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

    /** @return array{0: GymClass, 1: ClassSchedule, 2: ClassEnrollment} */
    public static function classBooking(Member $member, string $status = 'booked', ?string $sessionDate = null): array
    {
        $date = $sessionDate ?? now()->toDateString();
        [$gymClass, $schedule] = self::schedule($date, [
            'name' => 'Admin Booking Guard '.Str::upper(Str::random(6)),
            'slug' => 'admin-booking-guard-'.Str::lower(Str::random(8)),
        ]);

        $enrollment = self::enrollment($member, $schedule, [
            'session_date' => $date,
            'status' => $status,
        ]);

        return [$gymClass, $schedule, $enrollment];
    }

    public static function attendance(ClassEnrollment $enrollment, User $admin): ClassAttendance
    {
        return ClassAttendance::create([
            'enrollment_id' => $enrollment->id,
            'schedule_id' => $enrollment->schedule_id,
            'member_id' => $enrollment->member_id,
            'attendance_date' => $enrollment->session_date,
            'attended_at' => now(),
            'method' => 'admin',
            'status' => 'present',
            'scanned_by' => $admin->id,
        ]);
    }

    public static function qrToken(Member $member, array $overrides = []): QrToken
    {
        return QrToken::create(array_merge([
            'tokenable_type' => Member::class,
            'tokenable_id' => $member->id,
            'token' => Str::random(64),
            'purpose' => 'member',
            'is_revoked' => false,
        ], $overrides));
    }

    public static function product(array $overrides = []): Product
    {
        $name = $overrides['name'] ?? 'Produk Admin QA '.Str::upper(Str::random(5));

        return Product::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => 95000,
            'stock' => 8,
            'is_active' => true,
        ], $overrides));
    }

    public static function promo(array $overrides = []): Promo
    {
        $title = $overrides['title'] ?? 'Promo Admin QA '.Str::upper(Str::random(5));

        return Promo::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => 'Promo QA',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addWeek(),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_published' => true,
        ], $overrides));
    }

    public static function gallery(array $overrides = []): Gallery
    {
        return Gallery::create(array_merge([
            'title' => 'Galeri Admin QA',
            'caption' => 'Foto fasilitas QA',
            'image_path' => 'storage/admin/gallery/sample.jpg',
            'image_alt' => 'Foto fasilitas',
            'is_published' => true,
        ], $overrides));
    }

    public static function trainer(array $overrides = []): Trainer
    {
        return Trainer::create(array_merge([
            'name' => 'Coach Admin QA '.Str::upper(Str::random(4)),
            'specialization' => 'Strength',
            'is_active' => true,
        ], $overrides));
    }

    public static function setting(string $key, ?string $value, string $type = 'text', string $group = 'general'): Setting
    {
        return Setting::create(compact('key', 'value', 'type', 'group'));
    }
}
