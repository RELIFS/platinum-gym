<?php

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
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('ownership policies allow members to access only their own records', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $owner = User::factory()->create();
    $owner->assignRole('owner');

    $memberUser = User::factory()->create();
    $memberUser->assignRole('member');

    $otherUser = User::factory()->create();
    $otherUser->assignRole('member');

    $member = Member::create([
        'user_id' => $memberUser->id,
        'member_code' => 'PG-TEST-0001',
        'gender' => 'male',
        'birth_date' => '2000-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $otherMember = Member::create([
        'user_id' => $otherUser->id,
        'member_code' => 'PG-TEST-0002',
        'gender' => 'female',
        'birth_date' => '2001-01-01',
        'joined_at' => now()->toDateString(),
        'status' => 'active',
    ]);

    $package = ServicePackage::create([
        'name' => 'Gym Test',
        'slug' => 'gym-test',
        'package_kind' => 'membership',
        'type' => 'gym',
        'price' => 100000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $membership = Membership::create([
        'member_id' => $member->id,
        'package_id' => $package->id,
        'code' => 'MBR-TEST-0001',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addMonth()->toDateString(),
        'price' => 100000,
        'status' => 'active',
    ]);

    $payment = Payment::create([
        'payment_code' => 'PAY-TEST-0001',
        'member_id' => $member->id,
        'payable_type' => Membership::class,
        'payable_id' => $membership->id,
        'method' => 'transfer',
        'amount' => 100000,
        'status' => 'waiting_confirmation',
    ]);

    $invoice = Invoice::create([
        'payment_id' => $payment->id,
        'invoice_number' => 'INV-TEST-0001',
        'issued_at' => now()->toDateString(),
        'subtotal' => 100000,
        'total' => 100000,
        'status' => 'issued',
    ]);

    $gymClass = GymClass::create([
        'name' => 'Zumba Test',
        'slug' => 'zumba-test',
        'class_type' => 'zumba',
        'access_type' => 'membership',
        'capacity' => 20,
        'is_active' => true,
    ]);

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gymClass->id,
        'day_of_week' => 1,
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'is_active' => true,
    ]);

    $enrollment = ClassEnrollment::create([
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
        'session_date' => now()->toDateString(),
        'status' => 'booked',
    ]);

    $qrToken = QrToken::create([
        'tokenable_type' => Member::class,
        'tokenable_id' => $member->id,
        'token' => Str::random(64),
        'purpose' => 'member',
    ]);

    expect($memberUser->can('view', $member))->toBeTrue()
        ->and($memberUser->can('update', $member))->toBeTrue()
        ->and($memberUser->can('view', $membership))->toBeTrue()
        ->and($memberUser->can('view', $payment))->toBeTrue()
        ->and($memberUser->can('update', $payment))->toBeTrue()
        ->and($memberUser->can('view', $invoice))->toBeTrue()
        ->and($memberUser->can('download', $invoice))->toBeTrue()
        ->and($memberUser->can('view', $enrollment))->toBeTrue()
        ->and($memberUser->can('cancel', $enrollment))->toBeTrue()
        ->and($memberUser->can('view', $qrToken))->toBeTrue()
        ->and($memberUser->can('view', $otherMember))->toBeFalse()
        ->and($otherUser->can('view', $payment))->toBeFalse()
        ->and($admin->can('view', $payment))->toBeTrue()
        ->and($owner->can('viewAny', Payment::class))->toBeTrue()
        ->and($owner->can('view', $payment))->toBeTrue()
        ->and($owner->can('update', $payment))->toBeFalse()
        ->and($owner->can('viewAny', Invoice::class))->toBeTrue()
        ->and($owner->can('view', $invoice))->toBeTrue()
        ->and($owner->can('download', $invoice))->toBeTrue()
        ->and($owner->can('update', $invoice))->toBeFalse();
});
