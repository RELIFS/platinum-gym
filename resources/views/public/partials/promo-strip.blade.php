@php
    $promoSectionId = $promoSectionId ?? 'promo-aktif';
    $promoTitle = $promoTitle ?? 'Penawaran aktif untuk mulai latihan.';
    $promoDescription = $promoDescription ?? 'Promo ditampilkan dari data aktif Platinum Gym Padang. Cek detail paket, jadwal, dan ketentuan sebelum mendaftar.';
    $primaryUrl = $primaryUrl ?? route('public.services');
    $primaryLabel = $primaryLabel ?? 'Lihat Layanan';
    $secondaryUrl = $secondaryUrl ?? null;
    $secondaryLabel = $secondaryLabel ?? null;
@endphp

@if ($promos->isNotEmpty())
    <section id="{{ $promoSectionId }}" class="relative isolate overflow-hidden bg-white text-zinc-950 dark:bg-zinc-950 dark:text-zinc-100" aria-labelledby="{{ $promoSectionId }}-title">
        <div class="public-surface-grid absolute inset-0 opacity-20" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/60 to-transparent" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-500/35 to-transparent" aria-hidden="true"></div>

        <div class="public-container relative py-8 sm:py-10 lg:py-12">
            <div class="grid gap-6 lg:grid-cols-[0.78fr_1.22fr] lg:items-center lg:gap-10">
                <div class="public-motion-reveal max-w-2xl" data-motion="reveal">
                    <p class="public-eyebrow">Promo Aktif</p>
                    <h2 id="{{ $promoSectionId }}-title" class="public-heading-balance mt-3 text-2xl type-title leading-tight sm:text-3xl lg:text-4xl">
                        {{ $promoTitle }}
                    </h2>
                    <p class="mt-4 break-words text-sm leading-7 text-zinc-600 dark:text-zinc-300 sm:text-base">
                        {{ $promoDescription }}
                    </p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $primaryUrl }}" class="public-button-primary public-motion-cta" data-motion="cta">{{ $primaryLabel }}</a>
                        @if ($secondaryUrl && $secondaryLabel)
                            <a href="{{ $secondaryUrl }}" class="public-button-secondary">{{ $secondaryLabel }}</a>
                        @endif
                    </div>
                </div>

                <div class="grid items-stretch gap-4 md:grid-cols-2">
                    @foreach ($promos as $promo)
                        <article class="group public-motion-card public-motion-reveal relative flex h-full min-w-0 flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white/95 p-5 shadow-[0_10px_28px_rgba(24,24,27,0.06)] motion-safe:transition motion-safe:duration-300 motion-safe:hover:-translate-y-0.5 hover:border-gold-600/50 hover:bg-white motion-reduce:transition-none dark:border-white/10 dark:bg-white/[0.06] dark:shadow-[0_14px_38px_rgba(0,0,0,0.24)] dark:hover:bg-white/[0.085] sm:p-6" data-motion="reveal card" data-motion-delay="{{ ($loop->index % 2) * 100 }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-gold-500 px-3 py-1 text-[0.65rem] type-control uppercase tracking-[0.12em] text-zinc-950">Promo {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @if ($promo->ends_at)
                                    <span class="rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 text-[0.65rem] type-control uppercase tracking-[0.1em] text-zinc-600 dark:border-white/10 dark:bg-zinc-950/55 dark:text-zinc-300">Berlaku s.d. {{ $promo->ends_at->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="rounded-full border border-zinc-200 bg-zinc-100 px-3 py-1 text-[0.65rem] type-control uppercase tracking-[0.1em] text-zinc-600 dark:border-white/10 dark:bg-zinc-950/55 dark:text-zinc-300">Periode aktif</span>
                                @endif
                            </div>

                            <h3 class="mt-5 break-words text-xl type-title leading-tight text-zinc-950 dark:text-zinc-100 sm:text-2xl">{{ $promo->title }}</h3>
                            @if ($promo->package)
                                <p class="mt-2 inline-flex w-fit max-w-full rounded-full border border-gold-600/30 bg-gold-500/10 px-3 py-1 text-xs type-control text-zinc-700 dark:border-gold-400/25 dark:text-gold-400">
                                    Untuk: {{ $promo->package->name }}
                                </p>
                            @endif
                            <p class="mt-3 break-words text-sm leading-7 text-zinc-600 dark:text-zinc-300">{{ $promo->description }}</p>

                            <dl class="mt-auto grid gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[0.65rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-400">Potongan</dt>
                                    <dd class="mt-1 break-words text-sm type-control text-zinc-700 dark:text-gold-400">
                                        @if ($promo->discount_type === 'fixed' && $promo->discount_value)
                                            Hemat @include('public.partials.price', ['amount' => $promo->discount_value])
                                        @elseif (in_array($promo->discount_type, ['percent', 'percentage'], true) && $promo->discount_value)
                                            Hemat {{ number_format((float) $promo->discount_value, 0, ',', '.') }}%
                                        @else
                                            Promo aktif
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-[0.65rem] type-control uppercase tracking-[0.11em] text-zinc-600 dark:text-zinc-400">Status</dt>
                                    <dd class="mt-1 break-words text-sm type-control text-zinc-950 dark:text-zinc-100">Tersedia di website</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
