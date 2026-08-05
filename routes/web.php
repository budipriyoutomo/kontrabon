<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TukarFakturAdminController;
use App\Http\Controllers\Admin\PerusahaanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\PerusahaanSearchController;
use App\Http\Controllers\TukarFakturController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
}); 

// Sumber data dropdown supplier. Dipakai form publik (tanpa login) dan
// halaman admin, jadi dibatasi throttle dan minimal 2 karakter.
Route::get('/perusahaan/search', PerusahaanSearchController::class)
    ->middleware('throttle:60,1')
    ->name('perusahaan.search');

//public Route
Route::middleware(['wednesday.office'])->group(function () {

    Route::get('/kontrabon', [TukarFakturController::class, 'create']);
    Route::post('/kontrabon', [TukarFakturController::class, 'store']);
    Route::get('/kontrabon/success', [TukarFakturController::class, 'success']);

}); 

// routes/web.php
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // --- Baca data: semua peran ---------------------------------------
    Route::middleware('role:kontrabon,verifikator,billing')->group(function () {

        Route::get('/tukar-faktur', [TukarFakturAdminController::class, 'index'])->name('admin.tukar-faktur.index');

        // Harus di atas route /{id} supaya "export" tidak dianggap sebagai id.
        Route::get('/tukar-faktur/export', [TukarFakturAdminController::class, 'export'])->name('admin.tukar-faktur.export');

        Route::get('/tukar-faktur/{id}', [TukarFakturAdminController::class, 'show'])->name('admin.tukar-faktur.show');
    });

    // --- Ubah data tukar faktur: kontrabon ----------------------------
    // Pengisian tanggal pembayaran memicu email ke supplier.
    Route::middleware('role:kontrabon')->group(function () {

        Route::post('/admin/tukar-faktur/{id}/payment-date', [TukarFakturAdminController::class, 'updatePaymentDate'])->name('admin.tukar-faktur.payment-date');

        // Kirim ulang bukti yang sudah pernah terkirim; tidak mengubah status.
        Route::post('/tukar-faktur/{id}/resend-email', [TukarFakturAdminController::class, 'resendEmail'])
            ->name('admin.tukar-faktur.resend-email');

        Route::delete('/admin/tukar-faktur/{id}', [TukarFakturAdminController::class, 'destroy'])->name('admin.tukar-faktur.destroy');

        Route::put('/admin/tukar-faktur/{id}',[TukarFakturAdminController::class, 'update'])->name('admin.tukar-faktur.update');

        // Master data perusahaan
        Route::resource('perusahaan', PerusahaanController::class)
            ->names('admin.perusahaan')
            ->parameters(['perusahaan' => 'perusahaan']);
    });

    // --- Verifikasi: verifikator --------------------------------------
    // Yang diverifikasi adalah data yang emailnya sudah terkirim.
    Route::middleware('role:verifikator')->group(function () {

        Route::get('/verifikasi', [VerifikasiController::class, 'index'])
            ->name('admin.verifikasi.index');

        Route::post('/verifikasi/bulk', [VerifikasiController::class, 'bulkVerify'])
            ->name('admin.verifikasi.bulk');

        Route::post('/verifikasi/{id}', [VerifikasiController::class, 'verify'])
            ->name('admin.verifikasi.verify');
    });

    // --- Manajemen pengguna: admin saja -------------------------------
    Route::middleware('role:admin')->group(function () {

        Route::resource('users', UserController::class)
            ->except('show')
            ->names('admin.users');

        Route::put('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('admin.users.toggle-active');

        Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('admin.users.reset-password');
    });
});


// --- Billing --------------------------------------------------------------
// Sumber datanya hanya pengajuan yang sudah lolos verifikasi.
Route::middleware(['auth', 'role:billing'])->prefix('billing')->group(function () {

    Route::get('/', [BillingController::class, 'index'])->name('billing.index');

    Route::get('/rekap', [BillingController::class, 'rekap'])->name('billing.rekap');

    Route::get('/export/csv', [BillingController::class, 'exportCsv'])->name('billing.export.csv');

    Route::get('/export/pdf', [BillingController::class, 'exportPdf'])->name('billing.export.pdf');

    Route::post('/proses-massal', [BillingController::class, 'prosesMassal'])->name('billing.proses-massal');

    Route::post('/{id}/proses', [BillingController::class, 'proses'])->name('billing.proses');
});


require __DIR__.'/auth.php';
