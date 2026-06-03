<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Member Area</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">Dashboard Member</h1>
        </div>
    </x-slot>

    <x-dashboard.page
        title="Selamat datang, {{ auth()->user()->name }}"
        description="Ringkasan akun member Platinum Gym Padang. Data membership, booking, dan transaksi akan tampil di area ini saat modul operasional aktif."
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat-card label="Status Akun" value="Aktif" description="Akun siap digunakan." />
            <x-dashboard.stat-card label="Membership" value="-" description="Belum ada paket aktif." />
            <x-dashboard.stat-card label="Booking" value="0" description="Belum ada booking berjalan." />
            <x-dashboard.stat-card label="Invoice" value="0" description="Tidak ada invoice tertunda." />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <x-dashboard.card title="Aktivitas Member" description="Data aktivitas akan mengikuti transaksi dan check-in member.">
                <x-dashboard.empty-state
                    title="Belum ada aktivitas terbaru"
                    description="Riwayat check-in, booking kelas, dan pembayaran member akan tampil setelah modul terkait aktif."
                    :action-href="route('public.services')"
                    action-label="Lihat Layanan"
                />
            </x-dashboard.card>

            <x-dashboard.card title="Akses Cepat">
                <div class="grid gap-3">
                    <a href="{{ route('profile.edit') }}" class="rounded-lg border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-800 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:text-zinc-200 dark:hover:text-gold-400">Kelola Profil</a>
                    <a href="{{ route('public.classes') }}" class="rounded-lg border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-800 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:text-zinc-200 dark:hover:text-gold-400">Jadwal Kelas</a>
                    <a href="{{ route('public.products') }}" class="rounded-lg border border-zinc-200 px-4 py-3 text-sm font-bold text-zinc-800 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:text-zinc-200 dark:hover:text-gold-400">Produk Gym</a>
                </div>
            </x-dashboard.card>
        </div>
    </x-dashboard.page>
</x-app-layout>
