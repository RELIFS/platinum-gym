<x-guest-layout>
    <div class="w-full">
        <div class="mb-8">
            <h2 class="mb-3 text-3xl font-extrabold leading-tight text-zinc-950 dark:text-white">
                Kebijakan <span class="text-gold-500">Privasi</span>
            </h2>
            <p class="auth-panel-copy">
                Ringkasan penggunaan data akun dan member Platinum Gym Padang.
            </p>
        </div>

        <div class="space-y-5 text-sm leading-7 text-zinc-600 dark:text-zinc-300">
            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Data yang Disimpan</h3>
                <p>Sistem menyimpan data akun, profil member, nomor WhatsApp, status membership, booking, transaksi, dan data pendukung lain yang dibutuhkan untuk layanan.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Penggunaan Data</h3>
                <p>Data dipakai untuk autentikasi, pengelolaan membership, booking, pembayaran, notifikasi layanan, dukungan pelanggan, dan keamanan akun.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Login Google</h3>
                <p>Jika memakai Google, sistem menggunakan email, nama, provider ID, dan avatar untuk membuat atau menghubungkan akun lokal. Token OAuth tidak ditampilkan di UI.</p>
            </section>

            <section>
                <h3 class="mb-2 text-base font-bold text-zinc-950 dark:text-white">Perlindungan Data</h3>
                <p>Akses data dibatasi sesuai role pengguna. Member hanya boleh melihat data miliknya sendiri, sedangkan admin dan owner mengikuti hak akses yang ditentukan sistem.</p>
            </section>
        </div>

        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ route('register') }}')" class="auth-button-secondary mt-8">
            Kembali
        </button>
    </div>
</x-guest-layout>
