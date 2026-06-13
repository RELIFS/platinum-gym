<?php

namespace App\Features\Admin\Queries;

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymCheckIn;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardQuery
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        $today = now()->toDateString();

        return [
            'admin' => $user,
            'stats' => $this->stats($today),
            'queue' => $this->operationalQueue($today),
            'quickLinks' => $this->quickLinks(),
            'recentMembers' => $this->recentMembers(),
            'recentPayments' => $this->recentPayments(),
            'todayBookings' => $this->todayBookings($today),
            'todayCheckIns' => $this->todayCheckIns($today),
            'moduleSummaries' => $this->moduleSummaries($today),
            'modules' => $this->modules($today, $user),
            'settings' => $this->settings(),
            'activityLogs' => $this->activityLogs(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function navigation(): array
    {
        return [
            [
                'label' => 'Ringkasan',
                'items' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
                ],
            ],
            [
                'label' => 'Operasional',
                'items' => [
                    ['label' => 'Check-in', 'route' => 'admin.check-in', 'active' => 'admin.check-in', 'icon' => 'qr'],
                    ['label' => 'Booking', 'route' => 'admin.booking', 'active' => 'admin.booking', 'icon' => 'calendar'],
                    ['label' => 'Notifikasi', 'route' => 'admin.notifications', 'active' => 'admin.notifications', 'icon' => 'bell'],
                ],
            ],
            [
                'label' => 'Anggota & Paket',
                'items' => [
                    ['label' => 'Anggota', 'route' => 'admin.members', 'active' => 'admin.members', 'icon' => 'members'],
                    ['label' => 'Paket', 'route' => 'admin.packages', 'active' => 'admin.packages', 'icon' => 'card'],
                ],
            ],
            [
                'label' => 'Aktivitas',
                'items' => [
                    ['label' => 'Kelas', 'route' => 'admin.classes', 'active' => 'admin.classes', 'icon' => 'activity'],
                ],
            ],
            [
                'label' => 'Keuangan',
                'items' => [
                    ['label' => 'Pembayaran', 'route' => 'admin.payments', 'active' => 'admin.payments', 'icon' => 'receipt'],
                ],
            ],
            [
                'label' => 'Produk & Konten',
                'items' => [
                    ['label' => 'Produk', 'route' => 'admin.products', 'active' => 'admin.products', 'icon' => 'box'],
                    ['label' => 'Galeri', 'route' => 'admin.gallery', 'active' => 'admin.gallery', 'icon' => 'image'],
                    ['label' => 'Testimoni', 'route' => 'admin.testimonials', 'active' => 'admin.testimonials', 'icon' => 'message'],
                    ['label' => 'Promo', 'route' => 'admin.promos', 'active' => 'admin.promos', 'icon' => 'tag'],
                ],
            ],
            [
                'label' => 'Tim & Sistem',
                'items' => [
                    ['label' => 'Trainer', 'route' => 'admin.trainers', 'active' => 'admin.trainers', 'icon' => 'trainer'],
                    ['label' => 'Laporan', 'route' => 'admin.reports', 'active' => 'admin.reports', 'icon' => 'chart'],
                    ['label' => 'Audit Log', 'route' => 'admin.audit-log', 'active' => 'admin.audit-log', 'icon' => 'shield'],
                    ['label' => 'Pengaturan', 'route' => 'admin.settings', 'active' => 'admin.settings', 'icon' => 'settings'],
                    ['label' => 'Profil Admin', 'route' => 'admin.profile', 'active' => 'admin.profile', 'icon' => 'user'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function pageDefinitions(): array
    {
        return [
            'check-in' => [
                'key' => 'check-in',
                'title' => 'Check-in',
                'eyebrow' => 'Operasional',
                'description' => 'Pantau check-in gym harian dan kesiapan alur QR member.',
                'route' => 'admin.check-in',
                'moduleKey' => 'checkIns',
            ],
            'booking' => [
                'key' => 'booking',
                'title' => 'Booking Kelas',
                'eyebrow' => 'Operasional',
                'description' => 'Pantau booking kelas aktif, status peserta, dan kapasitas jadwal.',
                'route' => 'admin.booking',
                'moduleKey' => 'bookings',
            ],
            'notifications' => [
                'key' => 'notifications',
                'title' => 'Notifikasi',
                'eyebrow' => 'Operasional',
                'description' => 'Kerangka awal untuk broadcast dan pengingat membership, booking, serta pembayaran.',
                'route' => 'admin.notifications',
                'moduleKey' => 'notifications',
            ],
            'members' => [
                'key' => 'members',
                'title' => 'Anggota',
                'eyebrow' => 'Anggota & Paket',
                'description' => 'Lihat daftar member terbaru, status akun, dan kode member.',
                'route' => 'admin.members',
                'moduleKey' => 'members',
            ],
            'packages' => [
                'key' => 'packages',
                'title' => 'Paket',
                'eyebrow' => 'Anggota & Paket',
                'description' => 'Pantau katalog membership dan paket sesi yang tersedia di website.',
                'route' => 'admin.packages',
                'moduleKey' => 'packages',
            ],
            'classes' => [
                'key' => 'classes',
                'title' => 'Kelas',
                'eyebrow' => 'Aktivitas',
                'description' => 'Ringkasan kelas, jadwal aktif, trainer, dan kapasitas.',
                'route' => 'admin.classes',
                'moduleKey' => 'classes',
            ],
            'payments' => [
                'key' => 'payments',
                'title' => 'Pembayaran',
                'eyebrow' => 'Keuangan',
                'description' => 'Pantau transaksi terbaru dan antrean pembayaran yang menunggu tindakan.',
                'route' => 'admin.payments',
                'moduleKey' => 'payments',
            ],
            'products' => [
                'key' => 'products',
                'title' => 'Produk',
                'eyebrow' => 'Produk & Konten',
                'description' => 'Pantau katalog produk, stok, dan status tampil di website.',
                'route' => 'admin.products',
                'moduleKey' => 'products',
            ],
            'gallery' => [
                'key' => 'gallery',
                'title' => 'Galeri',
                'eyebrow' => 'Produk & Konten',
                'description' => 'Pantau foto fasilitas dan konten visual yang tampil di website.',
                'route' => 'admin.gallery',
                'moduleKey' => 'gallery',
            ],
            'testimonials' => [
                'key' => 'testimonials',
                'title' => 'Testimoni',
                'eyebrow' => 'Produk & Konten',
                'description' => 'Pantau testimoni member dan status publikasinya.',
                'route' => 'admin.testimonials',
                'moduleKey' => 'testimonials',
            ],
            'promos' => [
                'key' => 'promos',
                'title' => 'Promo',
                'eyebrow' => 'Produk & Konten',
                'description' => 'Pantau promo aktif, periode tayang, dan nilai diskon.',
                'route' => 'admin.promos',
                'moduleKey' => 'promos',
            ],
            'trainers' => [
                'key' => 'trainers',
                'title' => 'Trainer',
                'eyebrow' => 'Tim & Sistem',
                'description' => 'Pantau trainer aktif dan spesialisasi yang mendukung kelas maupun PT.',
                'route' => 'admin.trainers',
                'moduleKey' => 'trainers',
            ],
            'reports' => [
                'key' => 'reports',
                'title' => 'Laporan',
                'eyebrow' => 'Tim & Sistem',
                'description' => 'Ringkasan awal untuk metrik membership, transaksi, booking, dan konten.',
                'route' => 'admin.reports',
                'moduleKey' => 'reports',
            ],
            'audit-log' => [
                'key' => 'audit-log',
                'title' => 'Audit Log',
                'eyebrow' => 'Tim & Sistem',
                'description' => 'Pantau jejak perubahan sistem dari activity log tanpa membuka data sensitif.',
                'route' => 'admin.audit-log',
                'moduleKey' => 'auditLogs',
            ],
            'settings' => [
                'key' => 'settings',
                'title' => 'Pengaturan',
                'eyebrow' => 'Tim & Sistem',
                'description' => 'Lihat konfigurasi website dengan nilai sensitif tersamarkan.',
                'route' => 'admin.settings',
                'moduleKey' => 'settings',
            ],
            'profile' => [
                'key' => 'profile',
                'title' => 'Profil Admin',
                'eyebrow' => 'Tim & Sistem',
                'description' => 'Ringkasan akun admin yang sedang masuk dan status role.',
                'route' => 'admin.profile',
                'moduleKey' => 'profile',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function stats(string $today): array
    {
        $pendingPaymentStatuses = $this->pendingPaymentStatuses();

        return [
            [
                'label' => 'Member Aktif',
                'value' => (string) Member::query()->where('status', 'active')->count(),
                'description' => Membership::query()->where('status', 'active')->whereDate('end_date', '>=', $today)->count().' membership aktif',
            ],
            [
                'label' => 'Booking Hari Ini',
                'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(),
                'description' => 'Kelas dan booking yang perlu dipantau hari ini.',
            ],
            [
                'label' => 'Pembayaran Pending',
                'value' => (string) Payment::query()->whereIn('status', $pendingPaymentStatuses)->count(),
                'description' => 'Butuh verifikasi atau tindak lanjut admin.',
            ],
            [
                'label' => 'Produk Aktif',
                'value' => (string) Product::query()->where('is_active', true)->count(),
                'description' => Product::query()->where('is_active', true)->where('stock', '<=', 3)->count().' produk stok rendah',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function operationalQueue(string $today): array
    {
        return [
            [
                'label' => 'Pembayaran menunggu',
                'value' => (string) Payment::query()->whereIn('status', $this->pendingPaymentStatuses())->count(),
                'description' => 'Prioritas validasi transfer dan status Midtrans.',
                'route' => 'admin.payments',
            ],
            [
                'label' => 'Booking kelas hari ini',
                'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(),
                'description' => 'Peserta kelas yang perlu dicek kapasitasnya.',
                'route' => 'admin.booking',
            ],
            [
                'label' => 'Check-in hari ini',
                'value' => (string) GymCheckIn::query()->whereDate('check_in_date', $today)->count(),
                'description' => 'Aktivitas masuk gym yang sudah tercatat.',
                'route' => 'admin.check-in',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function quickLinks(): array
    {
        return [
            ['label' => 'Anggota', 'description' => 'Data member terbaru', 'route' => 'admin.members', 'icon' => 'members'],
            ['label' => 'Pembayaran', 'description' => 'Antrean verifikasi', 'route' => 'admin.payments', 'icon' => 'receipt'],
            ['label' => 'Booking', 'description' => 'Jadwal kelas dan peserta', 'route' => 'admin.booking', 'icon' => 'calendar'],
            ['label' => 'Produk', 'description' => 'Katalog dan stok', 'route' => 'admin.products', 'icon' => 'box'],
            ['label' => 'Pengaturan', 'description' => 'Konfigurasi website', 'route' => 'admin.settings', 'icon' => 'settings'],
        ];
    }

    private function recentMembers(): Collection
    {
        return Member::query()
            ->with('user')
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    private function recentPayments(): Collection
    {
        return Payment::query()
            ->with(['member.user'])
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    private function todayBookings(string $today): Collection
    {
        return ClassEnrollment::query()
            ->with(['member.user', 'schedule.gymClass', 'schedule.trainer'])
            ->whereDate('session_date', $today)
            ->whereNotIn('status', ['cancelled', 'canceled'])
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    private function todayCheckIns(string $today): Collection
    {
        return GymCheckIn::query()
            ->with(['member.user'])
            ->whereDate('check_in_date', $today)
            ->latest('check_in_at')
            ->limit(8)
            ->get();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function modules(string $today, User $user): array
    {
        return [
            'checkIns' => [
                'title' => 'Check-in Terbaru',
                'description' => 'Data masuk gym hari ini dari QR atau petugas.',
                'empty' => 'Belum ada check-in hari ini.',
                'columns' => ['Member', 'Tanggal', 'Jam', 'Metode'],
                'rows' => $this->todayCheckIns($today)->map(fn (GymCheckIn $checkIn): array => [
                    $checkIn->member?->user?->name ?? $checkIn->member?->member_code ?? '-',
                    $checkIn->check_in_date?->translatedFormat('d M Y') ?? '-',
                    $checkIn->check_in_at?->format('H:i') ?? '-',
                    $this->headline($checkIn->method),
                ]),
            ],
            'bookings' => [
                'title' => 'Booking Hari Ini',
                'description' => 'Peserta yang tercatat pada jadwal hari ini.',
                'empty' => 'Belum ada booking kelas hari ini.',
                'columns' => ['Member', 'Kelas', 'Jam', 'Status'],
                'rows' => $this->todayBookings($today)->map(fn (ClassEnrollment $enrollment): array => [
                    $enrollment->member?->user?->name ?? $enrollment->member?->member_code ?? '-',
                    $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym',
                    substr((string) $enrollment->schedule?->start_time, 0, 5),
                    $this->headline($enrollment->status),
                ]),
            ],
            'notifications' => [
                'title' => 'Kesiapan Notifikasi',
                'description' => 'Fondasi menu untuk pengingat member, booking, dan pembayaran.',
                'empty' => 'Belum ada daftar notifikasi operasional.',
                'columns' => ['Area', 'Status', 'Catatan'],
                'rows' => collect([
                    ['Membership', 'Direncanakan', 'Pengingat masa aktif dan renewal.'],
                    ['Booking', 'Direncanakan', 'Reminder jadwal kelas dan perubahan kuota.'],
                    ['Pembayaran', 'Direncanakan', 'Follow-up pembayaran menunggu.'],
                ]),
            ],
            'members' => [
                'title' => 'Member Terbaru',
                'description' => 'Akun member yang terakhir terdaftar.',
                'empty' => 'Belum ada data member.',
                'columns' => ['Nama', 'Kode', 'Status', 'Bergabung'],
                'rows' => $this->recentMembers()->map(fn (Member $member): array => [
                    $member->user?->name ?? '-',
                    $member->member_code,
                    $this->headline($member->status),
                    $member->joined_at?->translatedFormat('d M Y') ?? '-',
                ]),
            ],
            'packages' => [
                'title' => 'Katalog Paket',
                'description' => 'Paket aktif dan nonaktif dari database layanan.',
                'empty' => 'Belum ada paket.',
                'columns' => ['Paket', 'Jenis', 'Harga', 'Status'],
                'rows' => Package::query()->orderByDesc('is_active')->orderBy('package_kind')->orderBy('price')->limit(12)->get()->map(fn (Package $package): array => [
                    $package->name,
                    $this->headline($package->package_kind),
                    $this->money($package->promo_price ?? $package->price),
                    $package->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'classes' => [
                'title' => 'Jadwal Kelas Aktif',
                'description' => 'Kelas dan jadwal yang tampil di katalog.',
                'empty' => 'Belum ada jadwal kelas aktif.',
                'columns' => ['Kelas', 'Hari', 'Jam', 'Trainer'],
                'rows' => ClassSchedule::query()
                    ->with(['gymClass', 'trainer'])
                    ->where('is_active', true)
                    ->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->limit(12)
                    ->get()
                    ->map(fn (ClassSchedule $schedule): array => [
                        $schedule->gymClass?->name ?? 'Kelas Platinum Gym',
                        $this->dayLabel((int) $schedule->day_of_week),
                        substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5),
                        $schedule->trainer?->name ?? '-',
                    ]),
            ],
            'payments' => [
                'title' => 'Pembayaran Terbaru',
                'description' => 'Transaksi terkini dari membership, paket sesi, dan kelas.',
                'empty' => 'Belum ada pembayaran.',
                'columns' => ['Kode', 'Member', 'Nominal', 'Status'],
                'rows' => $this->recentPayments()->map(fn (Payment $payment): array => [
                    $payment->payment_code,
                    $payment->member?->user?->name ?? $payment->member?->member_code ?? '-',
                    $this->money($payment->amount),
                    $this->headline($payment->status),
                ]),
            ],
            'products' => [
                'title' => 'Produk Katalog',
                'description' => 'Produk yang tersedia untuk referensi pembelian di lokasi.',
                'empty' => 'Belum ada produk.',
                'columns' => ['Produk', 'Harga', 'Stok', 'Status'],
                'rows' => Product::query()->with('category')->orderByDesc('is_active')->orderBy('name')->limit(12)->get()->map(fn (Product $product): array => [
                    $product->name,
                    $this->money($product->price),
                    (string) $product->stock,
                    $product->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'gallery' => [
                'title' => 'Galeri Website',
                'description' => 'Konten visual yang mengisi halaman galeri publik.',
                'empty' => 'Belum ada galeri.',
                'columns' => ['Judul', 'Caption', 'Urutan', 'Status'],
                'rows' => Gallery::query()->orderBy('sort_order')->latest('created_at')->limit(12)->get()->map(fn (Gallery $gallery): array => [
                    $gallery->title ?? 'Galeri Platinum Gym',
                    $gallery->caption ?? '-',
                    (string) $gallery->sort_order,
                    $gallery->is_published ? 'Tayang' : 'Draft',
                ]),
            ],
            'testimonials' => [
                'title' => 'Testimoni Member',
                'description' => 'Cerita member yang bisa tampil di website publik.',
                'empty' => 'Belum ada testimoni.',
                'columns' => ['Nama', 'Role', 'Rating', 'Status'],
                'rows' => Testimonial::query()->latest('created_at')->limit(12)->get()->map(fn (Testimonial $testimonial): array => [
                    $testimonial->name,
                    $testimonial->role ?? '-',
                    $testimonial->rating.'/5',
                    $testimonial->is_published ? 'Tayang' : 'Draft',
                ]),
            ],
            'promos' => [
                'title' => 'Promo Website',
                'description' => 'Promo yang dapat tampil pada website dan katalog layanan.',
                'empty' => 'Belum ada promo.',
                'columns' => ['Promo', 'Periode', 'Diskon', 'Status'],
                'rows' => Promo::query()->latest('created_at')->limit(12)->get()->map(fn (Promo $promo): array => [
                    $promo->title,
                    ($promo->starts_at?->translatedFormat('d M Y') ?? '-').' - '.($promo->ends_at?->translatedFormat('d M Y') ?? '-'),
                    $this->promoValue($promo->discount_type, $promo->discount_value),
                    $promo->is_published ? 'Tayang' : 'Draft',
                ]),
            ],
            'trainers' => [
                'title' => 'Trainer',
                'description' => 'Tim trainer dan spesialisasi yang tersedia.',
                'empty' => 'Belum ada trainer.',
                'columns' => ['Nama', 'Spesialisasi', 'Pengalaman', 'Status'],
                'rows' => Trainer::query()->orderByDesc('is_active')->orderBy('name')->limit(12)->get()->map(fn (Trainer $trainer): array => [
                    $trainer->name,
                    $trainer->specialization ?? '-',
                    filled($trainer->experience_years) ? $trainer->experience_years.' tahun' : '-',
                    $trainer->is_active ? 'Aktif' : 'Nonaktif',
                ]),
            ],
            'reports' => [
                'title' => 'Ringkasan Laporan',
                'description' => 'Metrik awal untuk laporan operasional.',
                'empty' => 'Belum ada ringkasan laporan.',
                'columns' => ['Metrik', 'Nilai', 'Catatan'],
                'rows' => $this->reportRows($today),
            ],
            'auditLogs' => [
                'title' => 'Activity Log Terbaru',
                'description' => 'Jejak perubahan sistem dari paket Spatie Activitylog.',
                'empty' => 'Belum ada activity log.',
                'columns' => ['Aktivitas', 'Event', 'Subjek', 'Waktu'],
                'rows' => $this->activityLogs()->map(fn (object $log): array => [
                    (string) ($log->description ?? '-'),
                    $this->headline($log->event ?? '-'),
                    class_basename((string) ($log->subject_type ?? '-')),
                    filled($log->created_at) ? (string) $log->created_at : '-',
                ]),
            ],
            'settings' => [
                'title' => 'Pengaturan Website',
                'description' => 'Nilai sensitif otomatis dimask agar aman dilihat dari admin v1.',
                'empty' => 'Belum ada setting.',
                'columns' => ['Key', 'Group', 'Type', 'Value'],
                'rows' => $this->settings()->map(fn (array $setting): array => [
                    $setting['key'],
                    $setting['group'],
                    $setting['type'],
                    $setting['value'],
                ]),
            ],
            'profile' => [
                'title' => 'Profil Admin',
                'description' => 'Data aman akun admin saat ini.',
                'empty' => 'Profil admin belum tersedia.',
                'columns' => ['Field', 'Nilai', 'Catatan'],
                'rows' => collect([
                    ['Nama', $user->name, 'Akun yang sedang login.'],
                    ['Email', $user->email, 'Dipakai untuk autentikasi.'],
                    ['Telepon', $user->phone ?? '-', 'Opsional.'],
                    ['Role', $user->getRoleNames()->implode(', ') ?: '-', 'Akses admin dijaga middleware role.'],
                    ['Login terakhir', $user->last_login_at?->translatedFormat('d M Y H:i') ?? '-', 'Diisi saat login berhasil.'],
                ]),
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function moduleSummaries(string $today): array
    {
        return [
            ['label' => 'Member', 'value' => (string) Member::query()->count(), 'route' => 'admin.members', 'description' => 'Total anggota terdaftar.'],
            ['label' => 'Paket', 'value' => (string) Package::query()->where('is_active', true)->count(), 'route' => 'admin.packages', 'description' => 'Paket aktif di katalog.'],
            ['label' => 'Kelas', 'value' => (string) GymClass::query()->where('is_active', true)->count(), 'route' => 'admin.classes', 'description' => 'Kelas aktif.'],
            ['label' => 'Pembayaran', 'value' => (string) Payment::query()->whereDate('created_at', $today)->count(), 'route' => 'admin.payments', 'description' => 'Transaksi dibuat hari ini.'],
            ['label' => 'Produk', 'value' => (string) Product::query()->where('is_active', true)->count(), 'route' => 'admin.products', 'description' => 'Produk tampil.'],
            ['label' => 'Promo', 'value' => (string) Promo::query()->where('is_published', true)->count(), 'route' => 'admin.promos', 'description' => 'Promo tayang.'],
        ];
    }

    private function settings(): Collection
    {
        return Setting::query()
            ->orderBy('group')
            ->orderBy('key')
            ->limit(80)
            ->get()
            ->map(fn (Setting $setting): array => [
                'key' => $setting->key,
                'group' => $setting->group,
                'type' => $setting->type,
                'value' => $this->settingDisplayValue($setting->key, $setting->value),
            ]);
    }

    private function activityLogs(): Collection
    {
        if (! Schema::hasTable('activity_log')) {
            return collect();
        }

        return DB::table('activity_log')
            ->select(['description', 'event', 'subject_type', 'created_at'])
            ->latest('created_at')
            ->limit(12)
            ->get();
    }

    private function reportRows(string $today): Collection
    {
        return collect([
            ['Member aktif', (string) Member::query()->where('status', 'active')->count(), 'Status akun member.'],
            ['Membership aktif', (string) Membership::query()->where('status', 'active')->whereDate('end_date', '>=', $today)->count(), 'Masa aktif belum lewat.'],
            ['Pembayaran bulan ini', $this->money(Payment::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount')), 'Total nominal dibuat bulan ini.'],
            ['Booking hari ini', (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'Semua status kecuali yang dibatalkan dihitung pada modul booking.'],
            ['Check-in hari ini', (string) GymCheckIn::query()->whereDate('check_in_date', $today)->count(), 'Check-in gym tercatat.'],
        ]);
    }

    private function settingDisplayValue(string $key, ?string $value): string
    {
        if ($this->isSensitiveSetting($key)) {
            return filled($value) ? 'Tersamarkan' : '-';
        }

        if (! filled($value)) {
            return '-';
        }

        return str($value)->limit(120)->toString();
    }

    private function isSensitiveSetting(string $key): bool
    {
        $normalized = str($key)->lower()->toString();

        foreach (['secret', 'token', 'password', 'credential', 'client_secret', 'qr_secret', 'prompt', 'oauth', 'api_key'] as $needle) {
            if (str_contains($normalized, $needle)) {
                return true;
            }
        }

        return str_ends_with($normalized, '_key') || str_contains($normalized, 'key_');
    }

    /**
     * @return array<int, string>
     */
    private function pendingPaymentStatuses(): array
    {
        return ['pending', 'waiting_payment', 'waiting_confirmation', 'unpaid'];
    }

    private function headline(?string $value): string
    {
        return filled($value) ? str($value)->replace(['_', '-'], ' ')->headline()->toString() : '-';
    }

    private function money(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private function dayLabel(int $day): string
    {
        return [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$day] ?? '-';
    }

    private function promoValue(?string $type, mixed $value): string
    {
        if (! filled($type) || $type === 'none' || ! filled($value)) {
            return '-';
        }

        if ($type === 'percentage') {
            return number_format((float) $value, 0, ',', '.').'%';
        }

        return $this->money($value);
    }
}
