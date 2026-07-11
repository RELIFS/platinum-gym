<?php

namespace App\Features\Gymmi\Support;

class GymmiActionResolver
{
    /**
     * @return array{id: string, label: string, url: string}|null
     */
    public function resolve(GymmiTurnPlan $plan, string $surface): ?array
    {
        $intent = $plan->primaryIntent();

        if ($surface === 'member') {
            return match ($intent) {
                'member_membership', 'member_session', 'membership_price', 'class_price' => $this->action('view_membership', 'Buka Membership', route('member.membership')),
                'member_booking', 'class_schedule', 'class_coach', 'class_capacity' => $this->action('view_booking', 'Buka Booking Kelas', route('member.booking')),
                'member_payment' => $this->action('view_transactions', 'Buka Transaksi', route('member.transactions')),
                'member_qr' => $this->action('view_qr', 'Buka QR Member', route('member.qr')),
                'account_help' => $this->action('view_account_security', 'Buka Keamanan Akun', route('profile.edit')),
                'location_contact' => $this->action('view_location', 'Lihat Lokasi', route('public.location')),
                'product_stock' => $this->action('view_products', 'Lihat Produk', route('public.products')),
                default => null,
            };
        }

        return match ($intent) {
            'membership_price', 'registration' => $this->action('view_services', 'Lihat Layanan', route('public.services')),
            'class_price', 'class_schedule', 'class_coach', 'class_capacity', 'private_or_group' => $this->action('view_classes', 'Lihat Kelas', route('public.classes')),
            'product_stock' => $this->action('view_products', 'Lihat Produk', route('public.products')),
            'location_contact' => $this->action('view_location', 'Lihat Lokasi', route('public.location')),
            default => null,
        };
    }

    /**
     * @return array{id: string, label: string, url: string}
     */
    private function action(string $id, string $label, string $url): array
    {
        return compact('id', 'label', 'url');
    }
}
