<?php

use App\Models\Product;
use App\Models\Promo;
use App\Models\Testimonial;
use Database\Seeders\PackageSeeder;
use Database\Seeders\PromoSeeder;
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

test('admin content image uploads reject unsupported public image formats', function (string $resource, array $payload, string $folder, Closure $file) {
    Storage::fake('public');
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->from(route('admin.resources.create', $resource))
        ->post(route('admin.resources.store', $resource), $payload + [
            'image_file' => $file(),
        ])
        ->assertRedirect(route('admin.resources.create', $resource))
        ->assertSessionHasErrors('image_file');

    expect(Storage::disk('public')->allFiles($folder))->toBe([]);
})->with([
    'product svg' => ['products', [
        'name' => 'SVG Product QA',
        'slug' => '',
        'price' => 125000,
        'stock' => 12,
        'description' => 'Produk latihan resmi.',
        'image_alt' => 'Sarung tangan gym',
        'is_active' => '1',
    ], 'admin/products', fn () => UploadedFile::fake()->create('product-content.svg', 12, 'image/svg+xml')],
    'product gif' => ['products', [
        'name' => 'GIF Product QA',
        'slug' => '',
        'price' => 125000,
        'stock' => 12,
        'description' => 'Produk latihan resmi.',
        'image_alt' => 'Sarung tangan gym',
        'is_active' => '1',
    ], 'admin/products', fn () => UploadedFile::fake()->createWithContent('product-content.gif', base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='))],
    'gallery svg' => ['gallery', [
        'title' => 'SVG Gallery QA',
        'caption' => 'Foto fasilitas QA.',
        'image_alt' => 'Foto fasilitas',
        'sort_order' => 1,
        'is_published' => '1',
    ], 'admin/gallery', fn () => UploadedFile::fake()->create('gallery-content.svg', 12, 'image/svg+xml')],
    'gallery gif' => ['gallery', [
        'title' => 'GIF Gallery QA',
        'caption' => 'Foto fasilitas QA.',
        'image_alt' => 'Foto fasilitas',
        'sort_order' => 1,
        'is_published' => '1',
    ], 'admin/gallery', fn () => UploadedFile::fake()->createWithContent('gallery-content.gif', base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw=='))],
]);

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

test('admin promo resource can be linked to a package', function () {
    $admin = AdminFixture::admin();
    $package = AdminFixture::package(['name' => 'Paket Promo Linked QA']);

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'promos'), [
            'package_id' => $package->id,
            'title' => 'Promo Paket Linked QA',
            'starts_at_display' => now()->addDay()->format('d/m/Y H:i'),
            'ends_at_display' => now()->addWeek()->format('d/m/Y H:i'),
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'is_published' => '1',
        ])
        ->assertRedirect(route('admin.promos'));

    expect(Promo::where('title', 'Promo Paket Linked QA')->firstOrFail()->package_id)->toBe($package->id);
});

test('admin promo page renders official seeded gym duration promos as published content', function () {
    $admin = AdminFixture::admin();
    $this->seed([PackageSeeder::class, PromoSeeder::class]);

    $this->actingAs($admin)
        ->get(route('admin.promos'))
        ->assertOk()
        ->assertSee('Beli Gym Umum 3 Bulan Gratis 1 Bulan')
        ->assertSee('Beli Gym Umum 6 Bulan Gratis 2 Bulan')
        ->assertSee('Tayang')
        ->assertDontSee('Hemat 0%');
});

test('admin testimonial resource renders star rating and stores rating value', function () {
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->get(route('admin.resources.create', 'testimonials'))
        ->assertOk()
        ->assertSee('role="radiogroup"', false)
        ->assertSee('admin-star-rating', false)
        ->assertSee('admin-star-rating-star', false)
        ->assertSee('Klik bintang untuk memilih rating.')
        ->assertSee('&#9733;', false)
        ->assertSee('5 dari 5');

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'testimonials'), [
            'name' => 'Testimoni Bintang QA',
            'role' => 'Member',
            'content' => 'Latihan makin konsisten.',
            'rating' => 4,
            'is_published' => '1',
        ])
        ->assertRedirect(route('admin.testimonials'));

    $testimonial = Testimonial::where('name', 'Testimoni Bintang QA')->firstOrFail();

    expect($testimonial->rating)->toBe(4);

    $this->actingAs($admin)
        ->get(route('admin.resources.edit', ['resource' => 'testimonials', 'id' => $testimonial->id]))
        ->assertOk()
        ->assertSee('value="4" class="admin-star-rating-input" checked', false);
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
