<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCheckInController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminResourceController;
use App\Http\Controllers\Admin\AdminResourceStatusController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\AdminPortalController;
use App\Http\Controllers\GymmiChatController;
use App\Http\Controllers\Member\MemberBookingController;
use App\Http\Controllers\Member\MemberCheckoutController;
use App\Http\Controllers\Member\MemberNotificationController;
use App\Http\Controllers\Member\MemberProfileController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicWebsiteController;
use App\Http\Controllers\Webhook\MidtransWebhookController;
use App\Support\RoleRedirect;
use Illuminate\Support\Facades\Route;

Route::controller(PublicWebsiteController::class)->group(function () {
    Route::get('/', 'home')->name('public.home');
    Route::get('/tentang-kami', 'about')->name('public.about');
    Route::get('/layanan', 'services')->name('public.services');
    Route::get('/kelas', 'classes')->name('public.classes');
    Route::get('/produk', 'products')->name('public.products');
    Route::get('/galeri', 'gallery')->name('public.gallery');
    Route::get('/lokasi', 'location')->name('public.location');
    Route::get('/bmi', 'bmi')->name('public.bmi');
});

Route::view('/syarat-ketentuan', 'legal.terms')->name('legal.terms');
Route::view('/kebijakan-privasi', 'legal.privacy')->name('legal.privacy');
Route::post('/webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');
Route::post('/gymmi/chat', GymmiChatController::class)->middleware('throttle:gymmi')->name('gymmi.chat');

Route::get('/dashboard', function () {
    return redirect(RoleRedirect::pathFor(request()->user()) ?? route('login'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:member', 'member.profile.complete'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {
        Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [MemberPortalController::class, 'profile'])->name('profile');
        Route::patch('/profil', [MemberProfileController::class, 'update'])->name('profile.update');
        Route::get('/membership', [MemberPortalController::class, 'membership'])->name('membership');
        Route::post('/membership/{package}/checkout', [MemberCheckoutController::class, 'membership'])->name('membership.checkout');
        Route::post('/paket-sesi/{package}/checkout', [MemberCheckoutController::class, 'packageSession'])->name('package-sessions.checkout');
        Route::get('/booking-kelas', [MemberPortalController::class, 'booking'])->name('booking');
        Route::post('/booking-kelas/{schedule}', [MemberBookingController::class, 'store'])->name('booking.store');
        Route::get('/riwayat-booking', [MemberPortalController::class, 'bookings'])->name('bookings');
        Route::delete('/riwayat-booking/{enrollment}', [MemberBookingController::class, 'destroy'])->name('bookings.destroy');
        Route::get('/transaksi', [MemberPortalController::class, 'transactions'])->name('transactions');
        Route::get('/transaksi/{payment}', [MemberCheckoutController::class, 'show'])->name('transactions.show');
        Route::post('/transaksi/{payment}/bayar', [MemberCheckoutController::class, 'pay'])->name('transactions.pay');
        Route::get('/qr', [MemberPortalController::class, 'qr'])->name('qr');
        Route::get('/notifikasi', [MemberPortalController::class, 'notifications'])->name('notifications');
        Route::post('/notifikasi/{notification}/baca', [MemberNotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifikasi/baca-semua', [MemberNotificationController::class, 'readAll'])->name('notifications.read-all');
    });

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/check-in', [AdminPortalController::class, 'checkIn'])->name('check-in');
        Route::post('/check-in/scan', [AdminCheckInController::class, 'scan'])->name('check-in.scan');
        Route::post('/check-in/manual', [AdminCheckInController::class, 'manual'])->name('check-in.manual');
        Route::get('/booking', [AdminPortalController::class, 'booking'])->name('booking');
        Route::post('/booking', [AdminBookingController::class, 'store'])->name('booking.store');
        Route::post('/booking/{enrollment}/confirm', [AdminBookingController::class, 'confirm'])->name('booking.confirm');
        Route::post('/booking/{enrollment}/cancel', [AdminBookingController::class, 'cancel'])->name('booking.cancel');
        Route::get('/notifikasi', [AdminPortalController::class, 'notifications'])->name('notifications');
        Route::get('/resource/{resource}/tambah', [AdminResourceController::class, 'create'])->name('resources.create');
        Route::post('/resource/{resource}', [AdminResourceController::class, 'store'])->name('resources.store');
        Route::get('/resource/{resource}/{id}/edit', [AdminResourceController::class, 'edit'])->name('resources.edit');
        Route::patch('/resource/{resource}/{id}', [AdminResourceController::class, 'update'])->name('resources.update');
        Route::patch('/resource/{resource}/{id}/toggle', [AdminResourceStatusController::class, 'toggle'])->name('resources.toggle');
        Route::get('/anggota', [AdminPortalController::class, 'members'])->name('members');
        Route::get('/paket', [AdminPortalController::class, 'packages'])->name('packages');
        Route::get('/kelas', [AdminPortalController::class, 'classes'])->name('classes');
        Route::get('/pembayaran', [AdminPortalController::class, 'payments'])->name('payments');
        Route::post('/pembayaran/cash', [AdminPaymentController::class, 'storeCash'])->name('payments.cash');
        Route::post('/pembayaran/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/pembayaran/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
        Route::get('/produk', [AdminPortalController::class, 'products'])->name('products');
        Route::get('/galeri', [AdminPortalController::class, 'gallery'])->name('gallery');
        Route::get('/testimoni', [AdminPortalController::class, 'testimonials'])->name('testimonials');
        Route::get('/promo', [AdminPortalController::class, 'promos'])->name('promos');
        Route::get('/trainer', [AdminPortalController::class, 'trainers'])->name('trainers');
        Route::get('/laporan', [AdminPortalController::class, 'reports'])->name('reports');
        Route::get('/laporan/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/audit-log', [AdminPortalController::class, 'auditLog'])->name('audit-log');
        Route::get('/pengaturan', [AdminPortalController::class, 'settings'])->name('settings');
        Route::patch('/pengaturan', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::get('/profil', [AdminPortalController::class, 'profile'])->name('profile');
    });

Route::middleware(['auth', 'verified', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::view('/', 'owner.dashboard')->name('dashboard');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
