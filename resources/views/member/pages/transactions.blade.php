<section class="member-card mt-6">
    <p class="member-eyebrow">Transaksi</p>
    <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Riwayat pembayaran</h3>

    <div class="mt-5 space-y-3 md:hidden">
        @forelse ($payments as $payment)
            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                        <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    <span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span>
                </div>
                <p class="mt-4 text-xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
            </article>
        @empty
            <div class="member-soft-panel">Belum ada transaksi.</div>
        @endforelse
    </div>

    <div class="mt-5 hidden overflow-x-auto rounded-lg border border-zinc-200 dark:border-white/10 md:block">
        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
            <thead class="bg-zinc-50 text-xs uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.04] dark:text-zinc-400">
                <tr>
                    <th class="px-5 py-4 font-black">Kode</th>
                    <th class="px-5 py-4 font-black">Tanggal</th>
                    <th class="px-5 py-4 font-black">Metode</th>
                    <th class="px-5 py-4 font-black">Jumlah</th>
                    <th class="px-5 py-4 font-black">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                @forelse ($payments as $payment)
                    <tr class="bg-white dark:bg-zinc-950/35">
                        <td class="px-5 py-4 font-mono font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</td>
                        <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ $payment->created_at?->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ str((string) $payment->method)->headline() }}</td>
                        <td class="px-5 py-4 font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-4"><span class="member-status-pill bg-zinc-100 text-zinc-700 dark:bg-white/[0.07] dark:text-zinc-300">{{ str((string) $payment->status)->headline() }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center font-semibold text-zinc-500">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
