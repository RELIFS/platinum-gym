@php
    $profileFields = [$user->name, $user->email, $user->phone, $user->avatar, $member->gender, $member->birth_date, $member->address, $member->emergency_contact];
    if ($member->is_student) {
        $profileFields[] = $member->student_proof_path;
    }
    $filledFields = collect($profileFields)->filter(fn ($value) => filled($value))->count();
    $completionPercent = (int) round(($filledFields / count($profileFields)) * 100);
@endphp

<div class="mt-6 grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
    <section class="member-card min-w-0">
        <div class="member-section-header lg:items-end">
            <div class="min-w-0">
                <p class="member-eyebrow">Edit Profil</p>
                <h3 class="member-section-title">Data yang digunakan admin untuk layanan member</h3>
                <p class="mt-3 member-copy max-w-3xl">Pastikan nomor WhatsApp aktif. Email yang diganti akan membutuhkan verifikasi ulang sebelum akses penuh dibuka kembali.</p>
            </div>
            <span class="member-status-pill bg-gold-500/15 text-gold-text">{{ $completionPercent }}% lengkap</span>
        </div>

        <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6" x-data="{ submitting: false, avatarPreviewUrl: null, avatarPreviewActive: false, avatarObjectUrl: null, studentProofPreviewUrl: null, studentProofPreviewActive: false, studentProofObjectUrl: null, setAvatarPreview(event) { const file = event.target.files?.[0]; if (this.avatarObjectUrl) { URL.revokeObjectURL(this.avatarObjectUrl); this.avatarObjectUrl = null; } if (! file) { this.avatarPreviewUrl = null; this.avatarPreviewActive = false; return; } this.avatarObjectUrl = URL.createObjectURL(file); this.avatarPreviewUrl = this.avatarObjectUrl; this.avatarPreviewActive = true; }, setStudentProofPreview(event) { const file = event.target.files?.[0]; if (this.studentProofObjectUrl) { URL.revokeObjectURL(this.studentProofObjectUrl); this.studentProofObjectUrl = null; } if (! file) { this.studentProofPreviewUrl = null; this.studentProofPreviewActive = false; return; } this.studentProofObjectUrl = URL.createObjectURL(file); this.studentProofPreviewUrl = this.studentProofObjectUrl; this.studentProofPreviewActive = true; } }" x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
            @csrf
            @method('patch')

            <div class="member-soft-panel flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                <div class="grid h-20 w-20 shrink-0 place-items-center overflow-hidden rounded-lg border border-gold-500/30 bg-gold-500 text-3xl type-emphasis text-zinc-950" aria-hidden="true">
                    <template x-if="avatarPreviewUrl">
                        <img x-bind:src="avatarPreviewUrl" alt="" class="h-full w-full object-cover">
                    </template>
                    <template x-if="! avatarPreviewUrl">
                        <x-member-avatar :user="$user" class="h-full w-full rounded-lg border-0 text-3xl" />
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <x-input-label for="member_avatar" value="Foto Profil" />
                    <input id="member_avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="member-form-input mt-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-zinc-950" x-on:change="setAvatarPreview($event)">
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    <p class="mt-2 text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                    <p class="mt-2 text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400" x-show="avatarPreviewActive" x-cloak>Preview, belum disimpan</p>
                </div>
            </div>

            <div class="grid min-w-0 gap-4 lg:grid-cols-3">
                <div class="min-w-0">
                    <x-input-label for="member_name" value="Nama Lengkap" />
                    <x-text-input id="member_name" name="name" type="text" class="mt-2 block min-h-12 w-full" :value="old('name', $user->name)" required autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div class="min-w-0">
                    <x-input-label for="member_email" value="Email" />
                    <x-text-input id="member_email" name="email" type="email" class="mt-2 block min-h-12 w-full" :value="old('email', $user->email)" required autocomplete="email" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="min-w-0">
                    <x-input-label for="member_phone" value="Nomor WhatsApp" />
                    <x-text-input id="member_phone" name="phone" type="tel" class="mt-2 block min-h-12 w-full" :value="old('phone', $user->phone)" required inputmode="tel" autocomplete="tel" placeholder="081234567890" data-phone-feedback-input aria-describedby="member_phone-feedback" />
                    <p id="member_phone-feedback" class="mt-1.5 hidden text-xs type-compact text-red-600 dark:text-red-400" data-phone-feedback>Gunakan format nomor 08xxxxxxxxxx.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
            </div>

            <div class="grid min-w-0 gap-4 lg:grid-cols-3">
                <div class="min-w-0">
                    <x-input-label for="member_gender" value="Gender" />
                    <select id="member_gender" name="gender" class="member-form-input mt-2 min-h-12" required autocomplete="sex">
                        <option value="male" @selected(old('gender', $member->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $member->gender) === 'female')>Perempuan</option>
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                </div>

                <x-birth-date-selects
                    id="member_birth_date"
                    :value="$member->birth_date"
                    label-class="mb-2 block text-sm type-compact text-zinc-700 dark:text-zinc-300"
                    select-class="member-form-input min-h-12"
                    error-class="mt-2"
                />

                <div class="min-w-0">
                    <x-input-label for="member_emergency_contact" value="Kontak Darurat" />
                    <x-text-input id="member_emergency_contact" name="emergency_contact" type="tel" class="mt-2 block min-h-12 w-full" :value="old('emergency_contact', $member->emergency_contact)" inputmode="tel" autocomplete="tel" placeholder="081234567890" />
                    <x-input-error class="mt-2" :messages="$errors->get('emergency_contact')" />
                </div>
            </div>

            <div class="min-w-0">
                <x-input-label for="member_address" value="Alamat" />
                <textarea id="member_address" name="address" rows="3" class="member-form-input mt-2 min-h-28 resize-y" autocomplete="street-address" placeholder="Alamat domisili atau alamat utama yang bisa dihubungi">{{ old('address', $member->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div class="member-soft-panel min-w-0">
                <label for="member_is_student" class="flex items-start gap-3">
                    <input id="member_is_student" name="is_student" type="checkbox" value="1" class="mt-0.5 h-6 w-6 rounded border-zinc-300 text-gold-700 focus:ring-gold-700/40 dark:border-white/20 dark:bg-zinc-950 dark:text-gold-400 dark:focus:ring-gold-400/35" @checked(old('is_student', $member->is_student))>
                    <span class="min-w-0">
                        <span class="block text-sm type-control text-zinc-950 dark:text-zinc-100">Member mahasiswa</span>
                        <span class="mt-1 block break-words text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">Upload KTM atau screenshot akun portal mahasiswa untuk checkout paket mahasiswa.</span>
                    </span>
                </label>
                <div class="mt-4 grid min-w-0 gap-4 lg:grid-cols-[9rem_minmax(0,1fr)]">
                    <div class="grid aspect-[4/3] min-h-28 min-w-0 place-items-center overflow-hidden rounded-lg border border-dashed border-gold-500/35 bg-gold-500/10 text-center">
                        <template x-if="studentProofPreviewUrl">
                            <img x-bind:src="studentProofPreviewUrl" alt="" class="h-full w-full object-cover">
                        </template>
                        @if ($member->student_proof_path)
                            <template x-if="! studentProofPreviewUrl">
                                <img src="{{ route('member.profile.student-proof') }}" alt="Bukti mahasiswa tersimpan" class="h-full w-full object-cover" loading="lazy">
                            </template>
                        @else
                            <template x-if="! studentProofPreviewUrl">
                                <span class="px-3 text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400">Belum diunggah</span>
                            </template>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <x-input-label for="member_student_proof" value="Bukti Mahasiswa" />
                            <span class="member-status-pill {{ $member->student_proof_path ? 'member-status-success' : 'bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-300' }}">{{ $member->student_proof_path ? 'Sudah diunggah' : 'Belum diunggah' }}</span>
                        </div>
                        <input id="member_student_proof" name="student_proof" type="file" accept="image/jpeg,image/png,image/webp" class="member-form-input mt-2 file:mr-3 file:rounded-md file:border-0 file:bg-gold-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-zinc-950" x-on:change="setStudentProofPreview($event)">
                        <x-input-error class="mt-2" :messages="$errors->get('student_proof')" />
                        <p class="mt-2 text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">Upload KTM atau screenshot akun portal mahasiswa. JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                        <p class="mt-2 text-xs type-control uppercase tracking-[0.12em] text-zinc-700 dark:text-gold-400" x-show="studentProofPreviewActive" x-cloak>Preview, belum disimpan</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs type-control leading-5 text-zinc-500 dark:text-zinc-400">Kode member dan status akun dikelola oleh admin Platinum Gym.</p>
                <button type="submit" class="member-button-primary w-full sm:w-auto" x-bind:disabled="submitting"><span x-show="! submitting">Simpan Profil</span><span x-show="submitting">Menyimpan...</span></button>
            </div>
        </form>
    </section>

    <aside class="member-card h-fit min-w-0">
        <p class="member-eyebrow">Navigasi</p>
        <h3 class="mt-2 text-xl type-title text-zinc-950 dark:text-zinc-100">Kembali ke profil</h3>
        <p class="mt-3 member-copy">Lihat ringkasan profil atau atur keamanan akun.</p>
        <a href="{{ route('member.profile') }}" class="member-button-secondary mt-5 w-full">Lihat Profil</a>
        <a href="{{ route('profile.edit') }}" class="member-button-secondary mt-3 w-full">Keamanan Akun</a>
    </aside>
</div>
