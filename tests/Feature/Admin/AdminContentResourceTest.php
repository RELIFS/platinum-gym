<?php

use App\Models\Product;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create product resource with safe public storage path', function () {
    Storage::fake('public');
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'products'), [
            'name' => 'Sarung Tangan Admin QA',
            'slug' => '',
            'price' => 125000,
            'stock' => 12,
            'description' => 'Produk latihan resmi.',
            'image_file' => UploadedFile::fake()->image('gloves.jpg', 600, 600),
            'image_alt' => 'Sarung tangan gym',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.products'));

    $product = Product::query()->where('name', 'Sarung Tangan Admin QA')->firstOrFail();

    expect($product->image_path)
        ->toStartWith('storage/admin/products/')
        ->not->toContain(base_path());

    Storage::disk('public')->assertExists(str_replace('storage/', '', $product->image_path));
});

test('admin resource registry returns not found for unknown resource', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get('/admin/resource/unknown-resource/tambah')
        ->assertNotFound();
});

test('admin promo resource validates date range without saving invalid data', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->from(route('admin.resources.create', 'promos'))
        ->post(route('admin.resources.store', 'promos'), [
            'title' => 'Promo Tanggal Salah',
            'starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'is_published' => '1',
        ])
        ->assertRedirect(route('admin.resources.create', 'promos'))
        ->assertSessionHasErrors('ends_at');

    $this->assertDatabaseMissing('promos', ['title' => 'Promo Tanggal Salah']);
});

test('admin can toggle boolean publication and active state resources', function () {
    $admin = AdminFixture::admin();
    $product = AdminFixture::product(['is_active' => true]);
    $promo = AdminFixture::promo(['is_published' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.resources.toggle', ['resource' => 'products', 'id' => $product->id]))
        ->assertRedirect();

    $this->actingAs($admin)
        ->patch(route('admin.resources.toggle', ['resource' => 'promos', 'id' => $promo->id]))
        ->assertRedirect();

    expect($product->refresh()->is_active)->toBeFalse()
        ->and($promo->refresh()->is_published)->toBeFalse();
});
