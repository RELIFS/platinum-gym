<?php

use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Package as ServicePackage;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create package resource and benefits are stored as structured list', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'packages'), [
            'name' => 'Paket Strength QA',
            'slug' => '',
            'package_kind' => 'membership',
            'type' => 'gym',
            'gender_restriction' => 'all',
            'price' => 300000,
            'duration_days' => 30,
            'benefits' => "Akses gym\nLoker harian",
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.packages'));

    $package = ServicePackage::query()->where('name', 'Paket Strength QA')->firstOrFail();

    expect($package->slug)->toBe('paket-strength-qa')
        ->and($package->benefits)->toBe(['Akses gym', 'Loker harian'])
        ->and($package->is_active)->toBeTrue();
});

test('admin can create class and valid schedule resource', function () {
    $admin = AdminFixture::admin();
    $trainer = AdminFixture::trainer();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'classes'), [
            'name' => 'Zumba Schedule QA',
            'slug' => '',
            'class_type' => 'zumba',
            'access_type' => 'included',
            'required_package_type' => 'zumba',
            'capacity' => 20,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.classes'));

    $class = GymClass::query()->where('name', 'Zumba Schedule QA')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'class-schedules'), [
            'gym_class_id' => $class->id,
            'trainer_id' => $trainer->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room' => 'Studio QA',
            'capacity' => 18,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.classes'));

    expect(ClassSchedule::query()
        ->where('gym_class_id', $class->id)
        ->where('trainer_id', $trainer->id)
        ->where('room', 'Studio QA')
        ->exists())->toBeTrue();
});

test('admin class schedule resource rejects invalid time range', function () {
    $admin = AdminFixture::admin();
    [$class] = AdminFixture::schedule();

    $this->actingAs($admin)
        ->from(route('admin.resources.create', 'class-schedules'))
        ->post(route('admin.resources.store', 'class-schedules'), [
            'gym_class_id' => $class->id,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '09:00',
            'capacity' => 10,
        ])
        ->assertRedirect(route('admin.resources.create', 'class-schedules'))
        ->assertSessionHasErrors('end_time');
});
