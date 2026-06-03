<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Admin Area</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">Dashboard Admin</h1>
        </div>
    </x-slot>

    <x-dashboard.page
        title="Operasional Platinum Gym"
        description="Ringkasan awal untuk pengelolaan member, layanan, kelas, produk, dan transaksi."
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat-card label="Member Aktif" value="-" description="Menunggu data membership." />
            <x-dashboard.stat-card label="Booking Hari Ini" value="0" description="Belum ada booking." />
            <x-dashboard.stat-card label="Pembayaran Pending" value="0" description="Tidak ada antrean." />
            <x-dashboard.stat-card label="Produk Aktif" value="-" description="Mengikuti katalog produk." />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <x-dashboard.card title="Prioritas Operasional" description="Area ini akan menjadi titik masuk modul admin berikutnya.">
                <x-dashboard.empty-state
                    title="Belum ada tugas operasional"
                    description="Tugas validasi member, booking, pembayaran, dan stok akan tampil setelah modul admin diaktifkan."
                />
            </x-dashboard.card>

            <x-dashboard.card title="Data Public Website" description="Konten public sudah berjalan dari database dan query layer.">
                <div class="grid gap-3 text-sm font-semibold text-zinc-600 dark:text-zinc-300">
                    <a href="{{ route('public.home') }}" class="rounded-lg border border-zinc-200 px-4 py-3 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:hover:text-gold-400">Preview Beranda</a>
                    <a href="{{ route('public.services') }}" class="rounded-lg border border-zinc-200 px-4 py-3 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:hover:text-gold-400">Preview Layanan</a>
                    <a href="{{ route('public.gallery') }}" class="rounded-lg border border-zinc-200 px-4 py-3 transition hover:border-gold-500/60 hover:text-gold-600 dark:border-white/10 dark:hover:text-gold-400">Preview Galeri</a>
                </div>
            </x-dashboard.card>
        </div>
    </x-dashboard.page>
</x-app-layout>
