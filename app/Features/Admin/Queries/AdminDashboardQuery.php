<?php

namespace App\Features\Admin\Queries;

use App\Features\Admin\Support\AdminEditableSettingRegistry;
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
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardQuery
{
    private const ADMIN_PER_PAGE = 12;

    public function __construct(private readonly AdminEditableSettingRegistry $editableSettings) {}

    /** @return array<string, mixed> */
    public function forUser(User $user, array $filters = [], ?string $activeModule = null): array
    {
        $today = now()->toDateString();
        $filters = $this->normaliseFilters($filters);

        return [
            'admin' => $user,
            'filters' => $filters,
            'stats' => $this->stats($today),
            'queue' => $this->operationalQueue($today),
            'quickLinks' => $this->quickLinks(),
            'recentMembers' => $this->recentMembers(),
            'recentPayments' => $this->recentPayments(),
            'paymentMembers' => $this->paymentMembers(),
            'paymentPackages' => $this->paymentPackages(),
            'paymentTrainers' => $this->paymentTrainers(),
            'todayBookings' => $this->todayBookings($today),
            'bookingMembers' => $this->paymentMembers(),
            'bookingSchedules' => $this->bookingSchedules(),
            'todayCheckIns' => $this->todayCheckIns($today),
            'checkInCandidates' => $this->checkInCandidates($today),
            'moduleSummaries' => $this->moduleSummaries($today),
            'modules' => $this->modules($today, $user, $filters, $activeModule),
            'settings' => $this->settings(),
            'editableSettings' => ['fields' => $this->editableSettings->fields(), 'values' => $this->editableSettings->values()],
            'activityLogs' => $this->latestActivityLogs(),
            'activityUsers' => $this->activityUsers(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function navigation(): array
    {
        return [
            ['label' => 'Ringkasan', 'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
            ]],
            ['label' => 'Operasional', 'items' => [
                ['label' => 'Check-in', 'route' => 'admin.check-in', 'active' => 'admin.check-in', 'icon' => 'qr'],
                ['label' => 'Booking', 'route' => 'admin.booking', 'active' => 'admin.booking', 'icon' => 'calendar'],
                ['label' => 'Notifikasi', 'route' => 'admin.notifications', 'active' => 'admin.notifications', 'icon' => 'bell'],
            ]],
            ['label' => 'Anggota & Paket', 'items' => [
                ['label' => 'Anggota', 'route' => 'admin.members', 'active' => 'admin.members', 'icon' => 'members'],
                ['label' => 'Paket', 'route' => 'admin.packages', 'active' => 'admin.packages', 'icon' => 'package'],
            ]],
            ['label' => 'Aktivitas', 'items' => [
                ['label' => 'Kelas', 'route' => 'admin.classes', 'active' => 'admin.classes', 'icon' => 'activity'],
            ]],
            ['label' => 'Keuangan', 'items' => [
                ['label' => 'Pembayaran', 'route' => 'admin.payments', 'active' => 'admin.payments', 'icon' => 'receipt'],
            ]],
            ['label' => 'Produk & Konten', 'items' => [
                ['label' => 'Produk', 'route' => 'admin.products', 'active' => 'admin.products', 'icon' => 'box'],
                ['label' => 'Galeri', 'route' => 'admin.gallery', 'active' => 'admin.gallery', 'icon' => 'image'],
                ['label' => 'Testimoni', 'route' => 'admin.testimonials', 'active' => 'admin.testimonials', 'icon' => 'message'],
                ['label' => 'Promo', 'route' => 'admin.promos', 'active' => 'admin.promos', 'icon' => 'tag'],
            ]],
            ['label' => 'Tim & Sistem', 'items' => [
                ['label' => 'Trainer', 'route' => 'admin.trainers', 'active' => 'admin.trainers', 'icon' => 'trainer'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'active' => 'admin.reports', 'icon' => 'chart'],
                ['label' => 'Audit Log', 'route' => 'admin.audit-log', 'active' => 'admin.audit-log', 'icon' => 'clipboard-list'],
                ['label' => 'Pengaturan', 'route' => 'admin.settings', 'active' => 'admin.settings', 'icon' => 'settings'],
            ]],
            ['label' => 'Akun', 'items' => [
                ['label' => 'Profil Admin', 'route' => 'admin.profile', 'active' => 'admin.profile', 'icon' => 'user'],
            ]],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function pageDefinitions(): array
    {
        return [
            'check-in' => $this->page('check-in', 'Check-in', 'Pantau check-in gym harian dan proses masuk member dari QR atau input manual.', 'admin.check-in', 'checkIns'),
            'booking' => $this->page('booking', 'Booking Kelas', 'Kelola booking kelas hari ini, status peserta, dan kapasitas jadwal.', 'admin.booking', 'bookings'),
            'notifications' => $this->page('notifications', 'Notifikasi', 'Pantau area notifikasi operasional untuk member, booking, dan pembayaran.', 'admin.notifications', 'notifications'),
            'members' => $this->page('members', 'Anggota', 'Kelola daftar member, status akun, dan kode keanggotaan.', 'admin.members', 'members') + ['createResource' => 'members'],
            'packages' => $this->page('packages', 'Paket', 'Kelola katalog membership dan paket sesi yang tersedia di website.', 'admin.packages', 'packages') + ['createResource' => 'packages'],
            'classes' => $this->page('classes', 'Kelas', 'Kelola kelas, jadwal, trainer, dan kapasitas peserta.', 'admin.classes', 'classes') + ['createResource' => 'classes', 'secondaryCreateResource' => 'class-schedules', 'secondaryCreateLabel' => 'Tambah Jadwal'],
            'payments' => $this->page('payments', 'Pembayaran', 'Kelola transaksi membership, paket sesi, dan pembayaran langsung di kasir.', 'admin.payments', 'payments'),
            'products' => $this->page('products', 'Produk', 'Kelola katalog produk, stok, harga, dan status tampil di website.', 'admin.products', 'products') + ['createResource' => 'products', 'secondaryCreateResource' => 'product-categories', 'secondaryCreateLabel' => 'Tambah Kategori'],
            'gallery' => $this->page('gallery', 'Galeri', 'Kelola foto fasilitas dan konten visual yang tampil di website.', 'admin.gallery', 'gallery') + ['createResource' => 'gallery'],
            'testimonials' => $this->page('testimonials', 'Testimoni', 'Kelola testimoni member dan status publikasinya.', 'admin.testimonials', 'testimonials') + ['createResource' => 'testimonials'],
            'promos' => $this->page('promos', 'Promo', 'Kelola promo, periode tayang, dan nilai diskon.', 'admin.promos', 'promos') + ['createResource' => 'promos'],
            'trainers' => $this->page('trainers', 'Trainer', 'Kelola trainer, spesialisasi, sertifikasi, dan status aktif.', 'admin.trainers', 'trainers') + ['createResource' => 'trainers'],
            'reports' => $this->page('reports', 'Laporan', 'Lihat ringkasan operasional berdasarkan periode yang dipilih.', 'admin.reports', 'reports'),
            'audit-log' => $this->page('audit-log', 'Audit Log', 'Pantau jejak perubahan sistem tanpa membuka nilai sensitif.', 'admin.audit-log', 'auditLogs'),
            'settings' => $this->page('settings', 'Pengaturan', 'Kelola informasi publik website dan pantau konfigurasi dengan nilai sensitif tersamarkan.', 'admin.settings', 'settings'),
            'profile' => $this->page('profile', 'Profil Admin', 'Lihat ringkasan akun admin yang sedang masuk dan status role.', 'admin.profile', 'profile'),
        ];
    }

    private function page(string $key, string $title, string $description, string $route, string $moduleKey): array
    {
        return compact('key', 'title', 'description', 'route', 'moduleKey');
    }

    public function reportRowsFor(array $filters = []): Collection
    {
        $filters = $this->normaliseFilters($filters);
        [$from, $to] = $this->dateRange($filters);

        return collect([
            ['Member aktif', (string) Member::query()->where('status', 'active')->count(), 'Status akun member saat laporan dibuat.'],
            ['Membership aktif', (string) Membership::query()->where('status', 'active')->whereDate('end_date', '>=', $to)->count(), 'Membership aktif sampai akhir periode.'],
            ['Pembayaran periode ini', $this->money(Payment::query()->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->sum('amount')), 'Total nominal transaksi pada periode terpilih.'],
            ['Booking periode ini', (string) ClassEnrollment::query()->whereBetween('session_date', [$from->toDateString(), $to->toDateString()])->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'Booking aktif pada periode terpilih.'],
            ['Check-in periode ini', (string) GymCheckIn::query()->whereBetween('check_in_date', [$from->toDateString(), $to->toDateString()])->count(), 'Aktivitas masuk gym pada periode terpilih.'],
        ]);
    }

    /** @return array<int, array<string, string>> */
    private function stats(string $today): array
    {
        return [
            ['label' => 'Member Aktif', 'value' => (string) Member::query()->where('status', 'active')->count(), 'description' => Membership::query()->where('status', 'active')->whereDate('end_date', '>=', $today)->count().' membership aktif'],
            ['label' => 'Booking Hari Ini', 'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Kelas dan booking yang perlu dipantau hari ini.'],
            ['label' => 'Pembayaran Pending', 'value' => (string) Payment::query()->whereIn('status', $this->pendingPaymentStatuses())->count(), 'description' => 'Butuh verifikasi atau tindak lanjut admin.'],
            ['label' => 'Produk Aktif', 'value' => (string) Product::query()->where('is_active', true)->count(), 'description' => Product::query()->where('is_active', true)->where('stock', '<=', 3)->count().' produk stok rendah'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function operationalQueue(string $today): array
    {
        return [
            ['label' => 'Pembayaran menunggu', 'value' => (string) Payment::query()->whereIn('status', $this->pendingPaymentStatuses())->count(), 'description' => 'Prioritas validasi transfer dan status Midtrans.', 'route' => 'admin.payments'],
            ['label' => 'Booking kelas hari ini', 'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Peserta kelas yang perlu dicek kapasitasnya.', 'route' => 'admin.booking'],
            ['label' => 'Check-in hari ini', 'value' => (string) GymCheckIn::query()->whereDate('check_in_date', $today)->count(), 'description' => 'Aktivitas masuk gym yang sudah tercatat.', 'route' => 'admin.check-in'],
        ];
    }

    /** @return array<int, array<string, string>> */
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
        return Member::query()->with('user')->latest('created_at')->limit(8)->get();
    }

    private function recentPayments(): Collection
    {
        return Payment::query()->with(['member.user'])->latest('created_at')->limit(8)->get();
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
        return GymCheckIn::query()->with(['member.user'])->whereDate('check_in_date', $today)->latest('check_in_at')->limit(8)->get();
    }

    private function paymentMembers(): Collection
    {
        return Member::query()->with('user')->where('status', 'active')->latest('created_at')->limit(200)->get();
    }

    private function paymentPackages(): Collection
    {
        return Package::query()->where('is_active', true)->orderBy('package_kind')->orderBy('name')->limit(200)->get();
    }

    private function paymentTrainers(): Collection
    {
        return Trainer::query()->where('is_active', true)->orderBy('name')->limit(100)->get();
    }

    private function bookingSchedules(): Collection
    {
        return ClassSchedule::query()
            ->with(['gymClass', 'trainer'])
            ->where('is_active', true)
            ->whereHas('gymClass', fn (EloquentBuilder $query) => $query->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(200)
            ->get();
    }

    private function checkInCandidates(string $today): Collection
    {
        return Member::query()
            ->with('user')
            ->where('status', 'active')
            ->whereHas('memberships', fn (EloquentBuilder $query) => $query->where('status', 'active')->whereDate('end_date', '>=', $today))
            ->latest('id')
            ->limit(200)
            ->get();
    }

    /** @return array<string, array<string, mixed>> */
    private function modules(string $today, User $user, array $filters, ?string $activeModule): array
    {
        return [
            'checkIns' => $this->checkInsModule($today, $filters, $activeModule === 'checkIns'),
            'bookings' => $this->bookingsModule($today, $filters, $activeModule === 'bookings'),
            'notifications' => $this->notificationsModule(),
            'members' => $this->membersModule($filters, $activeModule === 'members'),
            'packages' => $this->packagesModule($filters, $activeModule === 'packages'),
            'classes' => $this->classesModule($filters, $activeModule === 'classes'),
            'payments' => $this->paymentsModule($filters, $activeModule === 'payments'),
            'products' => $this->productsModule($filters, $activeModule === 'products'),
            'gallery' => $this->galleryModule($filters, $activeModule === 'gallery'),
            'testimonials' => $this->testimonialsModule($filters, $activeModule === 'testimonials'),
            'promos' => $this->promosModule($filters, $activeModule === 'promos'),
            'trainers' => $this->trainersModule($filters, $activeModule === 'trainers'),
            'reports' => $this->reportsModule($filters),
            'auditLogs' => $this->auditLogsModule($filters, $activeModule === 'auditLogs'),
            'settings' => $this->settingsModule($filters, $activeModule === 'settings'),
            'profile' => $this->profileModule($user),
        ];
    }

    private function checkInsModule(string $today, array $filters, bool $loadRows): array
    {
        $module = $this->module('Check-in Terbaru', 'Data masuk gym hari ini dari QR atau petugas.', 'Belum ada check-in hari ini.', ['Member', 'Tanggal', 'Jam', 'Metode']);
        if (! $loadRows) {
            return $module;
        }

        $query = GymCheckIn::query()->with(['member.user'])->whereDate('check_in_date', $today)->latest('check_in_at');
        $this->applyCheckInSearch($query, $filters['q']);

        return $this->withPaginatedRows($module, $query, fn (GymCheckIn $checkIn): array => ['cells' => [
            $checkIn->member?->user?->name ?? $checkIn->member?->member_code ?? '-',
            $checkIn->check_in_date?->translatedFormat('d M Y') ?? '-',
            $checkIn->check_in_at?->format('H:i') ?? '-',
            $this->headline($checkIn->method),
        ], 'actions' => []], $filters);
    }

    private function bookingsModule(string $today, array $filters, bool $loadRows): array
    {
        $options = $this->headlineStatusOptions(['booked', 'confirmed', 'attended', 'cancelled']);
        $module = array_replace($this->module('Booking Hari Ini', 'Peserta yang tercatat pada jadwal hari ini.', 'Belum ada booking kelas hari ini.', ['Member', 'Kelas', 'Jam', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = ClassEnrollment::query()->with(['member.user', 'schedule.gymClass', 'schedule.trainer'])->whereDate('session_date', $today)->latest('created_at');
        $this->applyBookingSearch($query, $filters['q']);
        $this->applyExactStatusFilter($query, $filters['status'], array_keys($options));

        return $this->withPaginatedRows($module, $query, fn (ClassEnrollment $enrollment): array => ['cells' => [
            $enrollment->member?->user?->name ?? $enrollment->member?->member_code ?? '-',
            $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym',
            substr((string) $enrollment->schedule?->start_time, 0, 5),
            $this->headline($enrollment->status),
        ], 'actions' => $this->bookingActions($enrollment)], $filters, $options);
    }

    private function notificationsModule(): array
    {
        return array_replace($this->module('Kesiapan Notifikasi', 'Area pengingat member, booking, dan pembayaran.', 'Belum ada daftar notifikasi operasional.', ['Area', 'Status', 'Catatan']), [
            'view' => 'admin.partials.notifications-page',
            'rows' => collect([
                ['area' => 'Membership', 'status' => 'Siap', 'kind' => 'success', 'note' => 'Pengingat masa aktif dan renewal.'],
                ['area' => 'Booking', 'status' => 'Siap', 'kind' => 'success', 'note' => 'Reminder jadwal kelas dan perubahan kuota.'],
                ['area' => 'Pembayaran', 'status' => 'Siap', 'kind' => 'success', 'note' => 'Follow-up pembayaran menunggu.'],
            ]),
        ]);
    }

    private function membersModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Daftar Anggota', 'Akun member, kode keanggotaan, dan status akses.', 'Belum ada data member.', ['Nama', 'Kode', 'Status', 'Bergabung']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Member::query()->with('user')->latest('created_at');
        $this->applyMemberSearch($query, $filters['q']);
        $this->applyExactStatusFilter($query, $filters['status'], array_keys($options));

        return $this->withPaginatedRows($module, $query, fn (Member $member): array => $this->actionRow('members', $member, [
            $member->user?->name ?? '-',
            $member->member_code,
            $this->headline($member->status),
            $member->joined_at?->translatedFormat('d M Y') ?? '-',
        ], 'status'), $filters, $options);
    }

    private function packagesModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Katalog Paket', 'Paket membership dan paket sesi dari database layanan.', 'Belum ada paket.', ['Paket', 'Jenis', 'Harga', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Package::query()->orderByDesc('is_active')->orderBy('package_kind')->orderBy('price');
        $this->applySimpleSearch($query, $filters['q'], ['name', 'package_kind', 'type', 'category', 'description']);
        $this->applyBooleanStatusFilter($query, 'is_active', $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Package $package): array => $this->actionRow('packages', $package, [
            $package->name,
            $this->headline($package->package_kind),
            $this->money($package->promo_price ?? $package->price),
            $package->is_active ? 'Aktif' : 'Nonaktif',
        ]), $filters, $options);
    }

    private function classesModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Jadwal Kelas', 'Kelas, jadwal, trainer, kapasitas, dan status tampil.', 'Belum ada jadwal kelas.', ['Kelas', 'Hari', 'Jam', 'Trainer', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = ClassSchedule::query()->with(['gymClass', 'trainer'])->orderByDesc('is_active')->orderBy('day_of_week')->orderBy('start_time');
        $this->applyClassScheduleSearch($query, $filters['q']);
        $this->applyBooleanStatusFilter($query, 'is_active', $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (ClassSchedule $schedule): array => $this->actionRow('class-schedules', $schedule, [
            $schedule->gymClass?->name ?? 'Kelas Platinum Gym',
            $this->dayLabel((int) $schedule->day_of_week),
            substr((string) $schedule->start_time, 0, 5).' - '.substr((string) $schedule->end_time, 0, 5),
            $schedule->trainer?->name ?? '-',
            $schedule->is_active ? 'Aktif' : 'Nonaktif',
        ]), $filters, $options);
    }

    private function paymentsModule(array $filters, bool $loadRows): array
    {
        $options = $this->headlineStatusOptions(['pending', 'waiting_payment', 'waiting_confirmation', 'unpaid', 'paid', 'rejected', 'failed', 'expired', 'cancelled']);
        $module = array_replace($this->module('Daftar Pembayaran', 'Transaksi membership, paket sesi, dan kelas.', 'Belum ada pembayaran.', ['Kode', 'Member', 'Nominal', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Payment::query()->with(['member.user'])->latest('created_at');
        $this->applyPaymentSearch($query, $filters['q']);
        $this->applyExactStatusFilter($query, $filters['status'], array_keys($options));

        return $this->withPaginatedRows($module, $query, fn (Payment $payment): array => ['cells' => [
            $payment->payment_code,
            $payment->member?->user?->name ?? $payment->member?->member_code ?? '-',
            $this->money($payment->amount),
            $this->headline($payment->status),
        ], 'actions' => []], $filters, $options);
    }

    private function productsModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Produk Katalog', 'Produk yang tersedia untuk referensi pembelian di lokasi.', 'Belum ada produk.', ['Produk', 'Harga', 'Stok', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Product::query()->with('category')->orderByDesc('is_active')->orderBy('name');
        $this->applyProductSearch($query, $filters['q']);
        $this->applyBooleanStatusFilter($query, 'is_active', $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Product $product): array => $this->actionRow('products', $product, [
            $product->name,
            $this->money($product->price),
            (string) $product->stock,
            $product->is_active ? 'Aktif' : 'Nonaktif',
        ]), $filters, $options);
    }

    private function galleryModule(array $filters, bool $loadRows): array
    {
        $options = ['published' => 'Tayang', 'draft' => 'Draft'];
        $module = array_replace($this->module('Galeri Website', 'Konten visual yang mengisi halaman galeri publik.', 'Belum ada galeri.', ['Judul', 'Caption', 'Urutan', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Gallery::query()->orderBy('sort_order')->latest('created_at');
        $this->applySimpleSearch($query, $filters['q'], ['title', 'caption', 'image_alt']);
        $this->applyPublishedStatusFilter($query, $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Gallery $gallery): array => $this->actionRow('gallery', $gallery, [
            $gallery->title ?? 'Galeri Platinum Gym',
            $gallery->caption ?? '-',
            (string) $gallery->sort_order,
            $gallery->is_published ? 'Tayang' : 'Draft',
        ], 'is_published'), $filters, $options);
    }

    private function testimonialsModule(array $filters, bool $loadRows): array
    {
        $options = ['published' => 'Tayang', 'draft' => 'Draft'];
        $module = array_replace($this->module('Testimoni Member', 'Cerita member yang tampil di website publik.', 'Belum ada testimoni.', ['Nama', 'Role', 'Rating', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Testimonial::query()->latest('created_at');
        $this->applySimpleSearch($query, $filters['q'], ['name', 'role', 'content']);
        $this->applyPublishedStatusFilter($query, $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Testimonial $testimonial): array => $this->actionRow('testimonials', $testimonial, [
            $testimonial->name,
            $testimonial->role ?? '-',
            $testimonial->rating.'/5',
            $testimonial->is_published ? 'Tayang' : 'Draft',
        ], 'is_published'), $filters, $options);
    }

    private function promosModule(array $filters, bool $loadRows): array
    {
        $options = ['published' => 'Tayang', 'draft' => 'Draft'];
        $module = array_replace($this->module('Promo Website', 'Promo yang tampil pada website dan katalog layanan.', 'Belum ada promo.', ['Promo', 'Periode', 'Diskon', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Promo::query()->latest('created_at');
        $this->applySimpleSearch($query, $filters['q'], ['title', 'slug', 'description']);
        $this->applyPublishedStatusFilter($query, $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Promo $promo): array => $this->actionRow('promos', $promo, [
            $promo->title,
            ($promo->starts_at?->translatedFormat('d M Y') ?? '-').' - '.($promo->ends_at?->translatedFormat('d M Y') ?? '-'),
            $this->promoValue($promo->discount_type, $promo->discount_value),
            $promo->is_published ? 'Tayang' : 'Draft',
        ], 'is_published'), $filters, $options);
    }

    private function trainersModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Trainer', 'Tim trainer dan spesialisasi yang tersedia.', 'Belum ada trainer.', ['Nama', 'Spesialisasi', 'Pengalaman', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Trainer::query()->orderByDesc('is_active')->orderBy('name');
        $this->applySimpleSearch($query, $filters['q'], ['name', 'specialization', 'bio']);
        $this->applyBooleanStatusFilter($query, 'is_active', $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Trainer $trainer): array => $this->actionRow('trainers', $trainer, [
            $trainer->name,
            $trainer->specialization ?? '-',
            filled($trainer->experience_years) ? $trainer->experience_years.' tahun' : '-',
            $trainer->is_active ? 'Aktif' : 'Nonaktif',
        ]), $filters, $options);
    }

    private function reportsModule(array $filters): array
    {
        return array_replace($this->module('Ringkasan Laporan', 'Metrik operasional sesuai periode filter.', 'Belum ada ringkasan laporan.', ['Metrik', 'Nilai', 'Catatan']), [
            'rows' => $this->reportRowsFor($filters),
        ]);
    }

    private function auditLogsModule(array $filters, bool $loadRows): array
    {
        $module = $this->module('Activity Log Terbaru', 'Jejak perubahan sistem dari paket Spatie Activitylog.', 'Belum ada activity log.', ['Aktivitas', 'Event', 'Subjek', 'Waktu']);
        if (! $loadRows || ! Schema::hasTable('activity_log')) {
            return $module;
        }

        $query = DB::table('activity_log')->select(['id', 'description', 'event', 'subject_type', 'causer_id', 'created_at'])->latest('created_at');
        $this->applyActivityLogFilters($query, $filters);

        return $this->withPaginatedRows($module, $query, fn (object $log): array => ['cells' => [
            (string) ($log->description ?? '-'),
            $this->headline($log->event ?? '-'),
            class_basename((string) ($log->subject_type ?? '-')),
            filled($log->created_at) ? (string) $log->created_at : '-',
        ], 'actions' => []], $filters);
    }

    private function settingsModule(array $filters, bool $loadRows): array
    {
        $module = $this->module('Pengaturan Website', 'Nilai sensitif otomatis disamarkan agar aman dilihat dari admin.', 'Belum ada setting.', ['Key', 'Group', 'Type', 'Value']);
        if (! $loadRows) {
            return $module;
        }

        $query = Setting::query()->orderBy('group')->orderBy('key');
        $this->applySimpleSearch($query, $filters['q'], ['key', 'group', 'type', 'value']);

        return $this->withPaginatedRows($module, $query, fn (Setting $setting): array => ['cells' => [
            $setting->key,
            $setting->group,
            $setting->type,
            $this->settingDisplayValue($setting->key, $setting->value),
        ], 'actions' => []], $filters);
    }

    private function profileModule(User $user): array
    {
        return array_replace($this->module('Profil Admin', 'Data aman akun admin saat ini.', 'Profil admin belum tersedia.', ['Field', 'Nilai', 'Catatan']), [
            'rows' => collect([
                ['Nama', $user->name, 'Akun yang sedang login.'],
                ['Email', $user->email, 'Dipakai untuk autentikasi.'],
                ['Telepon', $user->phone ?? '-', 'Opsional.'],
                ['Role', $user->getRoleNames()->implode(', ') ?: '-', 'Akses admin dijaga middleware role.'],
                ['Login terakhir', $user->last_login_at?->translatedFormat('d M Y H:i') ?? '-', 'Diisi saat login berhasil.'],
            ]),
        ]);
    }

    /** @return array<int, array<string, string>> */
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

    private function module(string $title, string $description, string $empty, array $columns): array
    {
        return ['title' => $title, 'description' => $description, 'empty' => $empty, 'columns' => $columns, 'rows' => collect(), 'paginator' => null, 'statusOptions' => [], 'filters' => []];
    }

    private function withPaginatedRows(array $module, EloquentBuilder|QueryBuilder $query, callable $mapper, array $filters, array $statusOptions = []): array
    {
        $paginator = $query->paginate(self::ADMIN_PER_PAGE)->withQueryString();

        return array_replace($module, ['rows' => $paginator->getCollection()->map($mapper)->values(), 'paginator' => $paginator, 'statusOptions' => $statusOptions, 'filters' => $filters]);
    }

    private function actionRow(string $resource, Model $model, array $cells, string $toggleField = 'is_active'): array
    {
        return ['cells' => $cells, 'actions' => [
            ['label' => 'Edit', 'url' => route('admin.resources.edit', ['resource' => $resource, 'id' => $model->getKey()]), 'method' => 'GET', 'variant' => 'secondary'],
            ['label' => $this->toggleLabel($model, $toggleField), 'url' => route('admin.resources.toggle', ['resource' => $resource, 'id' => $model->getKey()]), 'method' => 'PATCH', 'variant' => 'secondary'],
        ]];
    }

    private function bookingActions(ClassEnrollment $enrollment): array
    {
        if (in_array($enrollment->status, ['cancelled', 'canceled'], true)) {
            return [];
        }

        $actions = [];
        if ($enrollment->status !== 'confirmed') {
            $actions[] = ['label' => 'Konfirmasi', 'url' => route('admin.booking.confirm', $enrollment), 'method' => 'POST', 'variant' => 'primary'];
        }
        $actions[] = ['label' => 'Batalkan', 'url' => route('admin.booking.cancel', $enrollment), 'method' => 'POST', 'variant' => 'secondary'];

        return $actions;
    }

    private function toggleLabel(Model $model, string $field): string
    {
        $value = $model->getAttribute($field);
        if ($field === 'status') {
            return $value === 'active' ? 'Nonaktifkan' : 'Aktifkan';
        }
        if ($field === 'is_published') {
            return (bool) $value ? 'Jadikan Draft' : 'Tayangkan';
        }

        return (bool) $value ? 'Nonaktifkan' : 'Aktifkan';
    }

    private function settings(): Collection
    {
        return Setting::query()->orderBy('group')->orderBy('key')->limit(80)->get()->map(fn (Setting $setting): array => [
            'key' => $setting->key,
            'group' => $setting->group,
            'type' => $setting->type,
            'value' => $this->settingDisplayValue($setting->key, $setting->value),
        ]);
    }

    private function latestActivityLogs(): Collection
    {
        if (! Schema::hasTable('activity_log')) {
            return collect();
        }

        return DB::table('activity_log')->select(['description', 'event', 'subject_type', 'created_at'])->latest('created_at')->limit(12)->get();
    }

    private function activityUsers(): Collection
    {
        if (! Schema::hasTable('activity_log')) {
            return collect();
        }

        $ids = DB::table('activity_log')->where('causer_type', User::class)->whereNotNull('causer_id')->distinct()->pluck('causer_id');

        return $ids->isEmpty() ? collect() : User::query()->select(['id', 'name'])->whereIn('id', $ids)->orderBy('name')->get();
    }

    private function applyMemberSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('member_code', 'like', $like)
            ->orWhere('status', 'like', $like)
            ->orWhereHas('user', fn (EloquentBuilder $userQuery) => $userQuery->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)));
    }

    private function applyClassScheduleSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('day_of_week', 'like', $like)
            ->orWhere('start_time', 'like', $like)
            ->orWhere('end_time', 'like', $like)
            ->orWhereHas('gymClass', fn (EloquentBuilder $classQuery) => $classQuery->where('name', 'like', $like)->orWhere('class_type', 'like', $like)->orWhere('access_type', 'like', $like))
            ->orWhereHas('trainer', fn (EloquentBuilder $trainerQuery) => $trainerQuery->where('name', 'like', $like)->orWhere('specialization', 'like', $like)));
    }

    private function applyPaymentSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('payment_code', 'like', $like)
            ->orWhere('method', 'like', $like)
            ->orWhere('status', 'like', $like)
            ->orWhereHas('member', fn (EloquentBuilder $memberQuery) => $memberQuery
                ->where('member_code', 'like', $like)
                ->orWhereHas('user', fn (EloquentBuilder $userQuery) => $userQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))));
    }

    private function applyProductSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('name', 'like', $like)
            ->orWhere('slug', 'like', $like)
            ->orWhere('description', 'like', $like)
            ->orWhereHas('category', fn (EloquentBuilder $categoryQuery) => $categoryQuery->where('name', 'like', $like)));
    }

    private function applyBookingSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('status', 'like', $like)
            ->orWhereHas('member', fn (EloquentBuilder $memberQuery) => $memberQuery->where('member_code', 'like', $like)->orWhereHas('user', fn (EloquentBuilder $userQuery) => $userQuery->where('name', 'like', $like)))
            ->orWhereHas('schedule.gymClass', fn (EloquentBuilder $classQuery) => $classQuery->where('name', 'like', $like)));
    }

    private function applyCheckInSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(fn (EloquentBuilder $query) => $query
            ->where('method', 'like', $like)
            ->orWhereHas('member', fn (EloquentBuilder $memberQuery) => $memberQuery->where('member_code', 'like', $like)->orWhereHas('user', fn (EloquentBuilder $userQuery) => $userQuery->where('name', 'like', $like))));
    }

    private function applySimpleSearch(EloquentBuilder $query, string $search, array $columns): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(function (EloquentBuilder $query) use ($columns, $like): void {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', $like);
            }
        });
    }

    private function applyExactStatusFilter(EloquentBuilder $query, string $status, array $allowed = []): void
    {
        if (filled($status) && ($allowed === [] || in_array($status, $allowed, true))) {
            $query->where('status', $status);
        }
    }

    private function applyBooleanStatusFilter(EloquentBuilder $query, string $column, string $status): void
    {
        if ($status === 'active') {
            $query->where($column, true);
        } elseif ($status === 'inactive') {
            $query->where($column, false);
        }
    }

    private function applyPublishedStatusFilter(EloquentBuilder $query, string $status): void
    {
        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        }
    }

    private function applyActivityLogFilters(QueryBuilder $query, array $filters): void
    {
        [$from, $to] = $this->dateRange($filters);
        $query->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        if (filled($filters['event'])) {
            $query->where('event', $filters['event']);
        }
        if (filled($filters['causer_id'])) {
            $query->where('causer_id', (int) $filters['causer_id']);
        }
        if (filled($filters['q'])) {
            $like = $this->like($filters['q']);
            $query->where(fn (QueryBuilder $query) => $query->where('description', 'like', $like)->orWhere('event', 'like', $like)->orWhere('subject_type', 'like', $like));
        }
    }

    private function settingDisplayValue(string $key, ?string $value): string
    {
        if ($this->isSensitiveSetting($key)) {
            return filled($value) ? 'Tersamarkan' : '-';
        }

        return filled($value) ? str($value)->limit(120)->toString() : '-';
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

    /** @return array<int, string> */
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

        return $type === 'percentage' ? number_format((float) $value, 0, ',', '.').'%' : $this->money($value);
    }

    /** @return array<string, string> */
    private function headlineStatusOptions(array $statuses): array
    {
        return collect($statuses)->mapWithKeys(fn (string $status): array => [$status => $this->headline($status)])->all();
    }

    /** @return array{q: string, status: string, date_from: string, date_to: string, event: string, causer_id: string, page: string} */
    private function normaliseFilters(array $filters): array
    {
        return [
            'q' => str((string) ($filters['q'] ?? ''))->squish()->limit(80, '')->toString(),
            'status' => str((string) ($filters['status'] ?? ''))->squish()->limit(40, '')->toString(),
            'date_from' => $this->parseDate($filters['date_from'] ?? null)?->toDateString() ?? '',
            'date_to' => $this->parseDate($filters['date_to'] ?? null)?->toDateString() ?? '',
            'event' => str((string) ($filters['event'] ?? ''))->squish()->limit(40, '')->toString(),
            'causer_id' => filled($filters['causer_id'] ?? null) ? (string) max(1, (int) $filters['causer_id']) : '',
            'page' => filled($filters['page'] ?? null) ? (string) max(1, (int) $filters['page']) : '',
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function dateRange(array $filters): array
    {
        $from = $this->parseDate($filters['date_from'] ?? null) ?? now()->startOfMonth();
        $to = $this->parseDate($filters['date_to'] ?? null) ?? now();

        return $from->greaterThan($to) ? [$to, $from] : [$from, $to];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function like(string $value): string
    {
        return '%'.$value.'%';
    }
}
