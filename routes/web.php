<?php

use App\Http\Controllers\ProfileController;
use App\Support\RoleRedirect;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
        Route::view('/dashboard', 'member.dashboard')->name('dashboard');
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
