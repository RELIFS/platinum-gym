<?php

namespace Tests\Feature\PublicWebsite\Support;

use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymClass;
use App\Models\Package as ServicePackage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Str;

class PublicWebsiteFixtures
{
    public const SENSITIVE_FRAGMENTS = [
        'public-secret-api-key',
        'midtrans-server-key',
        'owner-secret-snap-token',
        'raw-gateway-secret',
        'qr-token-secret',
        'change-this-in-production',
        'gemini_system_prompt',
        'PG-PRIVATE-MEMBER',
        'INV-PRIVATE-REPORT',
    ];

    /** @return array<string, string> */
    public static function getRoutes(): array
    {
        return [
            'public.home' => '/',
            'public.about' => '/tentang-kami',
            'public.services' => '/layanan',
            'public.classes' => '/kelas',
            'public.products' => '/produk',
            'public.gallery' => '/galeri',
            'public.location' => '/lokasi',
            'public.bmi' => '/bmi',
            'legal.terms' => '/syarat-ketentuan',
            'legal.privacy' => '/kebijakan-privasi',
        ];
    }

    public static function user(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Public QA User',
            'email' => 'public.qa.'.Str::lower(Str::random(8)).'@example.test',
        ], $overrides));
    }

    public static function package(array $overrides = []): ServicePackage
    {
        $name = $overrides['name'] ?? 'Public QA Membership '.Str::upper(Str::random(5));

        return ServicePackage::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'package_kind' => 'membership',
            'type' => 'gym',
            'price' => 250000,
            'duration_days' => 30,
            'description' => 'Paket latihan public QA.',
            'benefits' => ['Akses gym', 'Konsultasi awal'],
            'is_active' => true,
        ], $overrides));
    }

    public static function trainer(array $overrides = []): Trainer
    {
        return Trainer::create(array_merge([
            'name' => 'Coach Public QA '.Str::upper(Str::random(4)),
            'specialization' => 'Strength',
            'bio' => 'Mendampingi latihan public QA.',
            'is_active' => true,
        ], $overrides));
    }

    /** @return array{0: GymClass, 1: ClassSchedule} */
    public static function schedule(array $classOverrides = [], array $scheduleOverrides = []): array
    {
        $name = $classOverrides['name'] ?? 'Public QA Class '.Str::upper(Str::random(5));
        $trainer = self::trainer();

        $gymClass = GymClass::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Kelas public QA.',
            'class_type' => 'senam',
            'access_type' => 'included',
            'required_package_type' => 'senam',
            'capacity' => 18,
            'member_price' => 0,
            'non_member_price' => 35000,
            'is_active' => true,
        ], $classOverrides));

        $schedule = ClassSchedule::create(array_merge([
            'gym_class_id' => $gymClass->id,
            'trainer_id' => $trainer->id,
            'day_of_week' => 3,
            'start_time' => '17:00:00',
            'end_time' => '18:00:00',
            'room' => 'Studio QA',
            'capacity' => 18,
            'is_active' => true,
        ], $scheduleOverrides));

        return [$gymClass, $schedule];
    }

    public static function category(array $overrides = []): ProductCategory
    {
        $name = $overrides['name'] ?? 'Kategori Public QA '.Str::upper(Str::random(4));

        return ProductCategory::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Kategori produk public QA.',
            'sort_order' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public static function product(?ProductCategory $category = null, array $overrides = []): Product
    {
        $name = $overrides['name'] ?? 'Produk Public QA '.Str::upper(Str::random(5));
        $category ??= self::category();

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'price' => 45000,
            'stock' => 7,
            'description' => 'Pembelian tersedia langsung di lokasi Platinum Gym Padang.',
            'image_path' => null,
            'image_alt' => 'Foto '.$name,
            'is_active' => true,
        ], $overrides));
    }

    public static function gallery(array $overrides = []): Gallery
    {
        return Gallery::create(array_merge([
            'title' => 'Galeri Public QA '.Str::upper(Str::random(4)),
            'caption' => 'Dokumentasi public QA.',
            'image_path' => null,
            'image_alt' => 'Aktivitas public QA',
            'sort_order' => 10,
            'is_published' => true,
        ], $overrides));
    }

    public static function promo(array $overrides = []): Promo
    {
        $title = $overrides['title'] ?? 'Promo Public QA '.Str::upper(Str::random(4));

        return Promo::create(array_merge([
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => 'Promo public QA.',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addWeek(),
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_published' => true,
            'sort_order' => 10,
        ], $overrides));
    }

    public static function testimonial(array $overrides = []): Testimonial
    {
        return Testimonial::create(array_merge([
            'name' => 'Member Public QA',
            'role' => 'Member',
            'content' => 'Latihan terasa lebih terarah.',
            'rating' => 5,
            'is_published' => true,
            'sort_order' => 10,
        ], $overrides));
    }

    public static function setting(string $key, mixed $value, string $type = 'text', string $group = 'public'): Setting
    {
        return Setting::create([
            'key' => $key,
            'value' => is_array($value) ? json_encode($value) : $value,
            'type' => $type,
            'group' => $group,
        ]);
    }

    public static function publicSettings(): void
    {
        self::setting('site_name', 'Platinum Gym Padang');
        self::setting('address', 'Jl. Public QA No. 1, Padang');
        self::setting('phone_display', '+62 821-7477-7761');
        self::setting('whatsapp_number', '6282174777761');
        self::setting('public_email', 'public.qa@platinum.test');
        self::setting('instagram_handle', '@platinumgym.qa');
        self::setting('instagram_url', 'https://www.instagram.com/platinumgym.qa');
        self::setting('maps_url', 'https://www.google.com/maps?cid=12649542093238337913');
        self::setting('maps_search_url', 'https://www.google.com/maps/search/?api=1&query=Platinum%20Gym%20Padang');
        self::setting('maps_embed_url', 'https://www.google.com/maps/embed?pb=public-qa');
        self::setting('operational_hours', ['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup'], 'json');
    }

    public static function sensitiveSettings(): void
    {
        self::setting('midtrans_server_key', 'midtrans-server-key', 'password', 'payments');
        self::setting('gemini_system_prompt', 'gemini_system_prompt public-secret-api-key', 'textarea', 'ai');
        self::setting('qr_secret', 'qr-token-secret', 'password', 'security');
    }
}
