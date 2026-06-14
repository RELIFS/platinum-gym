@php
    $initial = str($user->name)->substr(0, 1)->upper();
    $genderLabel = match ((string) $member->gender) {
        'male' => 'Laki-laki',
        'female' => 'Perempuan',
        default => filled($member->gender) ? str((string) $member->gender)->headline()->toString() : '-',
    };
    $membershipLabel = $activeMembership?->package?->name ?? 'Belum aktif';
    $profileFields = [
        $user->name,
        $user->email,
        $user->phone,
        $member->gender,
        $member->birth_date,
        $member->address,
        $member->emergency_contact,
        $member->height_cm,
        $member->weight_kg,
    ];
    $filledFields = collect($profileFields)->filter(fn ($value) => filled($value))->count();
    $completionPercent = (int) round(($filledFields / count($profileFields)) * 100);
    $identityRows = [
        ['label' => 'Nama Lengkap', 'value' => $user->name],
        ['label' => 'Alamat Email', 'value' => $user->email],
        ['label' => 'No. WhatsApp', 'value' => $user->phone ?? '-'],
        ['label' => 'Kode Member', 'value' => $member->member_code, 'mono' => true],
    ];
    $profileRows = [
        ['label' => 'Gender', 'value' => $genderLabel],
        ['label' => 'Tanggal Lahir', 'value' => $member->birth_date?->translatedFormat('d M Y') ?? '-'],
        ['label' => 'Alamat', 'value' => $member->address ?? '-'],
        ['label' => 'Kontak Darurat', 'value' => $member->emergency_contact ?? '-'],
    ];
    $trainingRows = [
        ['label' => 'Tinggi Badan', 'value' => filled($member->height_cm) ? $member->height_cm.' cm' : '-'],
        ['label' => 'Berat Badan', 'value' => filled($member->weight_kg) ? number_format((float) $member->weight_kg, 1, ',', '.').' kg' : '-'],
        ['label' => 'Kategori Member', 'value' => $member->is_student ? 'Mahasiswa' : 'Umum'],
        ['label' => 'No. Identitas Mahasiswa', 'value' => $member->student_id_number ?? '-'],
    ];
