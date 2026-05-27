<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Syarat <span class="text-gold-500">Ketentuan</span>
            </h2>
            <p class="auth-panel-copy">
                Ringkasan ketentuan penggunaan layanan digital Platinum Gym Padang.
            </p>
        </div>

        <div class="space-y-5 text-sm leading-7 text-zinc-600 dark:text-zinc-300">
            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Akun Member</h3>
                <p>Data pendaftaran harus benar dan dapat diverifikasi. Akun dipakai untuk mengakses dashboard, membership, booking, transaksi, dan layanan lain yang tersedia.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Kewajiban Pengguna</h3>
                <p>Pengguna wajib menjaga keamanan akun, memakai nomor WhatsApp aktif, dan mengikuti aturan operasional Platinum Gym Padang saat memakai fasilitas atau kelas.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Pembayaran dan Layanan</h3>
                <p>Membership, paket sesi, booking kelas, dan transaksi lain mengikuti harga, periode aktif, kuota, serta validasi yang berlaku di sistem.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Perubahan Ketentuan</h3>
                <p>Platinum Gym Padang dapat memperbarui ketentuan layanan sesuai kebutuhan operasional, keamanan, atau kepatuhan.</p>
            </section>
        </div>

        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('register') }}')" class="auth-button-secondary mt-8">
            Kembali
        </button>
    </div>
</x-guest-layout>
