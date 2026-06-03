@php
    $siteName = $settings['site_name'] ?? 'Platinum Gym Padang';
    $hours = $settings['operational_hours'] ?? ['weekday' => '06:00-22:00', 'weekend' => '06:00-20:00'];
    $phoneNumber = preg_replace('/\D+/', '', (string) ($settings['whatsapp_number'] ?? '6282174777761'));
    $whatsappUrl = $settings['whatsapp_url'] ?? 'https://wa.me/6282174777761';
    $whatsappFooterUrl = $whatsappUrl.(str_contains($whatsappUrl, '?') ? '&' : '?').http_build_query([
        'text' => 'Halo Platinum Gym Padang, saya ingin tanya paket latihan.',
    ]);
@endphp

<footer class="border-t border-zinc-200 bg-white dark:border-white/10 dark:bg-zinc-950">
    <div class="public-container py-12 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[1.15fr_0.85fr_0.85fr]">
            <div>
                <a href="{{ route('public.home') }}" class="inline-flex min-h-11 touch-manipulation items-center rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-950" aria-label="{{ $siteName }}">
                    <img src="{{ asset('images/brand/platinum-gym-wordmark-480.webp') }}" alt="{{ $siteName }}" class="brand-logo h-11 w-auto" loading="lazy" width="480" height="112">
                </a>
                <p class="mt-5 max-w-lg break-words text-sm leading-7 text-zinc-600 dark:text-zinc-400">
                    Pusat kebugaran premium di Padang untuk gym, senam, personal trainer, Muaythai, Poundfit, dan kebutuhan pendukung latihan.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                    <a href="{{ $whatsappFooterUrl }}" target="_blank" rel="noopener noreferrer" class="public-button-secondary">WhatsApp</a>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-zinc-950 dark:text-white">Navigasi</h2>
                <div class="mt-5 grid grid-cols-2 gap-x-3 gap-y-1 text-sm font-semibold text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('public.about') }}" class="public-text-link">Tentang</a>
                    <a href="{{ route('public.services') }}" class="public-text-link">Layanan</a>
                    <a href="{{ route('public.classes') }}" class="public-text-link">Kelas</a>
                    <a href="{{ route('public.products') }}" class="public-text-link">Produk</a>
                    <a href="{{ route('public.gallery') }}" class="public-text-link">Galeri</a>
                    <a href="{{ route('public.bmi') }}" class="public-text-link">BMI</a>
                    <a href="{{ route('legal.terms') }}" class="public-text-link">Syarat</a>
                    <a href="{{ route('legal.privacy') }}" class="public-text-link">Privasi</a>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-black uppercase tracking-[0.2em] text-zinc-950 dark:text-white">Kontak</h2>
                <div class="mt-5 space-y-3 break-words text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                    <p>{{ $settings['address'] ?? 'Padang, Sumatera Barat' }}</p>
                    <p>Senin-Jumat: {{ $hours['weekday'] ?? '06:00-22:00' }}</p>
                    <p>Sabtu-Minggu: {{ $hours['weekend'] ?? '06:00-20:00' }}</p>
                    <p><a href="tel:+{{ $phoneNumber }}" class="public-text-link">{{ $settings['phone_display'] ?? '+62 821-7477-7761' }}</a></p>
                    <p><a href="mailto:{{ $settings['public_email'] ?? 'info@platinumgympadang.com' }}" class="public-text-link">{{ $settings['public_email'] ?? 'info@platinumgympadang.com' }}</a></p>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 break-words border-t border-zinc-200 pt-6 text-xs font-medium text-zinc-500 dark:border-white/10 dark:text-zinc-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $siteName }}. Semua hak cipta dilindungi.</p>
            <p>Fitness center premium di Padang.</p>
        </div>
    </div>
</footer>
