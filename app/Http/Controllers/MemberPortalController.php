<?php

namespace App\Http\Controllers;

use App\Features\MemberPortal\Queries\MemberDashboardQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MemberPortalController extends Controller
{
    public function dashboard(Request $request, MemberDashboardQuery $query): View
    {
        return view('member.dashboard', [
            'portal' => $query->forUser($request->user()),
        ]);
    }

    public function profile(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'profil', 'Profil Member', 'Data akun dan identitas member Platinum Gym Padang.');
    }

    public function membership(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'membership', 'Membership', 'Status paket aktif dan daftar paket yang tersedia.');
    }

    public function booking(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'booking-kelas', 'Booking Kelas', 'Jadwal kelas aktif dan kuota kelas member Platinum Gym.');
    }

    public function bookings(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'riwayat-booking', 'Riwayat Booking', 'Jadwal mendatang dan riwayat kelas yang pernah dipesan.');
    }

    public function transactions(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'transaksi', 'Transaksi', 'Riwayat pembayaran dan status verifikasi.');
    }

    public function qr(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'qr', 'QR Member', 'Status kartu digital member untuk check-in Platinum Gym.');
    }

    public function notifications(Request $request, MemberDashboardQuery $query): View
    {
        return $this->page($request, $query, 'notifikasi', 'Notifikasi', 'Pemberitahuan membership, booking, dan pembayaran.');
    }

    private function page(Request $request, MemberDashboardQuery $query, string $key, string $title, string $description): View
    {
        return view('member.page', [
            'portal' => $query->forUser($request->user(), $key, $request->query()),
            'page' => [
                'key' => $key,
                'title' => $title,
                'description' => $description,
            ],
        ]);
    }
}
