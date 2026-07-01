@php
    $actions = $document['actions'] ?? [];
    $backUrl = $actions['show'] ?? '#';
@endphp

@if (($layout ?? null) === 'owner')
    <x-owner-layout :portal="$portal" :navigation="$navigation" :title="$title">
        <section class="owner-page-header print:hidden">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="owner-eyebrow">Struk POS</p>
                    <h2 class="owner-title mt-2">Struk Transaksi</h2>
                    <p class="mt-3 owner-copy">Struk compact untuk bukti transaksi dan kebutuhan cetak.</p>
                </div>
                <a href="{{ $backUrl }}" class="owner-button-secondary justify-center">Kembali ke Invoice</a>
            </div>
        </section>

        <section class="mt-6 print:mt-0" aria-label="Preview struk transaksi">
            <div class="receipt-actions mb-4 grid gap-2 sm:grid-cols-3 print:hidden">
                <a href="{{ $backUrl }}" class="owner-button-secondary justify-center">Kembali ke Invoice</a>
                @if ($actions['receipt_download'] ?? null)
                    <a href="{{ $actions['receipt_download'] }}" class="owner-button-primary justify-center">Unduh PDF</a>
                @endif
                <button type="button" onclick="window.print()" class="owner-button-secondary justify-center">Cetak</button>
            </div>

            @include('invoices.partials.receipt-paper', ['document' => $document])
        </section>
    </x-owner-layout>
@elseif (($layout ?? null) === 'member')
    <x-member-layout :portal="$portal" :title="$title">
        <section class="member-section-header print:hidden">
            <div class="min-w-0">
                <p class="member-eyebrow">Struk POS</p>
                <h2 class="member-title mt-2">Struk Transaksi</h2>
                <p class="member-copy mt-3">Struk compact untuk bukti transaksi dan kebutuhan cetak.</p>
            </div>
            <a href="{{ $backUrl }}" class="member-button-secondary justify-center">Kembali ke Invoice</a>
        </section>

        <section class="mt-6 print:mt-0" aria-label="Preview struk transaksi">
            <div class="receipt-actions mb-4 grid gap-2 sm:grid-cols-3 print:hidden">
                <a href="{{ $backUrl }}" class="member-button-secondary justify-center">Kembali ke Invoice</a>
                @if ($actions['receipt_download'] ?? null)
                    <a href="{{ $actions['receipt_download'] }}" class="member-button-primary justify-center">Unduh PDF</a>
                @endif
                <button type="button" onclick="window.print()" class="member-button-secondary justify-center">Cetak</button>
            </div>

            <div class="mx-auto max-w-sm">
                @include('invoices.partials.receipt-paper', ['document' => $document])
            </div>
        </section>
    </x-member-layout>
@elseif (($layout ?? null) === 'admin')
    <x-admin-layout :portal="$portal" :navigation="$navigation" :title="$title">
        <section class="admin-page-header print:hidden">
            <div class="min-w-0">
                <p class="admin-eyebrow">Struk POS</p>
                <h2 class="admin-title mt-2">Struk Transaksi</h2>
                <p class="admin-copy mt-3">Struk compact untuk bukti transaksi dan kebutuhan cetak.</p>
            </div>
            <a href="{{ $backUrl }}" class="admin-button-secondary justify-center">Kembali ke Invoice</a>
        </section>

        <section class="mt-6 print:mt-0" aria-label="Preview struk transaksi">
            <div class="receipt-actions mb-4 grid gap-2 sm:grid-cols-3 print:hidden">
                <a href="{{ $backUrl }}" class="admin-button-secondary justify-center">Kembali ke Invoice</a>
                @if ($actions['receipt_download'] ?? null)
                    <a href="{{ $actions['receipt_download'] }}" class="admin-button-primary justify-center">Unduh PDF</a>
                @endif
                <button type="button" onclick="window.print()" class="admin-button-secondary justify-center">Cetak</button>
            </div>

            <div class="mx-auto max-w-sm">
                @include('invoices.partials.receipt-paper', ['document' => $document])
            </div>
        </section>
    </x-admin-layout>
@else
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background: #fff !important; }
            .receipt-actions { display: none !important; }
            .receipt-paper { box-shadow: none !important; margin: 0 auto !important; }
        }
    </style>
</head>
<body class="min-h-dvh bg-zinc-100 px-3 py-5 text-zinc-950 dark:bg-zinc-950">
    <main class="mx-auto max-w-sm">
        <div class="receipt-actions mb-4 grid gap-2 sm:grid-cols-3">
            <a href="{{ $backUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-800 shadow-sm transition hover:border-gold-500/50 hover:bg-gold-500/10 hover:text-gold-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.06] dark:text-white">Kembali ke Invoice</a>
            @if ($actions['receipt_download'] ?? null)
                <a href="{{ $actions['receipt_download'] }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-gold-500 px-3 text-sm font-black text-zinc-950 shadow-sm transition hover:bg-gold-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40">Unduh PDF</a>
            @endif
            <button type="button" onclick="window.print()" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 text-sm font-black text-zinc-800 shadow-sm transition hover:border-gold-500/50 hover:bg-gold-500/10 hover:text-gold-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 dark:border-white/10 dark:bg-white/[0.06] dark:text-white">Cetak</button>
        </div>

        @include('invoices.partials.receipt-paper', ['document' => $document])
    </main>
</body>
</html>
@endif
