<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Transaksi</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Riwayat pembayaran</h3>
        </div>
        <a href="{{ route('member.membership') }}" class="member-button-secondary">Pilih Paket</a>
    </div>

    @include('member.partials.filter-toolbar', [
        'filters' => $portal['pageFilters'] ?? [],
        'searchLabel' => 'Cari transaksi',
        'searchPlaceholder' => 'Cari kode, metode, status, invoice...',
        'selects' => [
            [
                'name' => 'status',
                'label' => 'Filter status transaksi',
                'placeholder' => 'Semua status',
                'options' => $portal['filterOptions']['paymentStatuses'] ?? [],
            ],
        ],
    ])

    @if ($payments->count() > 0)
        <div class="mt-5 space-y-3 md:hidden">
            @foreach ($payments as $payment)
                @php
                    $paymentMeta = $payment->member_status_meta ?? ['label' => str((string) $payment->status)->headline()->toString(), 'class' => 'member-status-neutral', 'can_pay' => false];
                    $payable = $payment->payable;
                    $serviceName = $payable?->package?->name ?? $payable?->schedule?->gymClass?->name ?? 'Layanan Platinum Gym';
                    $serviceKindLabel = match (true) {
                        $payable instanceof \App\Models\Membership => 'Membership',
                        $payable instanceof \App\Models\MemberPackageSession => 'Paket Sesi',
                        $payable instanceof \App\Models\ClassEnrollment => 'Booking Kelas',
                        default => 'Layanan',
                    };
                @endphp
                <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-mono text-sm font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</p>
                            <p class="mt-1 text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $payment->created_at?->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        <span class="member-status-pill {{ $paymentMeta['class'] }}">{{ $paymentMeta['label'] }}</span>
                    </div>
                    <div class="mt-3">
                        <p class="text-[0.7rem] font-black uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ $serviceKindLabel }}</p>
                        <p class="mt-1 break-words text-sm font-black text-zinc-950 dark:text-white">{{ $serviceName }}</p>
                    </div>
                    <p class="mt-4 text-xl font-black text-gold-600 dark:text-gold-400">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                    <div class="mt-4 flex flex-col gap-2">
                        <a href="{{ route('member.transactions.show', $payment) }}" class="member-button-secondary w-full">Detail</a>
                        @if ($paymentMeta['can_pay'])
                            <form method="POST" action="{{ route('member.transactions.pay', $payment) }}">
                                @csrf
                                <button type="submit" class="member-button-primary w-full">Bayar Sekarang</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-5 hidden overflow-x-auto rounded-lg border border-zinc-200 dark:border-white/10 md:block">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm dark:divide-white/10">
                <caption class="sr-only">Riwayat pembayaran member</caption>
                <thead class="bg-zinc-50 text-xs uppercase tracking-[0.14em] text-zinc-500 dark:bg-white/[0.04] dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-5 py-4 font-black">Kode</th>
                        <th scope="col" class="px-5 py-4 font-black">Layanan</th>
                        <th scope="col" class="px-5 py-4 font-black">Tanggal</th>
                        <th scope="col" class="px-5 py-4 font-black">Metode</th>
                        <th scope="col" class="px-5 py-4 font-black">Jumlah</th>
                        <th scope="col" class="px-5 py-4 font-black">Status</th>
                        <th scope="col" class="px-5 py-4 font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                    @foreach ($payments as $payment)
                        @php
                            $paymentMeta = $payment->member_status_meta ?? ['label' => str((string) $payment->status)->headline()->toString(), 'class' => 'member-status-neutral', 'can_pay' => false];
                            $payable = $payment->payable;
                            $serviceName = $payable?->package?->name ?? $payable?->schedule?->gymClass?->name ?? 'Layanan Platinum Gym';
                            $serviceKindLabel = match (true) {
                                $payable instanceof \App\Models\Membership => 'Membership',
                                $payable instanceof \App\Models\MemberPackageSession => 'Paket Sesi',
                                $payable instanceof \App\Models\ClassEnrollment => 'Booking Kelas',
                                default => 'Layanan',
                            };
                        @endphp
                        <tr class="bg-white dark:bg-zinc-950/35">
                            <td class="px-5 py-4 font-mono font-black text-zinc-950 dark:text-white">{{ $payment->payment_code }}</td>
                            <td class="px-5 py-4">
                                <p class="text-[0.7rem] font-black uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ $serviceKindLabel }}</p>
                                <p class="mt-1 break-words font-bold text-zinc-700 dark:text-zinc-200">{{ $serviceName }}</p>
                            </td>
                            <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ $payment->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 font-semibold text-zinc-600 dark:text-zinc-300">{{ str((string) $payment->method)->headline() }}</td>
                            <td class="px-5 py-4 font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4"><span class="member-status-pill {{ $paymentMeta['class'] }}">{{ $paymentMeta['label'] }}</span></td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('member.transactions.show', $payment) }}" class="member-button-secondary">Detail</a>
                                    @if ($paymentMeta['can_pay'])
                                        <form method="POST" action="{{ route('member.transactions.pay', $payment) }}">
                                            @csrf
                                            <button type="submit" class="member-button-primary">Bayar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'receipt',
            'title' => 'Belum ada transaksi',
            'body' => 'Riwayat pembayaran akan muncul setelah checkout membership, paket sesi, atau kelas.',
            'class' => 'mt-5',
        ])
    @endif

    @include('member.partials.pagination', ['paginator' => $payments, 'label' => 'transaksi'])
</section>
