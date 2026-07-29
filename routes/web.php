<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TukarFakturAdminController;
use App\Http\Controllers\Admin\PerusahaanController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
}); 

//public Route 
Route::middleware(['wednesday.office'])->group(function () {

    Route::get('/kontrabon', [TukarFakturController::class, 'create']);
    Route::post('/kontrabon', [TukarFakturController::class, 'store']);
    Route::get('/kontrabon/success', [TukarFakturController::class, 'success']);

}); 

// routes/web.php
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    Route::get('/tukar-faktur', [TukarFakturAdminController::class, 'index'])->name('admin.tukar-faktur.index');

    // Harus di atas route /{id} supaya "export" tidak dianggap sebagai id.
    Route::get('/tukar-faktur/export', [TukarFakturAdminController::class, 'export'])->name('admin.tukar-faktur.export');

    Route::get('/tukar-faktur/{id}', [TukarFakturAdminController::class, 'show'])->name('admin.tukar-faktur.show');
 
    Route::post('/admin/tukar-faktur/{id}/payment-date', [TukarFakturAdminController::class, 'updatePaymentDate'])->name('admin.tukar-faktur.payment-date');

    Route::delete('/admin/tukar-faktur/{id}', [TukarFakturAdminController::class, 'destroy'])->name('admin.tukar-faktur.destroy');

    Route::put('/admin/tukar-faktur/{id}',[TukarFakturAdminController::class, 'update'])->name('admin.tukar-faktur.update');

    // Master data perusahaan
    Route::resource('perusahaan', PerusahaanController::class)
        ->names('admin.perusahaan')
        ->parameters(['perusahaan' => 'perusahaan']);
});


require __DIR__.'/auth.php';
