<?php
use App\Http\Controllers\BarangController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('barang.index'));

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard redirect
    Route::get('/dashboard', fn () => redirect()->route('barang.index'))
        ->name('dashboard');

    // ── Barang CRUD ──────────────────────────────────────────────────────────
    Route::resource('barang', BarangController::class);

    // ── Download gambar ──────────────────────────────────────────────────────
    Route::get('barang/{barang}/download', [BarangController::class, 'download'])
        ->name('barang.download');

    // ── Steganography ────────────────────────────────────────────────────────
    Route::post('barang/{barang}/encode', [BarangController::class, 'encodeLSB'])
        ->name('barang.encode');

    Route::get('barang/{barang}/decode', [BarangController::class, 'decodeLSB'])
        ->name('barang.decode');
});

require __DIR__.'/auth.php';
