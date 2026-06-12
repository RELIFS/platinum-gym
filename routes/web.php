<?php

use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicWebsiteController;
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

Route::get('/dashboard', function () {
    return redirect(RoleRedirect::pathFor(request()->user()) ?? route('login'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:member', 'member.profile.complete'])
    ->prefix('member')
    ->name('member.')
    ->group(function () {
        Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [MemberPortalController::class, 'profile'])->name('profile');
        Route::get('/membership', [MemberPortalController::class, 'membership'])->name('membership');
        Route::get('/booking-kelas', [MemberPortalController::class, 'booking'])->name('booking');
        Route::get('/riwayat-booking', [MemberPortalController::class, 'bookings'])->name('bookings');
        Route::get('/transaksi', [MemberPortalController::class, 'transactions'])->name('transactions');
        Route::get('/qr', [MemberPortalController::class, 'qr'])->name('qr');
        Route::get('/notifikasi', [MemberPortalController::class, 'notifications'])->name('notifications');
    });

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
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
