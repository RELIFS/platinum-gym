<?php

namespace App\Features\Gymmi\Support;

use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promo;
use App\Models\QrToken;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GymmiLiveDataProvider
{
    public function __construct(
        private readonly GymmiIntentDetector $intentDetector,
        private readonly GymmiTextNormalizer $normalizer,
    ) {}

    /**
     * @param  array{type?: string, answer?: string|null, snippets?: array<int, string>, topic?: string|null, confidence?: int}  $match
     * @return array<int, string>
     */
    public function publicSnippets(string $message, array $match): array
    {
        $normalized = $this->normalize($message);
        $intent = $match['intent'] ?? $this->intentDetector->detect($message);

        if ($normalized === '') {
            return [];
        }

        return Cache::remember(
            'gymmi:live-public-snippets:'.sha1($normalized.'|'.(string) ($match['topic'] ?? '').'|'.serialize($intent)),
            now()->addMinutes(10),
            fn (): array => $this->uncachedPublicSnippets($normalized, $intent),
        );
    }

    /**
     * @return array<int, string>
     */
    public function memberSnippets(User $user, string $message): array
    {
        $member = $user->member()->first();

        if (! $member) {
            return [];
        }

        $normalized = $this->normalize($message);
        $snippets = [];

        if ($this->hasAny($normalized, ['membership', 'member', 'masa aktif', 'aktif', 'qr'])) {
            $snippets = array_merge($snippets, $this->memberMembershipSnippets($member));
        }

        if ($this->hasAny($normalized, ['sesi', 'paket sesi', 'pt', 'personal trainer', 'trainer'])) {
            $snippets = array_merge($snippets, $this->memberSessionSnippets($member));
        }

        if ($this->hasAny($normalized, ['transaksi', 'payment', 'pembayaran', 'tagihan', 'invoice', 'bayar'])) {
            $snippets = array_merge($snippets, $this->memberPaymentSnippets($member));
        }

        if ($this->hasAny($normalized, ['booking', 'kelas saya', 'jadwal saya', 'reservasi'])) {
            $snippets = array_merge($snippets, $this->memberBookingSnippets($member));
        }

        if ($this->hasAny($normalized, ['qr', 'check in', 'check-in', 'kartu'])) {
            $snippets[] = $this->memberQrSnippet($member);
        }

        return $this->limit($snippets, 8);
    }

    /**
     * @return array<int, string>
     */
    private function uncachedPublicSnippets(string $normalized, array $intent): array
    {
        $snippets = [];
        $isLocationIntent = ($intent['intent'] ?? null) === 'location_contact'
            || $this->hasAny($normalized, ['alamat', 'lokasi', 'dimana', 'di mana', 'arah', 'rute', 'maps', 'google maps', 'wa', 'whatsapp', 'kontak', 'instagram', 'ig', 'jam buka', 'operasional']);

        if (! $isLocationIntent && $this->shouldUsePackageSnippets($normalized, $intent)) {
            $snippets = array_merge($snippets, $this->packageSnippets($normalized));
        }

        if ($this->hasAny($normalized, ['promo', 'diskon', 'potongan', 'voucher'])) {
            $snippets = array_merge($snippets, $this->promoSnippets($normalized));
        }

        if ($this->shouldUseScheduleSnippets($normalized, $intent)) {
            $snippets = array_merge($snippets, $this->scheduleSnippets($normalized, $intent));
        }

        if (($intent['intent'] ?? null) === 'product_stock' || $this->hasAny($normalized, ['produk', 'stok', 'minuman', 'makanan', 'suplemen', 'protein', 'jual', 'beli', 'wrap', 'sarung'])) {
            $snippets = array_merge($snippets, $this->productSnippets($normalized));
        }

        if ($isLocationIntent) {
            $snippets = array_merge($snippets, $this->settingSnippets($normalized));
        }

        return $this->limit($snippets, 8);
    }

    /**
     * @return array<int, string>
     */
    private function packageSnippets(string $message): array
    {
        $tokens = $this->tokens($message);

        $rows = Package::query()
            ->where('is_active', true)
            ->orderBy('package_kind')
            ->orderBy('price')
            ->limit(24)
            ->get([
                'id',
                'name',
                'package_kind',
                'type',
                'category',
                'price',
                'promo_price',
                'promo_starts_at',
                'promo_ends_at',
                'duration_days',
                'base_duration_days',
                'bonus_duration_days',
                'bonus_label',
                'session_count',
                'requires_active_membership',
                'description',
            ])
            ->map(fn (Package $package): array => [
                'package' => $package,
                'score' => $this->score($message, $tokens, collect([
                    $package->name,
                    $package->package_kind,
                    $package->type,
                    $package->category,
                    $package->description,
                ])->filter()->implode(' ')),
            ])
            ->filter(fn (array $row): bool => $row['score'] > 0 || count($tokens) <= 2)
            ->sortByDesc('score')
            ->values();

        if ($rows->isEmpty()) {
            return [];
        }

        $packages = $this->focusedPackageRows($message, $rows)
            ->take(5)
            ->pluck('package')
            ->values();

        if ($packages->isEmpty()) {
            return [];
        }

        return [$this->packageReply($message, $packages)];
    }

    private function shouldUsePackageSnippets(string $message, array $intent): bool
    {
        if (($intent['intent'] ?? null) === 'membership_price') {
            return true;
        }

        if ($this->isClassPackageQuestion($message, $intent)) {
            return true;
        }

        return ($intent['subject'] ?? null) === null
            && $this->hasAny($message, ['harga', 'biaya', 'paket', 'membership', 'member', 'gym', 'pt', 'personal trainer', 'sesi', 'muaythai', 'poundfit']);
    }

    /**
     * @return array<int, string>
     */
    private function promoSnippets(string $message): array
    {
        $tokens = $this->tokens($message);

        return Promo::query()
            ->with(['package:id,name,is_active'])
            ->where('is_published', true)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('package_id')->orWhereHas('package', fn ($packageQuery) => $packageQuery->where('is_active', true));
            })
            ->orderBy('sort_order')
            ->orderByDesc('starts_at')
            ->limit(12)
            ->get(['id', 'package_id', 'title', 'description', 'starts_at', 'ends_at', 'discount_type', 'discount_value'])
            ->map(fn (Promo $promo): array => [
                'score' => $this->score($message, $tokens, collect([$promo->title, $promo->description, $promo->package?->name])->filter()->implode(' ')),
                'text' => $this->promoText($promo),
            ])
            ->filter(fn (array $row): bool => $row['score'] > 0 || count($tokens) <= 2 || str_contains($message, 'promo'))
            ->sortByDesc('score')
            ->take(4)
            ->pluck('text')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function scheduleSnippets(string $message, array $intent): array
    {
        $tokens = $this->tokens($message);
        $subject = $intent['subject'] ?? null;
        $intentName = (string) ($intent['intent'] ?? 'class_schedule');
        $limit = in_array($intentName, ['private_or_group', 'class_capacity', 'class_price', 'class_coach'], true) ? 2 : 6;

        $snippets = ClassSchedule::query()
            ->with([
                'gymClass:id,name,class_type,access_type,required_package_type,member_price,non_member_price,promo_price,is_active',
                'trainer:id,name,specialization,is_active',
            ])
            ->where('is_active', true)
            ->whereHas('gymClass', fn ($query) => $query->where('is_active', true))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(24)
            ->get(['id', 'gym_class_id', 'trainer_id', 'day_of_week', 'start_time', 'end_time', 'room', 'capacity'])
            ->map(function (ClassSchedule $schedule) use ($message, $tokens, $subject, $intentName): array {
                $haystack = collect([
                    $schedule->gymClass?->name,
                    $schedule->gymClass?->class_type,
                    $schedule->gymClass?->access_type,
                    $schedule->trainer?->name,
                    $schedule->trainer?->specialization,
                    $this->dayLabel((int) $schedule->day_of_week),
                ])->filter()->implode(' ');

                return [
                    'score' => $this->score($message, $tokens, $haystack) + $this->subjectScore($haystack, $subject),
                    'subject_match' => $subject === null || $this->subjectMatches($haystack, $subject),
                    'text' => $this->scheduleText($schedule, $intentName),
                ];
            })
            ->filter(fn (array $row): bool => $row['subject_match'] && ($row['score'] > 0 || count($tokens) <= 2))
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('text')
            ->values()
            ->all();

        if ($subject === 'muaythai' && in_array($intentName, ['private_or_group', 'class_capacity'], true)) {
            $snippets[] = 'Belum bisa dipastikan dari data resmi apakah Muaythai tersedia sebagai sesi privat satu member dengan coach. Jika ingin latihan hanya dengan coach, konfirmasi ketersediaan ke admin Platinum Gym.';
        }

        return $snippets;
    }

    /**
     * @return array<int, string>
     */
    private function productSnippets(string $message): array
    {
        $tokens = $this->tokens($message);

        return Product::query()
            ->with(['category:id,name,is_active'])
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('category_id')->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'category_id', 'name', 'price', 'stock', 'description'])
            ->map(fn (Product $product): array => [
                'score' => $this->score($message, $tokens, collect([$product->name, $product->category?->name, $product->description])->filter()->implode(' ')),
                'text' => $this->productText($product),
            ])
            ->filter(fn (array $row): bool => $row['score'] > 0 || count($tokens) <= 2 || str_contains($message, 'produk'))
            ->sortByDesc('score')
            ->take(6)
            ->pluck('text')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function settingSnippets(string $message): array
    {
        $settings = Setting::query()
            ->whereIn('key', ['address', 'phone_display', 'whatsapp_number', 'instagram_handle', 'instagram_url', 'maps_url', 'maps_search_url', 'maps_shared_url'])
            ->pluck('value', 'key');

        $snippets = [];

        if ($this->hasAny($message, ['alamat', 'lokasi'])) {
            $this->pushIfFilled($snippets, 'Alamat Platinum Gym', $settings->get('address'));
        }

        if ($this->hasAny($message, ['maps', 'google maps'])) {
            $this->pushIfFilled($snippets, 'Google Maps Platinum Gym', $settings->get('maps_shared_url') ?: $settings->get('maps_url') ?: $settings->get('maps_search_url'));
        }

        if ($this->hasAny($message, ['wa', 'whatsapp', 'kontak'])) {
            $this->pushIfFilled($snippets, 'WhatsApp Platinum Gym', $settings->get('whatsapp_number') ?: $settings->get('phone_display'));
        }

        if ($this->hasAny($message, ['instagram', 'ig'])) {
            $this->pushIfFilled($snippets, 'Instagram Platinum Gym', $settings->get('instagram_handle') ?: $settings->get('instagram_url'));
        }

        return $snippets;
    }

    /**
     * @return array<int, string>
     */
    private function memberMembershipSnippets(Member $member): array
    {
        $membership = Membership::query()
            ->with('package:id,name')
            ->whereBelongsTo($member)
            ->activeForAccess()
            ->orderByRaw('case when end_date is null then 1 else 0 end')
            ->orderBy('end_date')
            ->first(['id', 'member_id', 'package_id', 'start_date', 'end_date', 'status']);

        if (! $membership) {
            return ['Membership Anda: belum ada membership aktif di akun ini.'];
        }

        return ['Membership Anda: '.$membership->package?->name.' - '.$membership->validityLabel().'.'];
    }

    /**
     * @return array<int, string>
     */
    private function memberSessionSnippets(Member $member): array
    {
        return MemberPackageSession::query()
            ->with(['package:id,name', 'trainer:id,name'])
            ->whereBelongsTo($member)
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query): void {
                $query->whereNull('expired_at')->orWhereDate('expired_at', '>=', now()->toDateString());
            })
            ->orderBy('expired_at')
            ->limit(4)
            ->get(['id', 'member_id', 'package_id', 'trainer_id', 'remaining_sessions', 'expired_at', 'status'])
            ->map(function (MemberPackageSession $session): string {
                return collect([
                    'Paket sesi Anda: '.$session->package?->name,
                    'sisa '.$session->remaining_sessions.' sesi',
                    $session->trainer ? 'trainer '.$session->trainer->name : null,
                    $session->expired_at ? 'berlaku sampai '.$session->expired_at->translatedFormat('d M Y') : null,
                ])->filter()->implode(' / ').'.';
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function memberPaymentSnippets(Member $member): array
    {
        return Payment::query()
            ->with('payable')
            ->whereBelongsTo($member)
            ->whereIn('status', ['pending', 'waiting_payment', 'awaiting_confirmation'])
            ->latest()
            ->limit(4)
            ->get(['id', 'payment_code', 'member_id', 'payable_type', 'payable_id', 'method', 'amount', 'status', 'created_at'])
            ->map(function (Payment $payment): string {
                return collect([
                    'Transaksi Anda '.$payment->payment_code,
                    $this->payableLabel($payment),
                    'status '.$this->statusLabel($payment->status),
                    'metode '.$payment->method,
                    $this->rupiah($payment->amount),
                ])->filter()->implode(' / ').'.';
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function memberBookingSnippets(Member $member): array
    {
        return ClassEnrollment::query()
            ->with(['schedule:id,gym_class_id,trainer_id,day_of_week,start_time,end_time,room', 'schedule.gymClass:id,name', 'schedule.trainer:id,name'])
            ->whereBelongsTo($member)
            ->whereDate('session_date', '>=', now()->subDays(7)->toDateString())
            ->latest('session_date')
            ->limit(4)
            ->get(['id', 'schedule_id', 'member_id', 'session_date', 'status'])
            ->map(function (ClassEnrollment $enrollment): string {
                $schedule = $enrollment->schedule;

                return collect([
                    'Booking Anda: '.$schedule?->gymClass?->name,
                    $enrollment->session_date?->translatedFormat('d M Y'),
                    $schedule ? $this->timeRange($schedule->start_time, $schedule->end_time) : null,
                    $schedule?->trainer ? 'trainer '.$schedule->trainer->name : null,
                    'status '.$this->statusLabel($enrollment->status),
                ])->filter()->implode(' / ').'.';
            })
            ->values()
            ->all();
    }

    private function memberQrSnippet(Member $member): string
    {
        $hasActiveQr = QrToken::query()
            ->where('tokenable_type', Member::class)
            ->where('tokenable_id', $member->id)
            ->where('is_revoked', false)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        return $hasActiveQr
            ? 'QR member Anda aktif untuk check-in. Kode QR mentah tidak ditampilkan demi keamanan.'
            : 'QR member Anda belum aktif atau sudah tidak berlaku. Pastikan membership aktif di portal member.';
    }

    /**
     * @param  Collection<int, array{package: Package, score: int}>  $rows
     * @return Collection<int, array{package: Package, score: int}>
     */
    private function focusedPackageRows(string $message, Collection $rows): Collection
    {
        $focused = null;

        if (str_contains($message, 'gym umum') && ! str_contains($message, 'senam')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['gym umum'])
                && ! $this->packageNameHas($row['package'], ['senam', 'mahasiswa']));
        } elseif (str_contains($message, 'gym mahasiswa') && ! str_contains($message, 'senam')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['gym mahasiswa'])
                && ! $this->packageNameHas($row['package'], ['senam']));
        } elseif (str_contains($message, 'gym') && str_contains($message, 'senam') && str_contains($message, 'umum')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['gym', 'senam', 'umum']));
        } elseif (str_contains($message, 'gym') && str_contains($message, 'senam') && str_contains($message, 'mahasiswa')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['gym', 'senam', 'mahasiswa']));
        } elseif (str_contains($message, 'senam umum')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['senam umum'])
                && ! $this->packageNameHas($row['package'], ['mahasiswa']));
        } elseif (str_contains($message, 'senam mahasiswa')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['senam mahasiswa']));
        } elseif (str_contains($message, 'muaythai')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['muaythai']));
        } elseif (str_contains($message, 'poundfit')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['poundfit']));
        } elseif (str_contains($message, 'personal trainer')) {
            $focused = $rows->filter(fn (array $row): bool => $this->packageNameHas($row['package'], ['personal trainer']));
        }

        return $focused instanceof Collection && $focused->isNotEmpty() ? $focused->values() : $rows;
    }

    /**
     * @param  Collection<int, Package>  $packages
     */
    private function packageReply(string $message, Collection $packages): string
    {
        if ($this->isClassPackageMessage($message) && ! $this->wantsPackageList($message)) {
            return $this->compactClassPackageReply($message, $packages);
        }

        if ($packages->count() === 1) {
            return 'Harga '.$this->packageText($packages->first()).'.';
        }

        return 'Harga '.$this->packageSubjectLabel($message, $packages).' yang tersedia:'."\n"
            .$packages
                ->map(fn (Package $package): string => '- '.$this->packageText($package))
                ->implode("\n");
    }

    /**
     * @param  Collection<int, Package>  $packages
     */
    private function packageSubjectLabel(string $message, Collection $packages): string
    {
        return match (true) {
            str_contains($message, 'gym umum') && ! str_contains($message, 'senam') => 'Gym Umum',
            str_contains($message, 'gym mahasiswa') && ! str_contains($message, 'senam') => 'Gym Mahasiswa',
            str_contains($message, 'gym') && str_contains($message, 'senam') && str_contains($message, 'umum') => 'Gym + Senam Umum',
            str_contains($message, 'gym') && str_contains($message, 'senam') && str_contains($message, 'mahasiswa') => 'Gym + Senam Mahasiswa',
            str_contains($message, 'senam umum') => 'Senam Umum',
            str_contains($message, 'senam mahasiswa') => 'Senam Mahasiswa',
            str_contains($message, 'muaythai') => 'Muaythai',
            str_contains($message, 'poundfit') => 'Poundfit',
            str_contains($message, 'personal trainer') => 'Personal Trainer',
            default => 'paket',
        };
    }

    /**
     * @param  Collection<int, Package>  $packages
     */
    private function compactClassPackageReply(string $message, Collection $packages): string
    {
        $ordered = $packages
            ->sortBy(fn (Package $package): float => $this->packagePrice($package))
            ->values();
        $startingPackage = $ordered->first();

        if (! $startingPackage instanceof Package) {
            return '';
        }

        $label = $this->packageSubjectLabel($message, $ordered);
        $prefix = $ordered->count() > 1 ? 'mulai ' : '';
        $reply = 'Harga '.$label.' '.$prefix.$this->rupiah($this->packagePrice($startingPackage)).' untuk '.$this->packageUnit($startingPackage).'.';
        $sessionOptions = $this->additionalSessionOptions($ordered, $startingPackage);

        if ($sessionOptions !== '') {
            $audience = $this->packageAudienceLabel($ordered);
            $reply .= ' Paket '.$sessionOptions.' juga tersedia'.($audience !== '' ? ' untuk '.$audience : '').'.';
        }

        return $reply;
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function packageNameHas(Package $package, array $needles): bool
    {
        $haystack = $this->normalize(collect([
            $package->name,
            $package->package_kind,
            $package->type,
            $package->category,
        ])->filter()->implode(' '));

        foreach ($needles as $needle) {
            if (! str_contains($haystack, $this->normalize($needle))) {
                return false;
            }
        }

        return true;
    }

    private function packageText(Package $package): string
    {
        return $package->name.': '.$this->packageDetails($package);
    }

    private function packageDetails(Package $package): string
    {
        $activePromoPrice = $this->activePromoPrice($package);
        $price = $this->rupiah($activePromoPrice ?: $package->price);

        $notes = collect([
            $activePromoPrice ? 'harga normal '.$this->rupiah($package->price) : null,
            $package->durationMarketingLabel(),
            $package->session_count ? $package->session_count.' sesi' : null,
            $package->requires_active_membership ? 'perlu membership aktif' : null,
        ])->filter()->implode(', ');

        return $price.($notes !== '' ? ' ('.$notes.')' : '');
    }

    private function packagePrice(Package $package): float
    {
        return (float) ($this->activePromoPrice($package) ?: $package->price);
    }

    private function packageUnit(Package $package): string
    {
        if ($package->session_count) {
            return $package->session_count.' sesi';
        }

        $duration = $package->durationMarketingLabel();

        return $duration !== '' ? $duration : 'paket ini';
    }

    /**
     * @param  Collection<int, Package>  $packages
     */
    private function additionalSessionOptions(Collection $packages, Package $startingPackage): string
    {
        $options = $packages
            ->pluck('session_count')
            ->filter(fn (mixed $sessionCount): bool => (int) $sessionCount > (int) $startingPackage->session_count)
            ->map(fn (mixed $sessionCount): int => (int) $sessionCount)
            ->unique()
            ->sort()
            ->map(fn (int $sessionCount): string => $sessionCount.'x')
            ->values()
            ->all();

        return $this->joinLabels($options);
    }

    /**
     * @param  Collection<int, Package>  $packages
     */
    private function packageAudienceLabel(Collection $packages): string
    {
        $audiences = $packages
            ->pluck('category')
            ->filter(fn (mixed $category): bool => is_string($category) && filled(trim($category)))
            ->map(fn (string $category): string => trim($category))
            ->unique()
            ->values()
            ->all();

        return implode('/', $audiences);
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function joinLabels(array $labels): string
    {
        if (count($labels) <= 1) {
            return $labels[0] ?? '';
        }

        $last = array_pop($labels);

        return implode(', ', $labels).' dan '.$last;
    }

    private function promoText(Promo $promo): string
    {
        $details = collect([
            $promo->package ? 'untuk '.$promo->package->name : null,
            $promo->description ? Str::limit($promo->description, 120, '') : null,
            $promo->ends_at ? 'berlaku sampai '.$promo->ends_at->translatedFormat('d M Y') : null,
        ])->filter()->implode(', ');

        return 'Promo aktif '.$promo->title.($details !== '' ? ' '.$details : '').'.';
    }

    private function scheduleText(ClassSchedule $schedule, string $intent): string
    {
        $class = $schedule->gymClass;
        $name = $class?->name ?: 'Kelas';
        $time = collect([
            $this->dayLabel((int) $schedule->day_of_week),
            $this->timeRange($schedule->start_time, $schedule->end_time) ? 'pukul '.$this->timeRange($schedule->start_time, $schedule->end_time) : null,
        ])->filter()->implode(' ');

        $details = collect([
            $schedule->trainer ? 'bersama '.$schedule->trainer->name : null,
            $schedule->room ? 'di '.$schedule->room : null,
            $schedule->capacity ? 'kapasitas '.$schedule->capacity.' peserta' : null,
            $this->accessLabel($class?->access_type),
            $class?->member_price ? 'harga member '.$this->rupiah($class->member_price) : null,
            $class?->non_member_price ? 'harga non-member '.$this->rupiah($class->non_member_price) : null,
        ])->filter()->implode(', ');

        if ($intent === 'private_or_group') {
            return $name.' tercatat sebagai kelas berjadwal'.($details !== '' ? ' dengan '.$details : '').'.';
        }

        if ($intent === 'class_capacity') {
            return $name.' memiliki '.($schedule->capacity ? 'kapasitas '.$schedule->capacity.' peserta' : 'kapasitas yang belum tercatat').' pada data jadwal aktif.';
        }

        return $name.' tersedia '.$time.($details !== '' ? ' dengan '.$details : '').'.';
    }

    private function productText(Product $product): string
    {
        $details = collect([
            $product->category ? 'kategori '.$product->category->name : null,
            'harga '.$this->rupiah($product->price),
            'stok '.max((int) $product->stock, 0),
        ])->filter()->implode(', ');

        return 'Produk aktif '.$product->name.' memiliki '.$details.'. Produk hanya katalog informasi; pembelian dikonfirmasi ke admin atau di lokasi gym.';
    }

    private function accessLabel(?string $accessType): ?string
    {
        return match ($accessType) {
            'included', 'member' => 'akses mengikuti paket yang sesuai',
            'session_based', 'separate' => 'kelas terpisah atau berbayar',
            default => null,
        };
    }

    private function payableLabel(Payment $payment): ?string
    {
        $payable = $payment->payable;

        if ($payable instanceof Membership) {
            $payable->loadMissing('package:id,name');

            return 'membership '.$payable->package?->name;
        }

        if ($payable instanceof MemberPackageSession) {
            $payable->loadMissing('package:id,name');

            return 'paket sesi '.$payable->package?->name;
        }

        if ($payable instanceof ClassEnrollment) {
            $payable->loadMissing('schedule.gymClass:id,name');

            return 'booking '.$payable->schedule?->gymClass?->name;
        }

        return null;
    }

    private function activePromoPrice(Package $package): ?string
    {
        if (! $package->promo_price) {
            return null;
        }

        $startsAt = $package->promo_starts_at;
        $endsAt = $package->promo_ends_at;

        if ($startsAt && $startsAt->isFuture()) {
            return null;
        }

        if ($endsAt && $endsAt->isPast()) {
            return null;
        }

        return (string) $package->promo_price;
    }

    private function timeRange(mixed $start, mixed $end): string
    {
        return trim($this->timeLabel($start).'-'.$this->timeLabel($end), '-');
    }

    private function timeLabel(mixed $time): string
    {
        return substr((string) $time, 0, 5);
    }

    private function dayLabel(int $day): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][$day] ?? 'Hari belum ditentukan';
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'waiting_payment' => 'menunggu pembayaran',
            'awaiting_confirmation' => 'menunggu konfirmasi',
            'pending' => 'pending',
            'confirmed' => 'terkonfirmasi',
            'attended' => 'hadir',
            'cancelled', 'canceled' => 'dibatalkan',
            'active' => 'aktif',
            default => $status ?: 'belum tersedia',
        };
    }

    private function rupiah(mixed $amount): string
    {
        return 'Rp'.number_format((float) $amount, 0, ',', '.');
    }

    /**
     * @param  array<int, string>  $target
     */
    private function pushIfFilled(array &$target, string $label, mixed $value): void
    {
        if (is_string($value) && filled(trim($value))) {
            $target[] = $label.': '.trim($value).'.';
        }
    }

    /**
     * @param  array<int, string>  $snippets
     * @return array<int, string>
     */
    private function limit(array $snippets, int $limit): array
    {
        return collect($snippets)
            ->map(fn (string $snippet): string => trim($snippet))
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }

    private function shouldUseScheduleSnippets(string $message, array $intent): bool
    {
        $intentName = (string) ($intent['intent'] ?? '');

        if ($this->isClassPackageQuestion($message, $intent)) {
            return false;
        }

        if (in_array($intentName, ['class_schedule', 'class_coach', 'class_capacity', 'private_or_group'], true)) {
            return true;
        }

        if ($intentName === 'class_price') {
            return false;
        }

        return $this->hasAny($message, ['jadwal', 'kelas', 'senam', 'zumba', 'trainer', 'coach']);
    }

    private function isClassPackageQuestion(string $message, array $intent): bool
    {
        return in_array((string) ($intent['subject'] ?? ''), ['muaythai', 'poundfit'], true)
            && $this->isClassPackageMessage($message)
            && ! $this->isScheduleQuestion($message)
            && ! $this->isPackageRuleQuestion($message);
    }

    private function isClassPackageMessage(string $message): bool
    {
        return $this->hasAny($message, ['muaythai', 'poundfit'])
            && $this->hasAny($message, ['harga', 'biaya', 'tarif', 'berapa', 'bayar', 'paket', 'sesi', 'pilihan', 'daftar', 'semua', 'opsi']);
    }

    private function isScheduleQuestion(string $message): bool
    {
        return $this->hasAny($message, ['jadwal', 'hari apa', 'jam', 'kapan', 'booking']);
    }

    private function isPackageRuleQuestion(string $message): bool
    {
        return $this->hasAny($message, ['terpisah', 'termasuk', 'include', 'included']);
    }

    private function wantsPackageList(string $message): bool
    {
        return $this->hasAny($message, ['semua paket', 'daftar paket', 'pilihan paket', 'opsi paket', 'list paket', 'paket apa saja', 'harga lengkap', 'rincian harga', 'detail harga']);
    }

    private function subjectScore(string $haystack, ?string $subject): int
    {
        return $this->subjectMatches($haystack, $subject) ? 80 : 0;
    }

    private function subjectMatches(string $haystack, ?string $subject): bool
    {
        if ($subject === null) {
            return true;
        }

        $haystack = $this->normalize($haystack);

        foreach ($this->intentDetector->subjectAliases($subject) as $alias) {
            $alias = $this->normalize($alias);

            if ($alias !== '' && str_contains($haystack, $alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function score(string $message, array $tokens, string $haystack): int
    {
        $haystack = $this->normalize($haystack);
        $score = 0;

        foreach ($tokens as $token) {
            if (str_contains($haystack, $token)) {
                $score += 10;
            }
        }

        if ($haystack !== '' && str_contains($message, $haystack)) {
            $score += 40;
        }

        return $score;
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $message): array
    {
        $stopwords = ['apa', 'ada', 'yang', 'dan', 'atau', 'saya', 'mau', 'ingin', 'info', 'berapa', 'bagaimana', 'gimana', 'untuk', 'dari'];

        return collect(explode(' ', $this->normalize($message)))
            ->filter(fn (string $token): bool => mb_strlen($token) >= 3 && ! in_array($token, $stopwords, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function hasAny(string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            $needle = $this->normalize($needle);

            if ($needle !== '' && str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return $this->normalizer->normalize($value);
    }
}
