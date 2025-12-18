<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\KelompokController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminRekapController;

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

route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard umum (bisa diakses admin & anggota)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');


Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');





// dashboard admin
Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    // Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::post('/users/{user}/make-admin', [UserController::class, 'makeAdmin'])
        ->name('users.makeAdmin');

    Route::resource('tugas', TugasController::class);
    Route::resource('users', UserController::class);
    Route::resource('periode', PeriodeController::class);
    Route::resource('kelompok', KelompokController::class);

    // khusus pindah anggota
    Route::post('/kelompok/pindah/{user}', [KelompokController::class, 'pindahAnggota'])->name('kelompok.pindah');
    Route::post('/users/{user}/add-task', [UserController::class, 'addTask'])->name('users.addTask');

    Route::get('/rekap-belum', [AdminRekapController::class, 'index'])->name('rekap.belum');
    Route::post('/rekap/ambil-tugas', [AdminRekapController::class, 'ambilTugas'])->name('rekap.ambilTugas');
    Route::post('/tugas/ambil/{periode}/{juz}/{tugasId}/{kelompokId}', [TugasController::class, 'ambilTambahan'])
        ->name('tugas.ambil');
    Route::delete('/tugas/{id}/batal', [TugasController::class, 'batal'])->name('tugas.batal');

    // Route::get('/laporan/{periodeId}', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
    // routes/web.php
    Route::get('/laporan/{periodeId?}', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');

    Route::post('/laporan/{periodeId}/kirim', [App\Http\Controllers\LaporanController::class, 'kirim'])->name('laporan.kirim');
});

Route::middleware(['auth'])->group(function () {

    Route::resource('progress', ProgressController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::get('/progress/create', [ProgressController::class, 'create'])->name('progress.create');
    Route::get('/get-surat/{juz}', [ProgressController::class, 'getSuratByJuz']);
    Route::get('/get-ayat/{juz}/{surat}', [ProgressController::class, 'getAyatBySurat']);
    Route::get('/tugas/generate/{periode}', [TugasController::class, 'generate'])->name('tugas.generate');
    Route::get('/hitung-ayat', [ProgressController::class, 'hitungAyat'])->name('progress.hitungAyat');
    Route::get('/get-jumlah-ayat/{surat_id}', [ProgressController::class, 'getJumlahAyat']);


    Route::get('/profil', [App\Http\Controllers\ProfilController::class, 'index'])->name('profil.index');
    Route::post('/profil/password', [App\Http\Controllers\ProfilController::class, 'updatePassword'])->name('profil.updatePassword');

    Route::get('/tugas-kelompok', [DashboardController::class, 'tugasKelompok'])->name('tugas.kelompok');
    Route::get('/api/ayat-by-surat/{juz}/{surat}', [ProgressController::class, 'getAyatBySurat']);
});

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::post('/generate-periode', [DashboardController::class, 'generatePeriode'])
        ->name('admin.generate.periode')
        ->middleware('auth');

    Route::get('/import-users', [ImportController::class, 'index'])->name('import.users');
    Route::post('/import-users', [ImportController::class, 'import'])->name('import.users.post');
    Route::get('/kelompok/{id}/anggota', [KelompokController::class, 'anggota'])->name('kelompok.anggota');
    Route::post('/kelompok/pindahkan/{user}', [KelompokController::class, 'pindahkanAnggota'])->name('kelompok.pindahkan');

    Route::post('/kelompok/refresh-anggota', [App\Http\Controllers\KelompokController::class, 'refreshAnggota'])
        ->name('kelompok.refreshAnggota');

    Route::post('/kelompok/{kelompok}/isi-juz/{juz}', [KelompokController::class, 'isiJuz'])->name('kelompok.isiJuz');
    Route::get('/kelompok/{id}/anggota-by-kelompok/{periode_id}', [DashboardController::class, 'getAnggotaByKelompok'])->name('kelompok.getAnggotaByKelompok');
    Route::post('/kelompok/{kelompok}/isi-juz', [KelompokController::class, 'isiJuz'])->name('kelompok.isiJuz');

    Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
    // Route::post('/periode/generate', [PeriodeController::class, 'generate'])->name('periode.generate');
    Route::put('/periode/{periode}/tutup', [PeriodeController::class, 'tutup'])->name('periode.tutup');
    Route::put('/periode/{id}/buka', [PeriodeController::class, 'buka'])->name('periode.buka');
    // Route::get('/periode/{periode}', [PeriodeController::class, 'show'])->name('periode.show');
    
    Route::delete('/periode/{id}/hapus', [PeriodeController::class, 'destroy'])->name('periode.destroy');

});
