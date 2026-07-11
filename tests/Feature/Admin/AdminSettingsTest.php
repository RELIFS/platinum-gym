<?php

use App\Models\Setting;
use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function adminSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'site_name' => 'Platinum Gym QA',
        'address' => 'Jl. QA Padang',
        'phone_number' => '081234567890',
        'phone_display' => '0812-3456-7890',
        'whatsapp_number' => '6281234567890',
        'public_email' => 'hello@example.test',
        'instagram_handle' => '@platinumqa',
        'instagram_url' => 'https://instagram.example.test/platinumqa',
        'maps_url' => 'https://maps.example.test/platinum',
        'maps_search_url' => 'https://maps.example.test/search',
        'maps_shared_url' => 'https://maps.example.test/share',
        'maps_embed_url' => 'https://maps.example.test/embed',
        'operational_hours_monday_saturday' => '08:00-22:00',
        'operational_hours_sunday' => 'Tutup',
        'invoice_prefix' => 'PGQA',
        'invoice_footer' => 'Terima kasih sudah bertransaksi.',
    ], $overrides);
}

test('admin settings update only persists editable public settings', function () {
    $admin = AdminFixture::admin();
    AdminFixture::setting('maps_url', 'https://maps.example.test/original', 'text', 'contact');

    $this->actingAs($admin)
        ->patch(route('admin.settings.update'), adminSettingsPayload([
            'midtrans_server_key' => 'secret-server-key',
            'maps_url' => 'https://maps.example.test/changed-from-form',
        ]))
        ->assertRedirect()
        ->assertSessionHas('status', 'Pengaturan website berhasil diperbarui.');

    expect(Setting::query()->where('key', 'site_name')->value('value'))->toBe('Platinum Gym QA')
        ->and(json_decode((string) Setting::query()->where('key', 'operational_hours')->value('value'), true))
        ->toMatchArray(['monday_saturday' => '08:00-22:00', 'sunday' => 'Tutup'])
        ->and(Setting::query()->where('key', 'maps_url')->value('value'))->toBe('https://maps.example.test/original')
        ->and(Setting::query()->where('key', 'maps_search_url')->exists())->toBeFalse()
        ->and(Setting::query()->where('key', 'midtrans_server_key')->exists())->toBeFalse();
});

test('admin settings form masks sensitive operational context and validates public fields', function () {
    $admin = AdminFixture::admin();
    AdminFixture::setting('midtrans_server_key', 'server-secret-value', 'secret', 'payment');

    $this->actingAs($admin)
        ->get('/admin/pengaturan')
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee('Nama Website')
        ->assertSee('Media Sosial')
        ->assertDontSee('Media Sosial &amp; Peta', false)
        ->assertDontSee('URL Google Maps')
        ->assertDontSee('URL pencarian Maps')
        ->assertDontSee('URL share Maps')
        ->assertDontSee('Link embed peta Google')
        ->assertDontSee('Konfigurasi teknis dan sensitif')
        ->assertDontSee('Kunci')
        ->assertDontSee('Tersamarkan')
        ->assertDontSee('midtrans_server_key')
        ->assertDontSee('server-secret-value');

    $this->actingAs($admin)
        ->from('/admin/pengaturan')
        ->patch(route('admin.settings.update'), adminSettingsPayload([
            'site_name' => '',
            'public_email' => 'not-an-email',
        ]))
        ->assertRedirect('/admin/pengaturan')
        ->assertSessionHasErrors(['site_name', 'public_email']);
});

test('admin settings page hides raw technical settings table and sensitive values', function () {
    $admin = AdminFixture::admin();
    AdminFixture::setting('gemini_api_key', 'secret-search-sentinel', 'secret', 'ai');
    AdminFixture::setting('site_tagline', 'Public Search Sentinel', 'text', 'general');

    $this->actingAs($admin)
        ->get(route('admin.settings', ['q' => 'secret-search-sentinel']))
        ->assertOk()
        ->assertDontSee('gemini_api_key')
        ->assertDontSee('Tersamarkan')
        ->assertDontSee('secret-search-sentinel')
        ->assertDontSee('site_tagline')
        ->assertDontSee('Public Search Sentinel')
        ->assertDontSee('Kunci')
        ->assertDontSee('Grup')
        ->assertDontSee('Tipe')
        ->assertDontSee('Nilai');
});
