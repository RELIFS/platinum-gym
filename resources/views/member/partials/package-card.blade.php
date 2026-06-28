@php
    $packageMeta = $package->member_status_meta ?? [
        'kind_label' => str((string) $package->package_kind)->replace('_', ' ')->headline()->toString(),
        'is_membership' => (string) $package->package_kind === 'membership',
        'type_label' => (string) $package->package_kind === 'membership' ? 'Membership' : 'Sesi',
        'requires_trainer' => false,
        'price' => (float) ($package->price ?? 0),
        'promo_price' => filled($package->promo_price) ? (float) $package->promo_price : null,
        'has_promo' => false,
        'display_price' => (float) ($package->promo_price ?? $package->price ?? 0),
        'duration_label' => $package->durationMarketingLabel(),
        'bonus_label' => $package->durationBonusLabel(),
        'effective_duration_days' => $package->effectiveDurationDays(),
    ];
    $eligibility = $package->member_eligibility ?? [
        'can_checkout' => true,
        'reason' => null,
        'cta_route' => null,
        'cta_label' => null,
        'button_label' => (bool) ($packageMeta['is_membership'] ?? false) ? 'Checkout Membership' : 'Checkout Paket Sesi',
        'is_student_package' => false,
        'is_pt_package' => false,
        'is_gender_restricted' => false,
    ];
    $isMembership = (bool) ($packageMeta['is_membership'] ?? false);
    $canCheckout = (bool) ($eligibility['can_checkout'] ?? true);
    $checkoutRoute = $isMembership ? route('member.membership.checkout', $package) : route('member.package-sessions.checkout', $package);
    $requiresTrainer = (bool) ($packageMeta['requires_trainer'] ?? false);
    $packageTrainerList = $trainerOptions[$package->id] ?? [];
    $trainerSelectId = 'trainer-select-'.$package->id;
    $checkoutDurationMessage = $isMembership && ! empty($packageMeta['effective_duration_days'])
        ? sprintf(' Masa aktif total %s hari, mulai saat check-in pertama.', number_format((int) $packageMeta['effective_duration_days'], 0, ',', '.'))
        : '';
    $confirmMessage = sprintf(
        'Lanjut checkout %s seharga Rp %s?%s Anda akan diarahkan ke pembayaran Midtrans.',
        addslashes((string) $package->name),
        number_format((float) $packageMeta['display_price'], 0, ',', '.'),
        $checkoutDurationMessage,
    );
    $categoryLabel = match (str((string) $package->category)->lower()->toString()) {
        'mahasiswa' => 'Mahasiswa',
        'umum' => 'Umum',
        default => filled($package->category) ? str((string) $package->category)->headline()->toString() : null,
    };
@endphp

<article class="member-list-card flex min-w-0 flex-col gap-0">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-black uppercase tracking-[0.16em] text-gold-600 dark:text-gold-400">{{ $packageMeta['kind_label'] }}</p>
        <div class="flex flex-wrap justify-end gap-2">
            @if ($categoryLabel)
                <span class="member-status-pill member-status-neutral">{{ $categoryLabel }}</span>
            @endif
            @if ($eligibility['is_gender_restricted'] ?? false)
                <span class="member-status-pill member-status-warning">Khusus Perempuan</span>
            @endif
            @if ($eligibility['is_pt_package'] ?? false)
                <span class="member-status-pill member-status-info">Personal Trainer</span>
            @else
                <span class="member-status-pill {{ $isMembership ? 'member-status-info' : 'member-status-neutral' }}">{{ $packageMeta['type_label'] }}</span>
            @endif
        </div>
    </div>

    <h4 class="mt-3 min-w-0 break-words font-black text-zinc-950 dark:text-white">{{ $package->name }}</h4>
    <p class="mt-2 min-w-0 break-words text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $package->description ?? 'Paket Platinum Gym Padang.' }}</p>

    <div class="mt-4 flex flex-wrap items-baseline gap-2">
        <p class="text-xl font-black text-zinc-950 dark:text-white">Rp {{ number_format((float) $packageMeta['display_price'], 0, ',', '.') }}</p>
        @if ($packageMeta['has_promo'])
            <p class="text-sm font-bold text-zinc-400 line-through dark:text-zinc-500" aria-label="Harga normal">Rp {{ number_format((float) $packageMeta['price'], 0, ',', '.') }}</p>
            <span class="member-status-pill member-status-warning">Promo</span>
        @endif
    </div>
    @if ($isMembership && ! empty($packageMeta['duration_label']))
        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm font-bold leading-6 text-zinc-500 dark:text-zinc-400">
            <span class="min-w-0 break-words">Masa aktif: {{ $packageMeta['duration_label'] }}</span>
            @if (! empty($packageMeta['bonus_label']))
                <span class="member-status-pill member-status-warning">{{ $packageMeta['bonus_label'] }}</span>
            @endif
        </div>
    @endif

    <div class="mt-auto pt-4">
        @if ($canCheckout)
            <x-confirm-form
                :action="$checkoutRoute"
                method="POST"
                :message="$confirmMessage"
                variant="primary"
                confirm-label="Lanjut Checkout"
                class="grid gap-3"
            >
                @if ($requiresTrainer)
                    <div>
                        <label for="{{ $trainerSelectId }}" class="block text-xs font-black uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Pilih Trainer</label>
                        @if (! empty($packageTrainerList))
                            <select id="{{ $trainerSelectId }}" name="trainer_id" required class="member-form-input mt-2">
                                <option value="">Pilih trainer...</option>
                                @foreach ($packageTrainerList as $trainerOption)
                                    <option value="{{ $trainerOption['id'] }}" @selected((int) old('trainer_id') === (int) $trainerOption['id'])>{{ $trainerOption['label'] }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs font-semibold leading-5 text-zinc-500 dark:text-zinc-400">Trainer akan menerima notifikasi penjadwalan setelah pembayaran berhasil.</p>
                        @else
                            <p class="member-unavailable-note mt-2">Belum ada trainer aktif untuk paket ini. Hubungi admin Platinum Gym.</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('trainer_id')" />
                    </div>
                @endif
                <button type="submit" class="member-button-primary w-full" @disabled($requiresTrainer && empty($packageTrainerList))>
                    {{ $eligibility['button_label'] ?? ($isMembership ? 'Checkout Membership' : 'Checkout Paket Sesi') }}
                </button>
            </x-confirm-form>
        @else
            <button type="button" class="member-button-secondary w-full cursor-not-allowed justify-center opacity-70" disabled>
                {{ $eligibility['button_label'] ?? 'Tidak tersedia' }}
            </button>
            <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-3 text-sm font-bold leading-6 text-zinc-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-zinc-400">
                <p class="min-w-0 break-words">{{ $eligibility['reason'] ?? 'Paket belum tersedia untuk akun ini.' }}</p>
                @if (! empty($eligibility['cta_route']) && ! empty($eligibility['cta_label']))
                    <a href="{{ $eligibility['cta_route'] }}" class="mt-3 inline-flex min-h-10 max-w-full touch-manipulation items-center justify-center rounded-lg border border-gold-500/30 bg-gold-500/10 px-3 py-2 text-sm font-black text-gold-700 transition hover:border-gold-500/60 hover:bg-gold-500/15 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 dark:text-gold-400 dark:focus-visible:ring-offset-zinc-950">{{ $eligibility['cta_label'] }}</a>
                @endif
            </div>
        @endif
    </div>
</article>
