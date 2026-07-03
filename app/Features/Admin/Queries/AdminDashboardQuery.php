<?php

namespace App\Features\Admin\Queries;

use App\Features\Admin\Support\AdminEditableSettingRegistry;
use App\Features\Admin\Support\AdminOperationalRules;
use App\Features\Bookings\Support\BookingTimePolicy;
use App\Features\Classes\Support\ClassStaffPresenter;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymCheckIn;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\MemberPackageSessionUsage;
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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $paymentMembers = $this->paymentMembers();
        $paymentPackages = $this->paymentPackages();
        $bookingMembers = $this->bookingMembers();
        $bookingSchedules = $this->bookingSchedules();

        return [
            'admin' => $user,
            'filters' => $filters,
            'stats' => $this->stats($today),
            'queue' => $this->operationalQueue($today),
            'operationalTrend' => $this->operationalTrend(),
            'quickLinks' => $this->quickLinks(),
            'recentMembers' => $this->recentMembers(),
            'recentPayments' => $this->recentPayments(),
            'paymentMembers' => $paymentMembers,
            'paymentPackages' => $paymentPackages,
            'paymentTrainers' => $this->paymentTrainers(),
            'paymentPackageTrainerRules' => AdminOperationalRules::paymentPackageTrainerRules($paymentPackages),
            'paymentTrainerOptionsByPackage' => AdminOperationalRules::paymentTrainerOptionsByPackage($paymentPackages),
            'todayBookings' => $this->todayBookings($today),
            'bookingMembers' => $bookingMembers,
            'bookingSchedules' => $bookingSchedules,
            'bookingMemberScheduleAccess' => AdminOperationalRules::bookingMemberScheduleAccess($bookingMembers, $bookingSchedules),
            'todayCheckIns' => $this->todayCheckIns($today),
            'pendingApprovalCount' => $this->pendingStudentProofApprovalCount(),
            'moduleSummaries' => $this->moduleSummaries($today),
            'modules' => $this->modules($today, $user, $filters, $activeModule),
            'settings' => $this->settings(),
            'editableSettings' => [
                'fields' => $this->editableSettings->fields(),
                'groups' => $this->editableSettings->groups(),
                'values' => $this->editableSettings->values(),
            ],
            'activityLogs' => $this->latestActivityLogs(),
            'activityUsers' => $this->activityUsers(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function navigation(?int $pendingApprovalCount = null): array
    {
        $pendingApprovalCount ??= $this->pendingStudentProofApprovalCount();

        return [
            ['label' => 'Ringkasan', 'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
            ]],
            ['label' => 'Operasional', 'items' => [
                ['label' => 'Check-in', 'route' => 'admin.check-in', 'active' => 'admin.check-in', 'icon' => 'qr-scan'],
                ['label' => 'Booking', 'route' => 'admin.booking', 'active' => 'admin.booking', 'icon' => 'calendar-check'],
                ['label' => 'Notifikasi', 'route' => 'admin.notifications', 'active' => 'admin.notifications', 'icon' => 'bell', 'count' => $pendingApprovalCount],
            ]],
            ['label' => 'Anggota & Paket', 'items' => [
                ['label' => 'Anggota', 'route' => 'admin.members', 'active' => 'admin.members', 'icon' => 'members'],
                ['label' => 'Paket', 'route' => 'admin.packages', 'active' => 'admin.packages', 'icon' => 'membership-card'],
            ]],
            ['label' => 'Aktivitas', 'items' => [
                ['label' => 'Kelas', 'route' => 'admin.classes', 'active' => 'admin.classes', 'icon' => 'dumbbell'],
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
                ['label' => 'Trainer', 'route' => 'admin.trainers', 'active' => 'admin.trainers', 'icon' => 'coach'],
                ['label' => 'Laporan', 'route' => 'admin.reports', 'active' => 'admin.reports', 'icon' => 'chart'],
                ['label' => 'Audit Log', 'route' => 'admin.audit-log', 'active' => 'admin.audit-log', 'icon' => 'history'],
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
            'check-in' => $this->page('check-in', 'Check-in', 'Pantau check-in gym harian dan proses masuk member melalui QR kamera.', 'admin.check-in', 'checkIns'),
            'booking' => $this->page('booking', 'Booking Kelas', 'Kelola riwayat booking kelas, status peserta, dan kapasitas jadwal.', 'admin.booking', 'bookings'),
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
            'audit-log' => $this->page('audit-log', 'Audit Log', 'Pantau jejak perubahan penting yang tercatat di sistem.', 'admin.audit-log', 'auditLogs'),
            'settings' => $this->page('settings', 'Pengaturan', 'Kelola informasi publik website dan pantau konfigurasi dengan nilai sensitif tersamarkan.', 'admin.settings', 'settings'),
            'profile' => $this->page('profile', 'Profil Admin', 'Lihat ringkasan akun admin yang sedang masuk dan status peran.', 'admin.profile', 'profile'),
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
            ['Membership aktif', (string) Membership::query()->activeForAccess($to)->count(), 'Membership aktif dan siap akses sampai akhir periode.'],
            ['Pembayaran periode ini', $this->money(Payment::query()->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->sum('amount')), 'Total nominal transaksi pada periode terpilih.'],
            ['Booking periode ini', (string) ClassEnrollment::query()->whereBetween('session_date', [$from->toDateString(), $to->toDateString()])->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'Booking aktif pada periode terpilih.'],
            ['Check-in periode ini', (string) GymCheckIn::query()->whereBetween('check_in_date', [$from->toDateString(), $to->toDateString()])->count(), 'Aktivitas masuk gym pada periode terpilih.'],
        ]);
    }

    /** @return array<int, array<string, string>> */
    private function stats(string $today): array
    {
        return [
            ['label' => 'Member Aktif', 'value' => (string) Member::query()->where('status', 'active')->count(), 'description' => Membership::query()->activeForAccess($today)->count().' membership sedang aktif'],
            ['label' => 'Booking hari ini', 'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Booking kelas yang masuk untuk hari ini.'],
            ['label' => 'Menunggu Pembayaran', 'value' => (string) Payment::query()->whereIn('status', $this->pendingPaymentStatuses())->count(), 'description' => 'Pembayaran yang belum selesai dicek.'],
            ['label' => 'Produk Aktif', 'value' => (string) Product::query()->where('is_active', true)->count(), 'description' => Product::query()->where('is_active', true)->where('stock', '<=', 3)->count().' produk perlu dicek stoknya'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function operationalQueue(string $today): array
    {
        return [
            ['label' => 'Pembayaran menunggu', 'value' => (string) Payment::query()->whereIn('status', $this->pendingPaymentStatuses())->count(), 'description' => 'Pembayaran yang perlu dicek atau dikonfirmasi.', 'route' => 'admin.payments'],
            ['label' => 'Booking hari ini', 'value' => (string) ClassEnrollment::query()->whereDate('session_date', $today)->whereNotIn('status', ['cancelled', 'canceled'])->count(), 'description' => 'Booking kelas yang terjadwal hari ini.', 'route' => 'admin.booking'],
            ['label' => 'Check-in hari ini', 'value' => (string) GymCheckIn::query()->whereDate('check_in_date', $today)->count(), 'description' => 'Member yang sudah tercatat check-in hari ini.', 'route' => 'admin.check-in'],
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
            ->withExists('attendance')
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

    private function bookingMembers(): Collection
    {
        return Member::query()
            ->with(['user', 'memberships.package', 'packageSessions.package'])
            ->where('status', 'active')
            ->latest('created_at')
            ->limit(200)
            ->get();
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

    /** @return array<string, array<string, mixed>> */
    private function modules(string $today, User $user, array $filters, ?string $activeModule): array
    {
        return [
            'checkIns' => $this->checkInsModule($today, $filters, $activeModule === 'checkIns'),
            'bookings' => $this->bookingsModule($today, $filters, $activeModule === 'bookings'),
            'notifications' => $this->notificationsModule($filters),
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
        [$from, $to] = $this->dateRange($filters);
        $displayFilters = array_replace($filters, [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ]);

        $module = array_replace(
            $this->module('Riwayat Check-in & Sesi', 'Data check-in dan penggunaan sesi member dari QR kamera dan aksi admin terkonfirmasi.', 'Belum ada riwayat check-in pada periode ini.', ['Member', 'Tanggal', 'Jam', 'Paket', 'Sisa Sesi', 'Aktivitas']),
            ['dateFilters' => true, 'pillColumns' => ['Aktivitas'], 'searchPlaceholder' => 'Cari member, kode, paket, aktivitas...']
        );
        if (! $loadRows) {
            return $module;
        }

        $rows = $this->checkInActivityRows($from, $to);
        if (filled($filters['q'])) {
            $rows = $rows->filter(fn (array $row): bool => str_contains($row['search'], str($filters['q'])->lower()->toString()))->values();
        }

        $paginator = $this->paginateAdminRows($rows, $displayFilters);

        return array_replace($module, [
            'rows' => $paginator->getCollection(),
            'paginator' => $paginator,
            'filters' => $displayFilters,
        ]);
    }

    private function bookingsModule(string $today, array $filters, bool $loadRows): array
    {
        $options = $this->headlineStatusOptions(['booked', 'confirmed', 'attended', 'cancelled']);
        $module = array_replace($this->module('Riwayat Booking Kelas', 'Pantau semua booking kelas, status peserta, dan tindakan yang masih aman dilakukan.', 'Belum ada riwayat booking kelas.', ['Member', 'Kelas', 'Tanggal', 'Jam', 'Status']), [
            'statusOptions' => $options,
            'dateFilters' => true,
            'searchPlaceholder' => 'Cari member, kode, kelas, status...',
        ]);
        if (! $loadRows) {
            return $module;
        }

        $query = ClassEnrollment::query()
            ->with(['member.user', 'schedule.gymClass', 'schedule.trainer'])
            ->withExists('attendance')
            ->latest('created_at');
        if (filled($filters['date_from'])) {
            $query->whereDate('session_date', '>=', $filters['date_from']);
        }
        if (filled($filters['date_to'])) {
            $query->whereDate('session_date', '<=', $filters['date_to']);
        }
        $this->applyBookingSearch($query, $filters['q']);
        $this->applyExactStatusFilter($query, $filters['status'], array_keys($options));

        return $this->withPaginatedRows($module, $query, fn (ClassEnrollment $enrollment): array => ['cells' => [
            $enrollment->member?->user?->name ?? $enrollment->member?->member_code ?? '-',
            $enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym',
            $enrollment->session_date?->translatedFormat('d M Y') ?? '-',
            substr((string) $enrollment->schedule?->start_time, 0, 5),
            $this->statusLabel($enrollment->status),
        ], 'actions' => $this->bookingActions($enrollment)], $filters, $options);
    }

    private function notificationsModule(array $filters): array
    {
        $from = $this->parseDate($filters['date_from'] ?? null) ?? Carbon::create(2000, 1, 1)->startOfDay();
        $to = $this->parseDate($filters['date_to'] ?? null) ?? now();
        $displayFilters = array_replace($filters, [
            'date_from' => $filters['date_from'] ?? '',
            'date_to' => $filters['date_to'] ?? '',
        ]);
        $statusOptions = [
            'student_proof' => 'Bukti Mahasiswa',
        ];
        $rows = $this->studentProofApprovalRows($from, $to);

        if (filled($filters['status']) && array_key_exists($filters['status'], $statusOptions)) {
            $rows = $rows->filter(fn (array $row): bool => ($row['type'] ?? '') === $filters['status'])->values();
        }

        if (filled($filters['q'])) {
            $needle = str($filters['q'])->lower()->toString();
            $rows = $rows->filter(fn (array $row): bool => str_contains((string) ($row['search'] ?? ''), $needle))->values();
        }

        $paginator = $this->paginateActivityRows($rows, $displayFilters);

        return array_replace($this->module('Inbox Persetujuan Admin', 'Tinjau pengajuan member yang membutuhkan persetujuan admin sebelum dipakai di alur operasional.', 'Tidak ada persetujuan yang perlu ditinjau.', ['Persetujuan', 'Member', 'Status', 'Waktu']), [
            'view' => 'admin.partials.notifications-page',
            'rows' => $paginator->getCollection(),
            'paginator' => $paginator,
            'filters' => $displayFilters,
            'statusOptions' => $statusOptions,
            'dateFilters' => true,
            'searchPlaceholder' => 'Cari member, kode, email, WhatsApp, atau bukti mahasiswa...',
        ]);
    }

    private function membersModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Daftar Anggota', 'Akun member, kode keanggotaan, status akses, dan verifikasi bukti mahasiswa.', 'Belum ada data member.', ['Nama', 'Kode Member', 'WhatsApp', 'Status Member', 'Kategori', 'Verifikasi', 'Bergabung']), [
            'statusOptions' => $options,
            'pillColumns' => ['Status Member', 'Kategori', 'Verifikasi'],
            'searchPlaceholder' => 'Cari nama, kode, WhatsApp, email, atau status...',
        ]);
        if (! $loadRows) {
            return $module;
        }

        $query = Member::query()->with('user')->latest('created_at');
        $this->applyMemberSearch($query, $filters['q']);
        $this->applyExactStatusFilter($query, $filters['status'], array_keys($options));

        return $this->withPaginatedRows($module, $query, fn (Member $member): array => $this->memberRow($member), $filters, $options);
    }

    private function memberRow(Member $member): array
    {
        $actions = [];

        if ($member->is_student && filled($member->student_proof_path)) {
            $actions[] = [
                'label' => 'Review Bukti',
                'url' => route('admin.members.student-proof.review', $member),
                'method' => 'GET',
                'variant' => 'primary',
                'aria_label' => 'Review bukti mahasiswa '.$member->member_code,
            ];
        }

        return [
            'cells' => [
                $member->user?->name ?? '-',
                $member->member_code,
                $member->user?->phone ?: '-',
                $this->statusLabel($member->status),
                $member->is_student ? 'Mahasiswa' : 'Umum',
                $this->studentVerificationLabel($member),
                $member->joined_at?->translatedFormat('d M Y') ?? '-',
            ],
            'actions' => array_merge($actions, [
                ['label' => 'Edit', 'url' => route('admin.resources.edit', ['resource' => 'members', 'id' => $member->getKey()]), 'method' => 'GET', 'variant' => 'secondary'],
                ['label' => $this->toggleLabel($member, 'status'), 'url' => route('admin.resources.toggle', ['resource' => 'members', 'id' => $member->getKey()]), 'method' => 'PATCH', 'variant' => 'secondary'],
            ]),
        ];
    }

    private function packagesModule(array $filters, bool $loadRows): array
    {
        $options = ['active' => 'Aktif', 'inactive' => 'Nonaktif'];
        $module = array_replace($this->module('Katalog Paket', 'Paket membership dan paket sesi dari database layanan.', 'Belum ada paket.', ['Paket', 'Jenis', 'Durasi/Sesi', 'Harga', 'Status']), ['statusOptions' => $options]);
        if (! $loadRows) {
            return $module;
        }

        $query = Package::query()->orderByDesc('is_active')->orderBy('package_kind')->orderBy('price');
        $this->applySimpleSearch($query, $filters['q'], ['name', 'package_kind', 'type', 'category', 'description']);
        $this->applyBooleanStatusFilter($query, 'is_active', $filters['status']);

        return $this->withPaginatedRows($module, $query, fn (Package $package): array => $this->actionRow('packages', $package, [
            $package->name,
            $this->headline($package->package_kind),
            $package->durationMarketingLabel() ?? ($package->session_count ? $package->session_count.' sesi' : '-'),
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
            ClassStaffPresenter::timeLabel($schedule),
            ClassStaffPresenter::roleLabel($schedule).' '.ClassStaffPresenter::displayName($schedule->trainer, $schedule),
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
            $this->statusLabel($payment->status),
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
        $module = array_replace($this->module('Testimoni Member', 'Cerita member yang tampil di website publik.', 'Belum ada testimoni.', ['Nama', 'Label', 'Rating', 'Status']), ['statusOptions' => $options]);
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
        $module = array_replace($this->module('Log Aktivitas Terbaru', 'Jejak perubahan penting yang tercatat di sistem.', 'Belum ada log aktivitas.', ['Aktivitas', 'Jenis Perubahan', 'Subjek', 'Waktu']), [
            'searchPlaceholder' => 'Cari aktivitas, subjek, atau admin...',
        ]);
        if (! $loadRows || ! Schema::hasTable('activity_log')) {
            return $module;
        }

        $query = DB::table('activity_log')->select(['id', 'description', 'event', 'subject_type', 'causer_id', 'created_at'])->latest('created_at');
        $this->applyActivityLogFilters($query, $filters);

        return $this->withPaginatedRows($module, $query, fn (object $log): array => ['cells' => [
            (string) ($log->description ?? '-'),
            $this->activityEventLabel($log->event ?? null),
            class_basename((string) ($log->subject_type ?? '-')),
            filled($log->created_at) ? (string) $log->created_at : '-',
        ], 'actions' => []], $filters);
    }

    private function settingsModule(array $filters, bool $loadRows): array
    {
        $module = $this->module('Pengaturan Website', 'Nilai sensitif otomatis disamarkan agar aman dilihat dari admin.', 'Belum ada pengaturan.', ['Kunci', 'Grup', 'Tipe', 'Nilai']);
        if (! $loadRows) {
            return $module;
        }

        $query = Setting::query()->orderBy('group')->orderBy('key');
        $this->applySettingsSearch($query, $filters['q']);

        return $this->withPaginatedRows($module, $query, fn (Setting $setting): array => ['cells' => [
            $setting->key,
            $setting->group,
            $setting->type,
            $this->settingDisplayValue($setting->key, $setting->value),
        ], 'actions' => []], $filters);
    }

    private function profileModule(User $user): array
    {
        return array_replace($this->module('Profil Admin', 'Data aman akun admin saat ini.', 'Profil admin belum tersedia.', ['Informasi', 'Nilai', 'Catatan']), [
            'rows' => collect([
                ['Nama', $user->name, 'Akun yang sedang login.'],
                ['Email', $user->email, 'Digunakan untuk masuk ke akun admin.'],
                ['Telepon', $user->phone ?? '-', 'Opsional.'],
                ['Foto profil', filled($user->avatar) ? 'Tersedia' : 'Belum diunggah', 'Ditampilkan hanya di portal admin.'],
                ['Peran', $user->getRoleNames()->implode(', ') ?: '-', 'Akses admin dibatasi sesuai peran pengguna.'],
                ['Login terakhir', $user->last_login_at?->translatedFormat('d M Y H:i') ?? '-', 'Diisi saat login berhasil.'],
            ]),
        ]);
    }

    /** @return array<int, array<string, string>> */
    private function moduleSummaries(string $today): array
    {
        return [
            ['label' => 'Member', 'value' => (string) Member::query()->count(), 'route' => 'admin.members', 'description' => 'Total member terdaftar.'],
            ['label' => 'Paket', 'value' => (string) Package::query()->where('is_active', true)->count(), 'route' => 'admin.packages', 'description' => 'Paket yang sedang aktif.'],
            ['label' => 'Kelas', 'value' => (string) GymClass::query()->where('is_active', true)->count(), 'route' => 'admin.classes', 'description' => 'Kelas yang tersedia.'],
            ['label' => 'Pembayaran', 'value' => (string) Payment::query()->whereDate('created_at', $today)->count(), 'route' => 'admin.payments', 'description' => 'Transaksi yang dibuat hari ini.'],
            ['label' => 'Produk', 'value' => (string) Product::query()->where('is_active', true)->count(), 'route' => 'admin.products', 'description' => 'Produk aktif di katalog.'],
            ['label' => 'Promo', 'value' => (string) Promo::query()->where('is_published', true)->count(), 'route' => 'admin.promos', 'description' => 'Promo yang sedang tayang.'],
        ];
    }

    /** @return array<string, mixed> */
    private function operationalTrend(): array
    {
        $end = now()->startOfDay();
        $start = $end->copy()->subDays(13);
        $dates = collect(range(0, 13))->map(fn (int $offset): Carbon => $start->copy()->addDays($offset));
        $dateKeys = $dates->map(fn (Carbon $date): string => $date->toDateString());

        $checkIns = $this->dailyCounts(
            GymCheckIn::query()
                ->selectRaw('DATE(check_in_date) as date_key, COUNT(*) as aggregate')
                ->whereBetween('check_in_date', [$start->toDateString(), $end->toDateString()])
                ->groupBy('date_key')
        );

        $bookings = $this->dailyCounts(
            ClassEnrollment::query()
                ->selectRaw('DATE(session_date) as date_key, COUNT(*) as aggregate')
                ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])
                ->whereNotIn('status', ['cancelled', 'canceled'])
                ->groupBy('date_key')
        );

        $payments = $this->dailyCounts(
            Payment::query()
                ->selectRaw('DATE(COALESCE(paid_at, created_at)) as date_key, COUNT(*) as aggregate')
                ->where('status', 'paid')
                ->where(function (EloquentBuilder $query) use ($start, $end): void {
                    $query->whereBetween('paid_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                        ->orWhere(function (EloquentBuilder $query) use ($start, $end): void {
                            $query->whereNull('paid_at')
                                ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
                        });
                })
                ->groupBy('date_key')
        );

        $series = collect([
            ['key' => 'checkIns', 'name' => 'Check-in', 'tone' => 'gold', 'color' => '#FEAC18', 'counts' => $checkIns],
            ['key' => 'bookings', 'name' => 'Booking', 'tone' => 'sky', 'color' => '#38BDF8', 'counts' => $bookings],
            ['key' => 'payments', 'name' => 'Pembayaran', 'tone' => 'emerald', 'color' => '#10B981', 'counts' => $payments],
        ])->map(function (array $series) use ($dateKeys): array {
            $values = $dateKeys
                ->map(fn (string $date): int => (int) ($series['counts'][$date] ?? 0))
                ->values()
                ->all();

            return [
                'key' => $series['key'],
                'name' => $series['name'],
                'tone' => $series['tone'],
                'color' => $series['color'],
                'values' => $values,
                'total' => array_sum($values),
            ];
        })->values();

        $maxValue = max(1, (int) $series->flatMap(fn (array $series): array => $series['values'])->max());

        return [
            'title' => 'Tren aktivitas',
            'description' => 'Lihat perkembangan check-in, booking, dan pembayaran yang sudah dikonfirmasi selama 14 hari terakhir.',
            'period' => '14 hari terakhir',
            'labels' => $dates->map(fn (Carbon $date): string => $date->translatedFormat('d M'))->all(),
            'series' => $series->all(),
            'maxValue' => $maxValue,
            'isEmpty' => $series->flatMap(fn (array $series): array => $series['values'])->sum() === 0,
        ];
    }

    private function dailyCounts(EloquentBuilder $query): Collection
    {
        return $query
            ->get()
            ->mapWithKeys(fn (Model $row): array => [(string) $row->getAttribute('date_key') => (int) $row->getAttribute('aggregate')]);
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

    private function paginateAdminRows(Collection $rows, array $filters): LengthAwarePaginator
    {
        $page = filled($filters['page'] ?? null)
            ? max(1, (int) $filters['page'])
            : max(1, LengthAwarePaginator::resolveCurrentPage());

        $pageRows = $rows
            ->forPage($page, self::ADMIN_PER_PAGE)
            ->map(fn (array $row): array => ['cells' => $row['cells'], 'actions' => []])
            ->values();

        return new LengthAwarePaginator($pageRows, $rows->count(), self::ADMIN_PER_PAGE, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
    }

    private function paginateActivityRows(Collection $rows, array $filters): LengthAwarePaginator
    {
        $page = filled($filters['page'] ?? null)
            ? max(1, (int) $filters['page'])
            : max(1, LengthAwarePaginator::resolveCurrentPage());

        return new LengthAwarePaginator(
            $rows->forPage($page, self::ADMIN_PER_PAGE)->values(),
            $rows->count(),
            self::ADMIN_PER_PAGE,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    private function memberActivityNotificationRows(Carbon $from, Carbon $to): Collection
    {
        return collect()
            ->merge($this->paymentActivityRows($from, $to))
            ->merge($this->bookingActivityRows($from, $to))
            ->merge($this->membershipActivityRows($from, $to))
            ->merge($this->packageSessionActivityRows($from, $to))
            ->merge($this->checkInNotificationRows($from, $to))
            ->sortByDesc('sort_at')
            ->values();
    }

    private function pendingStudentProofApprovalCount(): int
    {
        return Member::query()
            ->where('is_student', true)
            ->where('student_verification_status', 'pending_review')
            ->whereNotNull('student_proof_path')
            ->count();
    }

    private function studentProofApprovalRows(Carbon $from, Carbon $to): Collection
    {
        return Member::query()
            ->with('user')
            ->where('is_student', true)
            ->where('student_verification_status', 'pending_review')
            ->whereNotNull('student_proof_path')
            ->whereBetween('student_proof_uploaded_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('student_proof_uploaded_at')
            ->get()
            ->map(function (Member $member): array {
                $uploadedAt = $member->student_proof_uploaded_at ?? $member->updated_at ?? now();
                $memberName = $member->user?->name ?? 'Member Platinum Gym';
                $memberCode = $member->member_code ?? '-';
                $phone = $member->user?->phone ?: '-';
                $email = $member->user?->email ?: '-';

                return [
                    'type' => 'student_proof',
                    'title' => 'Review bukti mahasiswa',
                    'member' => $memberName,
                    'member_code' => $memberCode,
                    'status' => $this->studentVerificationLabel($member),
                    'kind' => 'warning',
                    'note' => 'Upload bukti mahasiswa menunggu persetujuan admin. WhatsApp: '.$phone,
                    'time' => $uploadedAt->translatedFormat('d M Y H:i'),
                    'url' => route('admin.members.student-proof.review', $member),
                    'sort_at' => $uploadedAt->timestamp,
                    'search' => $this->searchableRowText([
                        'student_proof',
                        'Review bukti mahasiswa',
                        $memberName,
                        $memberCode,
                        $phone,
                        $email,
                        $this->studentVerificationLabel($member),
                    ]),
                ];
            })
            ->values();
    }

    private function paymentActivityRows(Carbon $from, Carbon $to): Collection
    {
        return Payment::query()
            ->with(['member.user', 'payable' => function (MorphTo $morphTo): void {
                $morphTo->morphWith([
                    Membership::class => ['package'],
                    MemberPackageSession::class => ['package'],
                    ClassEnrollment::class => ['schedule.gymClass'],
                ]);
            }])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('created_at')
            ->limit(60)
            ->get()
            ->map(fn (Payment $payment): array => $this->activityRow(
                'payment',
                'Pembayaran '.$this->statusLabel($payment->status),
                $payment->member,
                $this->statusLabel($payment->status),
                $this->statusKind($payment->status),
                $payment->payment_code.' - '.$this->money($payment->amount).' untuk '.$this->payableActivityLabel($payment->payable),
                $payment->created_at,
                route('admin.payments', ['q' => $payment->payment_code]),
                [$payment->payment_code, $payment->method]
            ));
    }

    private function bookingActivityRows(Carbon $from, Carbon $to): Collection
    {
        return ClassEnrollment::query()
            ->with(['member.user', 'schedule.gymClass', 'schedule.trainer'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('created_at')
            ->limit(60)
            ->get()
            ->map(fn (ClassEnrollment $enrollment): array => $this->activityRow(
                'booking',
                'Booking kelas '.$this->statusLabel($enrollment->status),
                $enrollment->member,
                $this->statusLabel($enrollment->status),
                $this->statusKind($enrollment->status),
                ($enrollment->schedule?->gymClass?->name ?? 'Kelas Platinum Gym').' pada '.($enrollment->session_date?->translatedFormat('d M Y') ?? '-').' pukul '.substr((string) $enrollment->schedule?->start_time, 0, 5),
                $enrollment->created_at,
                route('admin.booking', ['q' => $enrollment->member?->member_code]),
                [$enrollment->schedule?->gymClass?->name, $enrollment->status]
            ));
    }

    private function membershipActivityRows(Carbon $from, Carbon $to): Collection
    {
        return Membership::query()
            ->with(['member.user', 'package'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Membership $membership): array => $this->activityRow(
                'membership',
                'Membership '.$this->statusLabel($membership->status),
                $membership->member,
                $this->statusLabel($membership->status),
                $this->statusKind($membership->status),
                ($membership->package?->name ?? $membership->code).' - '.$this->money($membership->price),
                $membership->created_at,
                route('admin.members', ['q' => $membership->member?->member_code]),
                [$membership->code, $membership->package?->name]
            ));
    }

    private function packageSessionActivityRows(Carbon $from, Carbon $to): Collection
    {
        return MemberPackageSession::query()
            ->with(['member.user', 'package', 'trainer'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (MemberPackageSession $session): array => $this->activityRow(
                'package_session',
                'Paket sesi '.$this->statusLabel($session->status),
                $session->member,
                $this->statusLabel($session->status),
                $this->statusKind($session->status),
                collect([
                    $session->package?->name ?? $session->code,
                    $session->trainer?->name ? 'Coach: '.$session->trainer->name : null,
                    $session->remaining_sessions.' sesi tersisa',
                ])->filter()->implode(' - '),
                $session->created_at,
                route('admin.members', ['q' => $session->member?->member_code]),
                [$session->code, $session->package?->name, $session->trainer?->name]
            ));
    }

    private function checkInNotificationRows(Carbon $from, Carbon $to): Collection
    {
        return GymCheckIn::query()
            ->with(['member.user'])
            ->whereBetween('check_in_date', [$from->toDateString(), $to->toDateString()])
            ->latest('check_in_at')
            ->limit(50)
            ->get()
            ->map(fn (GymCheckIn $checkIn): array => $this->activityRow(
                'check_in',
                'Check-in member',
                $checkIn->member,
                'Tercatat',
                'success',
                'Masuk pada '.$checkIn->check_in_date?->translatedFormat('d M Y').' pukul '.($checkIn->check_in_at?->format('H:i') ?? '-'),
                $checkIn->check_in_at,
                route('admin.check-in', ['q' => $checkIn->member?->member_code]),
                [$checkIn->method]
            ));
    }

    private function activityRow(string $type, string $title, ?Member $member, string $status, string $kind, string $note, mixed $date, string $url, array $extraSearch = []): array
    {
        $memberName = $member?->user?->name ?? $member?->member_code ?? '-';
        $memberCode = $member?->member_code ?? '-';
        $time = $date ? Carbon::parse($date) : now();

        return [
            'type' => $type,
            'title' => $title,
            'member' => $memberName,
            'member_code' => $memberCode,
            'status' => $status,
            'kind' => $kind,
            'note' => $note,
            'time' => $time->translatedFormat('d M Y H:i'),
            'url' => $url,
            'sort_at' => $time->timestamp,
            'search' => $this->searchableRowText([$title, $memberName, $memberCode, $status, $note, $type], $extraSearch),
        ];
    }

    private function payableActivityLabel(mixed $payable): string
    {
        return match (true) {
            $payable instanceof Membership => $payable->package?->name ?? $payable->code ?? 'Membership',
            $payable instanceof MemberPackageSession => $payable->package?->name ?? $payable->code ?? 'Paket sesi',
            $payable instanceof ClassEnrollment => $payable->schedule?->gymClass?->name ?? 'Booking kelas',
            default => 'layanan Platinum Gym',
        };
    }

    private function statusKind(?string $status): string
    {
        return match ((string) $status) {
            'active', 'paid', 'booked', 'confirmed', 'attended', 'verified' => 'success',
            'pending', 'waiting_payment', 'waiting_confirmation', 'unpaid', 'pending_payment', 'pending_review' => 'warning',
            'rejected', 'failed', 'expired', 'cancelled', 'canceled' => 'danger',
            default => 'neutral',
        };
    }

    private function checkInActivityRows(Carbon $from, Carbon $to): Collection
    {
        $checkIns = GymCheckIn::query()
            ->with(['member.user', 'membership.package', 'packageSessionUsages.packageSession.package'])
            ->whereDate('check_in_date', '>=', $from->toDateString())
            ->whereDate('check_in_date', '<=', $to->toDateString())
            ->get()
            ->map(fn (GymCheckIn $checkIn): array => $this->checkInActivityRow($checkIn));

        $standaloneSessionUsages = MemberPackageSessionUsage::query()
            ->with(['member.user', 'packageSession.package'])
            ->whereDate('usage_date', '>=', $from->toDateString())
            ->whereDate('usage_date', '<=', $to->toDateString())
            ->whereNull('gym_check_in_id')
            ->get()
            ->map(fn (MemberPackageSessionUsage $usage): array => $this->standaloneSessionUsageActivityRow($usage));

        return $checkIns
            ->merge($standaloneSessionUsages)
            ->sortByDesc('sort_at')
            ->values();
    }

    private function checkInActivityRow(GymCheckIn $checkIn): array
    {
        $sessionDetails = $this->sessionUsageDetails($checkIn->packageSessionUsages);
        $activity = $sessionDetails->isNotEmpty() ? 'Check-in + Sesi' : 'Check-in';
        $membershipPackage = $checkIn->membership?->package?->name ?? 'Membership aktif';
        $cells = [
            $checkIn->member?->user?->name ?? $checkIn->member?->member_code ?? '-',
            $checkIn->check_in_date?->translatedFormat('d M Y') ?? '-',
            $checkIn->check_in_at?->format('H:i') ?? '-',
            collect([$membershipPackage])->merge($sessionDetails->pluck('name'))->filter()->implode(' + ') ?: '-',
            $this->remainingSessionsLabel($sessionDetails),
            $activity,
        ];

        return [
            'cells' => $cells,
            'sort_at' => ($checkIn->check_in_at ?? $checkIn->check_in_date?->startOfDay() ?? now()->startOfDay())->timestamp,
            'search' => $this->searchableRowText($cells, [$checkIn->member?->member_code]),
        ];
    }

    private function standaloneSessionUsageActivityRow(MemberPackageSessionUsage $usage): array
    {
        $sessionDetails = $this->sessionUsageDetails(collect([$usage]));
        $sessionDetail = $sessionDetails->first();
        $cells = [
            $usage->member?->user?->name ?? $usage->member?->member_code ?? '-',
            $usage->usage_date?->translatedFormat('d M Y') ?? '-',
            $usage->used_at?->format('H:i') ?? '-',
            $sessionDetail['name'] ?? 'Paket sesi',
            $this->remainingSessionsLabel($sessionDetails),
            'Sesi',
        ];

        return [
            'cells' => $cells,
            'sort_at' => ($usage->used_at ?? $usage->usage_date?->startOfDay() ?? now()->startOfDay())->timestamp,
            'search' => $this->searchableRowText($cells, [$usage->member?->member_code]),
        ];
    }

    private function searchableRowText(array $cells, array $extra = []): string
    {
        return collect($cells)->merge($extra)->map(fn (mixed $cell): string => str((string) $cell)->lower()->toString())->implode(' ');
    }

    private function sessionUsageDetails(Collection $usages): Collection
    {
        return $usages
            ->map(function (MemberPackageSessionUsage $usage): array {
                $packageSession = $usage->packageSession;

                return [
                    'name' => $packageSession?->package?->name ?? $packageSession?->code ?? 'Paket sesi',
                    'remaining_sessions' => filled($packageSession?->remaining_sessions) ? (int) $packageSession->remaining_sessions : null,
                ];
            })
            ->filter(fn (array $detail): bool => filled($detail['name'] ?? null))
            ->values();
    }

    private function remainingSessionsLabel(Collection $sessionDetails): string
    {
        if ($sessionDetails->isEmpty()) {
            return '-';
        }

        if ($sessionDetails->count() === 1) {
            return $this->remainingSessionText($sessionDetails->first()['remaining_sessions'] ?? null);
        }

        return $sessionDetails
            ->map(fn (array $detail): string => ($detail['name'] ?? 'Paket sesi').': '.$this->remainingSessionText($detail['remaining_sessions'] ?? null))
            ->implode(', ');
    }

    private function remainingSessionText(?int $remainingSessions): string
    {
        return $remainingSessions === null ? '-' : $remainingSessions.' sesi';
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
        if (in_array($enrollment->status, ['booked', 'active'], true)) {
            $actions[] = ['label' => 'Konfirmasi', 'url' => route('admin.booking.confirm', $enrollment), 'method' => 'POST', 'variant' => 'primary'];
        }

        if (
            ! in_array($enrollment->status, ['attended'], true)
            && ! (bool) $enrollment->getAttribute('attendance_exists')
            && ! ($enrollment->session_date?->isPast() && ! $enrollment->session_date?->isToday())
            && BookingTimePolicy::canCancel($enrollment)
        ) {
            $actions[] = ['label' => 'Batalkan Booking', 'url' => route('admin.booking.cancel', $enrollment), 'method' => 'POST', 'variant' => 'secondary'];
        }

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

    private function applySettingsSearch(EloquentBuilder $query, string $search): void
    {
        if (blank($search)) {
            return;
        }

        $like = $this->like($search);
        $query->where(function (EloquentBuilder $query) use ($like): void {
            $query->where('key', 'like', $like)
                ->orWhere('group', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere(function (EloquentBuilder $query) use ($like): void {
                    foreach (['secret', 'token', 'password', 'credential', 'client_secret', 'qr_secret', 'prompt', 'oauth', 'api_key'] as $needle) {
                        $query->where('key', 'not like', '%'.$needle.'%');
                    }

                    $query->where('key', 'not like', '%\_key')
                        ->where('key', 'not like', '%key\_%')
                        ->where('value', 'like', $like);
                });
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

    private function statusLabel(?string $status): string
    {
        return match ((string) $status) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'pending', 'waiting_payment', 'unpaid', 'pending_payment' => 'Menunggu Pembayaran',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'paid' => 'Lunas',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled', 'canceled' => 'Dibatalkan',
            'booked' => 'Terdaftar',
            'confirmed' => 'Terkonfirmasi',
            'attended' => 'Hadir',
            'pending_review' => 'Menunggu Review',
            'verified' => 'Terverifikasi',
            default => $this->headline($status),
        };
    }

    private function studentVerificationLabel(Member $member): string
    {
        if (! $member->is_student) {
            return '-';
        }

        return match ((string) $member->student_verification_status) {
            'pending_review' => 'Menunggu review',
            'verified' => 'Terverifikasi',
            'failed' => 'Ditolak',
            'unverified', '' => 'Belum diverifikasi',
            default => $this->statusLabel($member->student_verification_status),
        };
    }

    private function activityEventLabel(?string $event): string
    {
        return match ((string) $event) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            default => $this->headline($event),
        };
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
        return collect($statuses)->mapWithKeys(fn (string $status): array => [$status => $this->statusLabel($status)])->all();
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
