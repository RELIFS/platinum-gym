<x-public-layout title="Syarat Ketentuan Platinum Gym Padang" description="Ketentuan penggunaan layanan digital, akun member, pembayaran, dan layanan Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Legal',
        'title' => 'Syarat Ketentuan',
        'description' => 'Ketentuan penggunaan layanan digital Platinum Gym Padang.',
    ])

    <section class="public-section">
        <div class="public-container">
            <div class="public-card mx-auto max-w-3xl space-y-6 text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                <section>
                    <h2 class="mb-2 text-base font-black text-zinc-950 dark:text-white">Akun Member</h2>
                    <p>Data pendaftaran harus benar dan dapat diverifikasi. Akun dipakai untuk mengakses dashboard, membership, booking, transaksi, dan layanan lain yang tersedia.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-black text-zinc-950 dark:text-white">Kewajiban Pengguna</h2>
                    <p>Pengguna wajib menjaga keamanan akun, memakai nomor WhatsApp aktif, dan mengikuti aturan operasional Platinum Gym Padang saat memakai fasilitas atau kelas.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-black text-zinc-950 dark:text-white">Pembayaran dan Layanan</h2>
                    <p>Membership, paket sesi, booking kelas, dan transaksi lain mengikuti harga, periode aktif, kuota, serta validasi yang berlaku di sistem.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-black text-zinc-950 dark:text-white">Perubahan Ketentuan</h2>
                    <p>Platinum Gym Padang dapat memperbarui ketentuan layanan sesuai kebutuhan operasional, keamanan, atau kepatuhan.</p>
                </section>

                <div class="flex flex-col gap-3 border-t border-zinc-200 pt-6 dark:border-white/10 sm:flex-row">
                    <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                    <a href="{{ route('public.home') }}" class="public-button-secondary">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
