<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-600 dark:text-gold-400">Owner Area</p>
            <h1 class="mt-1 text-2xl font-black text-zinc-950 dark:text-white">Dashboard Owner</h1>
        </div>
    </x-slot>

    <x-dashboard.page
        title="Ringkasan Bisnis"
        description="Area owner disiapkan untuk pantauan performa membership, transaksi, kelas, dan laporan."
    >
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-dashboard.stat-card label="Pendapatan Bulan Ini" value="-" description="Menunggu modul payment." />
            <x-dashboard.stat-card label="Member Aktif" value="-" description="Menunggu data membership." />
            <x-dashboard.stat-card label="Transaksi" value="0" description="Belum ada transaksi." />
            <x-dashboard.stat-card label="Kelas Terisi" value="-" description="Menunggu booking kelas." />
        </div>

        <div class="mt-6">
            <x-dashboard.card title="Laporan" description="Laporan read-only akan menjadi pusat monitoring owner.">
                <x-dashboard.empty-state
                    title="Belum ada laporan tersedia"
                    description="Laporan keuangan, performa member, dan okupansi kelas akan tampil setelah modul report aktif."
                />
            </x-dashboard.card>
        </div>
    </x-dashboard.page>
</x-app-layout>