@endphp

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <section class="member-card-strong relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold-500/70 to-transparent" aria-hidden="true"></div>
        <div class="public-surface-grid absolute inset-0 opacity-[0.05]" aria-hidden="true"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex min-w-0 flex-col gap-5 sm:flex-row sm:items-start">
                <div class="grid h-20 w-20 shrink-0 place-items-center rounded-lg bg-gold-500 text-3xl font-black text-zinc-950 shadow-[0_18px_44px_rgba(254,172,24,0.28)]" aria-hidden="true">
                    {{ $initial }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-gold-400">Profil Member</p>
                    <h3 class="mt-2 break-words text-2xl font-black leading-tight text-white sm:text-3xl">{{ $user->name }}</h3>
                    <p class="mt-3 break-words text-sm font-semibold text-zinc-300">{{ $user->email }}</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="member-status-pill member-status-success">{{ $statusLabel }}</span>
                        <span class="member-status-pill bg-white/10 text-zinc-200">{{ $member->member_code }}</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:w-[28rem]">
                <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                    <p class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-400">Membership</p>
                    <p class="mt-2 break-words text-sm font-black text-white">{{ $membershipLabel }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                    <p class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-400">Bergabung</p>
                    <p class="mt-2 text-sm font-black text-white">{{ $member->joined_at?->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="rounded-lg border border-white/10 bg-white/[0.06] p-4">
                    <p class="text-[0.72rem] font-black uppercase tracking-[0.14em] text-zinc-400">Kelengkapan</p>
                    <p class="mt-2 text-sm font-black text-white">{{ $completionPercent }}%</p>
                </div>
            </div>
        </div>
    </section>

    <aside class="member-card">
        <p class="member-eyebrow">Aksi Profil</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Perbarui data member</h3>
        <p class="mt-3 member-copy">Edit data kontak, biodata, dan informasi latihan langsung dari portal member.</p>
        <a href="#edit-profil-member" class="member-button-primary mt-5 w-full">Edit Profil Member</a>
        <a href="{{ route('profile.edit') }}" class="member-button-secondary mt-3 w-full">Keamanan Akun</a>

        <div class="member-soft-panel mt-5" aria-label="Kelengkapan profil member">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-black text-zinc-950 dark:text-white">Kelengkapan Profil</p>
                <span class="text-sm font-black text-gold-600 dark:text-gold-400">{{ $completionPercent }}%</span>
            </div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10" aria-hidden="true">
                <div class="h-full rounded-full bg-gold-500" style="width: {{ $completionPercent }}%"></div>
            </div>
            <p class="mt-3 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Lengkapi kontak dan data tubuh agar admin lebih mudah membantu proses membership, booking, dan check-in.</p>
        </div>
    </aside>
</div>

<section id="edit-profil-member" class="member-card mt-6 scroll-mt-24">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="member-eyebrow">Edit Profil</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Data yang digunakan admin untuk layanan member</h3>
            <p class="mt-3 member-copy max-w-3xl">Pastikan nomor WhatsApp aktif. Email yang diganti akan membutuhkan verifikasi ulang sebelum akses penuh dibuka kembali.</p>
        </div>
        <span class="member-status-pill bg-gold-500/15 text-gold-700 dark:text-gold-300">{{ $completionPercent }}% lengkap</span>
    </div>

    <form method="POST" action="{{ route('member.profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="grid gap-4 lg:grid-cols-3">
            <div>
                <x-input-label for="member_name" value="Nama Lengkap" />
                <x-text-input id="member_name" name="name" type="text" class="mt-2 block min-h-12 w-full" :value="old('name', $user->name)" required autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="member_email" value="Email" />
                <x-text-input id="member_email" name="email" type="email" class="mt-2 block min-h-12 w-full" :value="old('email', $user->email)" required autocomplete="email" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="member_phone" value="Nomor WhatsApp" />
                <x-text-input id="member_phone" name="phone" type="tel" class="mt-2 block min-h-12 w-full" :value="old('phone', $user->phone)" required inputmode="tel" autocomplete="tel" placeholder="081234567890" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div>
                <x-input-label for="member_gender" value="Gender" />
                <select id="member_gender" name="gender" class="public-input mt-2 min-h-12" required autocomplete="sex">
                    <option value="male" @selected(old('gender', $member->gender) === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender', $member->gender) === 'female')>Perempuan</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>

            <div>
                <x-input-label for="member_birth_date" value="Tanggal Lahir" />
                <x-text-input id="member_birth_date" name="birth_date" type="date" class="mt-2 block min-h-12 w-full" :value="old('birth_date', $member->birth_date?->format('Y-m-d'))" required autocomplete="bday" />
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>

            <div>
                <x-input-label for="member_emergency_contact" value="Kontak Darurat" />
                <x-text-input id="member_emergency_contact" name="emergency_contact" type="tel" class="mt-2 block min-h-12 w-full" :value="old('emergency_contact', $member->emergency_contact)" inputmode="tel" autocomplete="tel" placeholder="081234567890" />
                <x-input-error class="mt-2" :messages="$errors->get('emergency_contact')" />
            </div>
        </div>

        <div>
            <x-input-label for="member_address" value="Alamat" />
            <textarea id="member_address" name="address" rows="3" class="public-input mt-2 min-h-28 resize-y" autocomplete="street-address" placeholder="Alamat domisili atau alamat utama yang bisa dihubungi">{{ old('address', $member->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_11rem_11rem]">
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                <label for="member_is_student" class="flex items-start gap-3">
                    <input id="member_is_student" name="is_student" type="checkbox" value="1" class="mt-0.5 h-6 w-6 rounded border-zinc-300 text-gold-500 focus:ring-gold-500/40 dark:border-white/20 dark:bg-zinc-950" @checked(old('is_student', $member->is_student))>
                    <span>
                        <span class="block text-sm font-black text-zinc-950 dark:text-white">Member mahasiswa</span>
                        <span class="mt-1 block text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Isi nomor identitas mahasiswa jika status ini aktif.</span>
                    </span>
                </label>
                <div class="mt-4">
                    <x-input-label for="member_student_id" value="No. Identitas Mahasiswa" />
                    <x-text-input id="member_student_id" name="student_id_number" type="text" class="mt-2 block min-h-12 w-full" :value="old('student_id_number', $member->student_id_number)" autocomplete="off" />
                    <x-input-error class="mt-2" :messages="$errors->get('student_id_number')" />
                </div>
            </div>

            <div>
                <x-input-label for="member_height_cm" value="Tinggi Badan" />
                <div class="relative mt-2">
                    <x-text-input id="member_height_cm" name="height_cm" type="number" min="100" max="250" class="block min-h-12 w-full pr-12" :value="old('height_cm', $member->height_cm)" inputmode="numeric" />
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-bold text-zinc-500">cm</span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('height_cm')" />
            </div>

            <div>
                <x-input-label for="member_weight_kg" value="Berat Badan" />
                <div class="relative mt-2">
                    <x-text-input id="member_weight_kg" name="weight_kg" type="number" min="30" max="250" step="0.1" class="block min-h-12 w-full pr-12" :value="old('weight_kg', filled($member->weight_kg) ? number_format((float) $member->weight_kg, 1, '.', '') : null)" inputmode="decimal" />
                    <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm font-bold text-zinc-500">kg</span>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('weight_kg')" />
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Kode member dan status akun dikelola oleh admin Platinum Gym.</p>
            <x-primary-button class="w-full sm:w-auto">Simpan Profil</x-primary-button>
        </div>
    </form>
</section>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="member-card">
        <p class="member-eyebrow">Identitas</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Data utama</h3>
        <dl class="mt-5">
            @foreach ($identityRows as $row)
                <div class="member-data-row">
                    <dt class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</dt>
                    <dd class="max-w-[60%] break-words text-right text-sm font-black text-zinc-950 dark:text-white {{ ($row['mono'] ?? false) ? 'font-mono' : '' }}">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    <section class="member-card">
        <p class="member-eyebrow">Data Pribadi</p>
        <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Kontak dan biodata</h3>
        <dl class="mt-5">
            @foreach ($profileRows as $row)
                <div class="member-data-row">
                    <dt class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</dt>
                    <dd class="max-w-[60%] break-words text-right text-sm font-black text-zinc-950 dark:text-white">{{ $row['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>

<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Data Latihan</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Informasi pendukung layanan</h3>
        </div>
        <a href="{{ route('member.membership') }}" class="member-button-secondary">Lihat Membership</a>
    </div>

    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($trainingRows as $row)
            <article class="member-soft-panel">
                <p class="text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</p>
                <p class="mt-2 break-words text-lg font-black text-zinc-950 dark:text-white">{{ $row['value'] }}</p>
            </article>
        @endforeach
    </div>
</section>
