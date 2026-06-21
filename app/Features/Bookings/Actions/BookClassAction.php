<?php

namespace App\Features\Bookings\Actions;

use App\Features\Payments\Actions\CreatePaymentCheckoutAction;
use App\Models\ClassEnrollment;
use App\Models\ClassSchedule;
use App\Models\Member;
use App\Models\Payment;
use App\Notifications\MemberOperationalNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookClassAction
{
    public function __construct(private readonly CreatePaymentCheckoutAction $checkout) {}

    /**
     * @return array{enrollment: ClassEnrollment, payment: Payment|null}
     */
    public function handle(Member $member, ClassSchedule $schedule, CarbonImmutable $sessionDate): array
    {
        try {
            return DB::transaction(function () use ($member, $schedule, $sessionDate): array {
                $schedule = ClassSchedule::query()
                    ->with(['gymClass', 'trainer'])
                    ->lockForUpdate()
                    ->findOrFail($schedule->id);

                $gymClass = $schedule->gymClass;

                if (! $schedule->is_active || ! $gymClass?->is_active) {
                    throw new RuntimeException('Jadwal kelas tidak aktif.');
                }

                if ($sessionDate->isPast() && ! $sessionDate->isToday()) {
                    throw new RuntimeException('Tanggal kelas tidak boleh lewat.');
                }

                if ((int) $schedule->day_of_week !== $sessionDate->dayOfWeekIso) {
                    throw new RuntimeException('Tanggal yang dipilih tidak sesuai hari jadwal kelas.');
                }

                if ($existing = $this->existingEnrollment($member, $schedule, $sessionDate)) {
                    return $this->existingEnrollmentResult($existing);
                }

                $capacity = (int) ($schedule->capacity ?? $gymClass->capacity ?? 0);
                $booked = ClassEnrollment::query()
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('session_date', $sessionDate->toDateString())
                    ->whereIn('status', ['booked', 'active', 'confirmed', 'pending_payment'])
                    ->count();

                if ($capacity > 0 && $booked >= $capacity) {
                    throw new RuntimeException('Kuota kelas sudah penuh.');
                }

                if ($gymClass->access_type === 'included' && ! $this->hasMembershipForClass($member, (string) $gymClass->required_package_type)) {
                    throw new RuntimeException('Kelas ini membutuhkan membership aktif yang sesuai.');
                }

                if ($gymClass->access_type === 'session_based' && ! $this->hasPackageSessionForClass($member, (string) $gymClass->required_package_type)) {
                    throw new RuntimeException('Kelas ini membutuhkan membership aktif yang sesuai.');
                }

                $status = $gymClass->access_type === 'paid' ? 'pending_payment' : 'booked';

                $enrollment = ClassEnrollment::create([
                    'schedule_id' => $schedule->id,
                    'member_id' => $member->id,
                    'session_date' => $sessionDate->toDateString(),
                    'status' => $status,
                ]);

                $payment = null;

                if ($gymClass->access_type === 'paid') {
                    $amount = (float) ($gymClass->promo_price ?? $gymClass->member_price ?? $gymClass->non_member_price ?? 0);

                    if ($amount <= 0) {
                        throw new RuntimeException('Harga kelas belum valid.');
                    }

                    $payment = $this->checkout->classEnrollment($member, $enrollment, $amount);
                } else {
                    $member->user?->notify(new MemberOperationalNotification(
                        'Booking Kelas Berhasil',
                        'Booking '.$gymClass->name.' untuk '.$sessionDate->translatedFormat('d M Y').' sudah tercatat.',
                        route('member.bookings'),
                        'Lihat Riwayat',
                    ));
                }

                return ['enrollment' => $enrollment->refresh(), 'payment' => $payment];
            });
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                throw new RuntimeException('Anda sudah terdaftar pada jadwal kelas ini.');
            }

            throw $exception;
        }
    }

    private function existingEnrollment(Member $member, ClassSchedule $schedule, CarbonImmutable $sessionDate): ?ClassEnrollment
    {
        return ClassEnrollment::query()
            ->where('schedule_id', $schedule->id)
            ->where('member_id', $member->id)
            ->whereDate('session_date', $sessionDate->toDateString())
            ->lockForUpdate()
            ->first();
    }

    /**
     * @return array{enrollment: ClassEnrollment, payment: Payment|null}
     */
    private function existingEnrollmentResult(ClassEnrollment $enrollment): array
    {
        $payment = $enrollment->payments()
            ->whereIn('status', ['waiting_payment', 'pending', 'unpaid', 'waiting_confirmation'])
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNotNull('midtrans_redirect_url')
            ->latest('created_at')
            ->first();

        if ($payment && $enrollment->status === 'pending_payment') {
            return ['enrollment' => $enrollment->refresh(), 'payment' => $payment];
        }

        throw new RuntimeException('Anda sudah terdaftar pada jadwal kelas ini.');
    }

    private function hasMembershipForClass(Member $member, string $requiredPackageType): bool
    {
        return $member->memberships()
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereHas('package', function ($query) use ($requiredPackageType): void {
                $query->where('package_kind', 'membership')
                    ->where(function ($query) use ($requiredPackageType): void {
                        $query->where('type', $requiredPackageType)
                            ->orWhere('type', 'include');
                    });
            })
            ->exists();
    }

    private function hasPackageSessionForClass(Member $member, string $requiredPackageType): bool
    {
        return $member->packageSessions()
            ->where('status', 'active')
            ->where('remaining_sessions', '>', 0)
            ->where(function ($query): void {
                $query->whereNull('expired_at')->orWhereDate('expired_at', '>=', now()->toDateString());
            })
            ->whereHas('package', fn ($query) => $query->where('type', $requiredPackageType))
            ->exists();
    }
}
