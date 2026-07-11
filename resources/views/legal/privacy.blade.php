<x-public-layout title="Kebijakan Privasi Platinum Gym Padang" description="Kebijakan penggunaan data akun, member, Gymmi, Google OAuth, dan perlindungan data Platinum Gym Padang.">
    @include('public.partials.page-hero', [
        'eyebrow' => 'Legal',
        'title' => 'Kebijakan Privasi',
        'description' => 'Ringkasan penggunaan data akun dan member Platinum Gym Padang.',
    ])

    <section class="public-section">
        <div class="public-container">
            <div class="public-card mx-auto max-w-3xl space-y-6 text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Data yang Disimpan</h2>
                    <p>Sistem menyimpan data akun, profil member, nomor WhatsApp, status membership, booking, transaksi, dan data pendukung lain yang dibutuhkan untuk layanan.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Penggunaan Data</h2>
                    <p>Data dipakai untuk autentikasi, pengelolaan membership, booking, pembayaran, notifikasi layanan, dukungan pelanggan, dan keamanan akun.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Login Google</h2>
                    <p>Jika memakai Google, sistem menggunakan email, nama, provider ID, dan avatar untuk membuat atau menghubungkan akun lokal. Token OAuth tidak ditampilkan di UI.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Perlindungan Data</h2>
                    <p>Akses data dibatasi sesuai role pengguna. Member hanya boleh melihat data miliknya sendiri, sedangkan admin dan owner mengikuti hak akses yang ditentukan sistem.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Percakapan Gymmi</h2>
                    <p>Gymmi memakai pesan yang Anda kirim untuk memberikan informasi resmi gym dan, pada portal member, data minimum dari akun Anda yang relevan dengan pertanyaan. Konteks percakapan disimpan sementara selama sesi pada tab yang sama. Catatan operasional dapat disimpan hingga 30 hari untuk keamanan dan peningkatan kualitas layanan.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Pemrosesan AI</h2>
                    <p>Untuk pertanyaan tertentu, pesan yang telah dibatasi dapat diproses oleh penyedia AI. Informasi berisiko tinggi seperti status pembayaran, QR, dan data privat member dijawab dari sistem Platinum Gym tanpa menyerahkan data tersebut kepada penyedia AI. Jangan masukkan kata sandi, token, kode pembayaran, data kartu, dokumen identitas, atau rahasia lain ke percakapan.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base type-control text-zinc-950 dark:text-zinc-100">Pertanyaan dan Penghapusan Data</h2>
                    <p>Untuk pertanyaan mengenai data atau permintaan penghapusan catatan percakapan, hubungi Platinum Gym melalui kontak resmi pada halaman Lokasi &amp; Kontak. Permintaan akan diverifikasi sebelum diproses.</p>
                </section>

                <div class="flex flex-col gap-3 border-t border-zinc-200 pt-6 dark:border-white/10 sm:flex-row">
                    <a href="{{ route('register') }}" class="public-button-primary">Daftar Member</a>
                    <a href="{{ route('public.home') }}" class="public-button-secondary">Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
</x-public-layout>
