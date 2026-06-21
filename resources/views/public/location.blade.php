<x-public-layout :settings="$settings" title="Lokasi Platinum Gym Padang" description="Alamat, jam operasional, WhatsApp, Instagram, dan Google Maps Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Lokasi & Kontak',
        'title' => 'Datang langsung ke Platinum Gym Padang.',
        'description' => 'Temukan alamat, jam operasional, kontak resmi, Instagram, dan rute Google Maps Platinum Gym Padang.',
    ])

    @php
        $hours = $settings['operational_hours'] ?? ['weekday' => '06:00-22:00', 'weekend' => '06:00-20:00'];
        $phoneNumber = preg_replace('/\D+/', '', (string) ($settings['whatsapp_number'] ?? '6282174777761'));
        $whatsappUrl = $settings['whatsapp_url'] ?? 'https://wa.me/6282174777761';
        $mapsEmbedUrl = $settings['maps_embed_url'] ?? null;
        $locationWhatsappUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
            'text' => 'Halo Platinum Gym Padang, saya ingin tanya lokasi dan jam operasional.',
        ]);
    @endphp

    <section class="public-section public-section-muted">
        <div class="public-container grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
            <div class="space-y-5">
                <article class="public-card">
                    <p class="public-eyebrow">Alamat</p>
                    <h2 class="mt-3 break-words text-2xl font-black text-zinc-950 dark:text-white">Platinum Gym Padang</h2>
                    <p class="mt-4 break-words text-sm leading-7 text-zinc-600 dark:text-zinc-400">{{ $settings['address'] }}</p>
                </article>

                <article class="public-card">
                    <p class="public-eyebrow">Jam Operasional</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="font-semibold text-zinc-500 dark:text-zinc-500">Senin-Jumat</dt>
                            <dd class="font-black text-zinc-950 dark:text-white">{{ $hours['weekday'] ?? '06:00-22:00' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="font-semibold text-zinc-500 dark:text-zinc-500">Sabtu-Minggu</dt>
                            <dd class="font-black text-zinc-950 dark:text-white">{{ $hours['weekend'] ?? '06:00-20:00' }}</dd>
                        </div>
                    </dl>
                </article>

                <article class="public-card">
                    <p class="public-eyebrow">Kontak</p>
                    <div class="mt-4 space-y-3 break-words text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                        <p>WhatsApp: <a href="tel:+{{ $phoneNumber }}" class="public-text-link text-zinc-900 dark:text-zinc-100">{{ $settings['phone_display'] }}</a></p>
                        <p>Email: <a href="mailto:{{ $settings['public_email'] }}" class="public-text-link text-zinc-900 dark:text-zinc-100">{{ $settings['public_email'] }}</a></p>
                        <p>Instagram: <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="public-text-link text-zinc-900 dark:text-zinc-100">{{ $settings['instagram_handle'] }}</a></p>
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <a href="{{ $locationWhatsappUrl }}" target="_blank" rel="noopener noreferrer" class="public-button-primary">WhatsApp</a>
                        <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="public-button-secondary">Instagram</a>
                    </div>
                </article>
            </div>

            <div class="public-card flex min-h-[32rem] flex-col justify-between overflow-hidden p-0">
                @if (filled($mapsEmbedUrl))
                    <div class="relative min-h-[26rem] flex-1 overflow-hidden bg-zinc-100 sm:min-h-[30rem] dark:bg-zinc-950">
                        <iframe
                            src="{{ $mapsEmbedUrl }}"
                            title="Peta lokasi Platinum Gym Padang di Google Maps"
                            class="absolute inset-0 h-full w-full border-0"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            data-public-map-embed
                        ></iframe>
                    </div>
                @else
                    <div class="relative flex min-h-96 flex-1 items-end overflow-hidden bg-zinc-100 p-8 dark:bg-zinc-950">
                        <img src="{{ asset('images/public/gallery/platinum-gym-padang-gym-exterior.webp') }}" alt="Tampak depan Platinum Gym Padang" class="absolute inset-0 h-full w-full object-cover opacity-55 dark:opacity-45" loading="lazy" width="600" height="336">
                        <div class="absolute inset-0 bg-gradient-to-br from-white/85 via-white/70 to-gold-500/25 dark:from-zinc-950 dark:via-zinc-950/85 dark:to-gold-600/50"></div>
                        <div class="absolute inset-0 opacity-[0.10] dark:opacity-[0.10]" aria-hidden="true" style="background-image: linear-gradient(rgba(24,24,27,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(24,24,27,.18) 1px, transparent 1px); background-size: 48px 48px;"></div>
                        <div class="absolute right-6 top-6 rounded-2xl border border-zinc-200 bg-white/85 px-4 py-3 text-right shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/10">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-gold-700 dark:text-gold-400">Padang Timur</p>
                            <p class="mt-1 text-sm font-bold text-zinc-950 dark:text-white">Sawahan</p>
                        </div>
                        <div class="relative max-w-xl">
                            <p class="public-eyebrow">Google Maps</p>
                            <h2 class="public-heading-balance mt-3 break-words text-4xl font-black text-zinc-950 dark:text-white">Jl. H. Agus Salim No.3A</h2>
                            <p class="mt-4 break-words text-sm leading-7 text-zinc-700 dark:text-zinc-300">Buka Google Maps untuk rute langsung ke lokasi resmi Platinum Gym Padang.</p>
                        </div>
                    </div>
                @endif
                <div class="grid gap-3 border-t border-zinc-200 bg-white/80 p-6 dark:border-white/10 dark:bg-zinc-950/60 sm:grid-cols-2">
                    <p class="break-words text-sm leading-6 text-zinc-600 dark:text-zinc-300 sm:col-span-2">Gunakan tombol di bawah peta untuk membuka rute langsung di Google Maps.</p>
                    <a href="{{ $settings['maps_url'] }}" target="_blank" rel="noopener noreferrer" class="public-button-primary">Buka Google Maps</a>
                    <a href="{{ $settings['maps_search_url'] }}" target="_blank" rel="noopener noreferrer" class="public-button-secondary">Cari di Maps</a>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
