<?php

namespace App\Features\Gymmi\Support;

use App\Models\ClassSchedule;
use App\Models\MemberPackageSession;
use App\Models\Membership;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Number;

class GymmiContextBuilder
{
    public function buildMemberOnly(User $user): string
    {
        return $this->memberContext($user);
    }

    public function build(string $context, ?User $user): string
    {
        $sections = [
            'Identitas: Platinum Gym Padang, pusat kebugaran di Padang dengan layanan gym, kelas, personal trainer, Muaythai, produk fitness, dan portal member.',
            $this->publicContactContext(),
            $this->packageContext(),
            $this->scheduleContext(),
        ];

        if ($context === 'member' && $user) {
            $sections[] = $this->memberContext($user);
        }

        $sections[] = 'Batasan: produk website hanya katalog dan stok, bukan checkout produk online. Payment online berlaku untuk membership dan paket sesi. QR mentah tidak boleh ditampilkan.';

        return collect($sections)
            ->filter()
            ->implode("\n\n");
    }

    private function publicContactContext(): string
    {
        $settings = Setting::query()
            ->whereIn('key', ['address', 'phone_display', 'phone_number', 'whatsapp_number', 'public_email', 'instagram_handle', 'operational_hours'])
            ->pluck('value', 'key');

        return 'Kontak dan lokasi: '.collect([
            'alamat' => $settings->get('address'),
            'telepon' => $settings->get('phone_display') ?: $settings->get('phone_number'),
            'WhatsApp' => $settings->get('whatsapp_number'),
            'email' => $settings->get('public_email'),
            'Instagram' => $settings->get('instagram_handle'),
            'jam operasional' => $settings->get('operational_hours'),
        ])->filter()->map(fn (string $value, string $key) => "{$key}: {$value}")->implode('; ');
    }

    private function packageContext(): string
    {
        $packages = Package::query()
            ->where('is_active', true)
            ->orderBy('package_kind')
            ->orderBy('price')
            ->limit(14)
            ->get(['name', 'package_kind', 'type', 'price', 'promo_price', 'duration_days', 'base_duration_days', 'bonus_duration_days', 'bonus_label', 'session_count', 'requires_active_membership']);

        if ($packages->isEmpty()) {
            return 'Paket layanan: data paket aktif belum tersedia.';
        }

        return 'Paket layanan aktif: '.$packages->map(function (Package $package): string {
            $price = $package->promo_price ?: $package->price;
            $duration = $package->durationMarketingLabel();
            $sessions = $package->session_count ? "{$package->session_count} sesi" : null;
            $requiresMembership = $package->requires_active_membership ? 'perlu membership aktif' : null;

            return collect([
                $package->name,
                $package->package_kind,
                $package->type,
                Number::currency((float) $price, 'IDR', 'id'),
                $duration,
                $sessions,
                $requiresMembership,
            ])->filter()->implode(' / ');
        })->implode('; ');
    }

    private function scheduleContext(): string
    {
        $schedules = ClassSchedule::query()
            ->with(['gymClass:id,name,access_type,required_package_type,member_price,non_member_price', 'trainer:id,name'])
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->limit(16)
            ->get(['id', 'gym_class_id', 'trainer_id', 'day_of_week', 'start_time', 'end_time', 'room', 'capacity']);

        if ($schedules->isEmpty()) {
            return 'Jadwal kelas: data jadwal aktif belum tersedia.';
        }

        return 'Jadwal kelas aktif: '.$schedules->map(function (ClassSchedule $schedule): string {
            $class = $schedule->gymClass;
            $trainer = $schedule->trainer?->name;
            $time = trim("{$schedule->start_time}-{$schedule->end_time}", '-');

            return collect([
                $class?->name,
                $schedule->day_of_week,
                $time,
                $trainer ? "trainer {$trainer}" : null,
                $schedule->room,
                $schedule->capacity ? "kapasitas {$schedule->capacity}" : null,
                $class?->access_type,
            ])->filter()->implode(' / ');
        })->implode('; ');
    }

    private function memberContext(User $user): string
    {
        $member = $user->member()->first();

        if (! $member) {
            return 'Data member login: profil member belum lengkap.';
        }

        $activeMembership = Membership::query()
            ->with('package:id,name')
            ->whereBelongsTo($member)
            ->activeForAccess()
            ->orderByRaw('case when end_date is null then 1 else 0 end')
            ->orderBy('end_date')
            ->orderBy('created_at')
            ->first();

        $activeSessions = MemberPackageSession::query()
            ->with('package:id,name')
            ->whereBelongsTo($member)
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query): void {
                $query->whereNull('expired_at')->orWhereDate('expired_at', '>=', now()->toDateString());
            })
            ->limit(5)
            ->get();

        $pendingPayments = Payment::query()
            ->whereBelongsTo($member)
            ->whereIn('status', ['pending', 'waiting_payment'])
            ->count();

        return collect([
            'Data member login: nama '.$user->name.', kode '.$member->member_code.'.',
            $activeMembership
                ? 'Membership aktif: '.$activeMembership->package?->name.' - '.$activeMembership->validityLabel().'.'
                : 'Membership aktif: belum ada atau sudah berakhir.',
            $activeSessions->isNotEmpty()
                ? 'Paket sesi aktif: '.$activeSessions->map(fn (MemberPackageSession $session) => $session->package?->name.' sisa '.$session->remaining_sessions.' sesi')->implode('; ').'.'
                : 'Paket sesi aktif: belum ada.',
            'Transaksi menunggu: '.$pendingPayments.'.',
        ])->filter()->implode(' ');
    }
}
