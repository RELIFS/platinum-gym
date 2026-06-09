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
    <section id="{{ $promoSectionId }}" class="relative isolate overflow-hidden bg-zinc-950 text-white" aria-labelledby="{{ $promoSectionId }}-title">
        <div class="public-surface-grid absolute inset-0 opacity-20" aria-hidden="true"></div>
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/60 to-transparent" aria-hidden="true"></div>
        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold-500/35 to-transparent" aria-hidden="true"></div>

        <div class="public-container relative py-8 sm:py-10 lg:py-12">
            <div class="grid gap-6 lg:grid-cols-[0.78fr_1.22fr] lg:items-center lg:gap-10">
                <div class="max-w-2xl">
                    <p class="public-eyebrow">Promo Aktif</p>
                    <h2 id="{{ $promoSectionId }}-title" class="public-heading-balance mt-3 text-2xl font-black leading-tight sm:text-3xl lg:text-4xl">
                        {{ $promoTitle }}
                    </h2>
                    <p class="mt-4 break-words text-sm leading-7 text-zinc-300 sm:text-base">
                        {{ $promoDescription }}
                    </p>
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $primaryUrl }}" class="public-button-primary">{{ $primaryLabel }}</a>
                        @if ($secondaryUrl && $secondaryLabel)
                            <a href="{{ $secondaryUrl }}" class="public-button-secondary border-white/10 bg-white/[0.045] text-white hover:border-gold-400/60 hover:bg-white/[0.075] hover:text-gold-400">{{ $secondaryLabel }}</a>
                        @endif
                    </div>
                </div>

                <div class="grid items-stretch gap-4 md:grid-cols-2">
                    @foreach ($promos as $promo)
                        <article class="group relative flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-5 shadow-[0_22px_70px_rgba(0,0,0,0.28)] motion-safe:transition motion-safe:duration-300 motion-safe:hover:-translate-y-1 hover:border-gold-500/55 hover:bg-white/[0.085] motion-reduce:transition-none sm:p-6">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-gold-500 px-3 py-1 text-[0.65rem] font-black uppercase tracking-[0.16em] text-zinc-950">Promo {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                @if ($promo->ends_at)
                                    <span class="rounded-full border border-white/10 bg-zinc-950/55 px-3 py-1 text-[0.65rem] font-bold uppercase tracking-[0.14em] text-zinc-300">Berlaku s.d. {{ $promo->ends_at->translatedFormat('d M Y') }}</span>
                                @else
                                    <span class="rounded-full border border-white/10 bg-zinc-950/55 px-3 py-1 text-[0.65rem] font-bold uppercase tracking-[0.14em] text-zinc-300">Periode aktif</span>
                                @endif
                            </div>

                            <h3 class="mt-5 break-words text-xl font-black leading-tight text-white sm:text-2xl">{{ $promo->title }}</h3>
                            <p class="mt-3 break-words text-sm leading-7 text-zinc-300">{{ $promo->description }}</p>

                            <dl class="mt-auto grid gap-3 border-t border-white/10 pt-5 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[0.65rem] font-black uppercase tracking-[0.16em] text-zinc-500">Potongan</dt>
                                    <dd class="mt-1 break-words text-sm font-black text-gold-400">
                                        @if ($promo->discount_type === 'fixed' && $promo->discount_value)
                                            Hemat @include('public.partials.price', ['amount' => $promo->discount_value])
                                        @elseif ($promo->discount_type === 'percent' && $promo->discount_value)
                                            Hemat {{ number_format((float) $promo->discount_value, 0, ',', '.') }}%
                                        @else
                                            Promo aktif
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-[0.65rem] font-black uppercase tracking-[0.16em] text-zinc-500">Status</dt>
                                    <dd class="mt-1 break-words text-sm font-black text-white">Tersedia di website</dd>
                                </div>
                            </dl>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
