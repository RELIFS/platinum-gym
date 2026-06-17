@php
    $user = $portal['user'];
    $member = $portal['member'];
    $activeMembership = $portal['activeMembership'];
    $activePackageSessions = $portal['activePackageSessions'];
    $payments = $portal['payments'];
    $recentEnrollments = $portal['recentEnrollments'];
    $qrToken = $portal['qrToken'];
    $qrStatusLabel = $portal['qrStatusLabel'] ?? 'Belum diterbitkan';
    $packages = $portal['packages'];
    $classSchedules = $portal['classSchedules'];
    $notifications = $portal['notifications'];
    $dayLabels = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $statusLabel = match ((string) $member->status) {
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'suspended' => 'Ditangguhkan',
        default => str((string) $member->status)->headline()->toString(),
    };
    $pagePartials = [
        'profil' => 'member.pages.profile',
        'membership' => 'member.pages.membership',
        'booking-kelas' => 'member.pages.booking',
        'riwayat-booking' => 'member.pages.booking-history',
        'transaksi' => 'member.pages.transactions',
        'qr' => 'member.pages.qr',
        'notifikasi' => 'member.pages.notifications',
    ];
@endphp

<x-member-layout :portal="$portal" :title="$page['title']">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">

                <h2 class="text-3xl font-black leading-tight tracking-tight text-white sm:text-4xl">{{ $page['title'] }}</h2>
                <p class="mt-4 text-sm font-medium leading-7 text-zinc-300">{{ $page['description'] }}</p>
            </div>
        </div>
    </section>

    @include($pagePartials[$page['key']] ?? 'member.pages.profile')
</x-member-layout>
