<?php

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Tugas;
use App\Models\JuzAyat;
use App\Models\Periode;
use App\Models\Kelompok;
use App\Models\Progress;
use Illuminate\Http\Request;
use App\Helpers\WhatsappHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ambil semua periode
        $periodes = Periode::orderBy('id', 'desc')->get();

        // ambil periode_id dari request atau default ke periode terakhir
        $periodeId = $request->get('periode_id', $periodes->first()?->id);
        $periodeAktif = $periodes->firstWhere('id', $periodeId);

        // cari tugas user pada periode aktif
        $tugasUser = \App\Models\Tugas::with('kelompok')
            ->where('user_id', $user->id)
            ->where('periode_id', $periodeId)
            ->where('is_additional', false)
            ->first();

        // ambil kelompok dari tugas
        $kelompokUser = $tugasUser?->kelompok;

        // ambil kelompok + tugas sesuai periode
        // $kelompok = Kelompok::with(['tugas' => function ($q) use ($periodeId) {
        //     $q->where('periode_id', $periodeId)->with('progress', 'user', 'periode');
        // }])->get();

        // ambil kelompok + hanya tugas utama sesuai periode
        $kelompok = Kelompok::with(['tugas' => function ($q) use ($periodeId) {
            $q->where('periode_id', $periodeId)
                ->where('is_additional', false) // ⬅️ filter hanya tugas utama
                ->with('progress', 'user', 'periode');
        }])->get();

        $kelompok = $kelompok->map(function ($item) {
            // ambil hanya tugas milik anggota aktif
            $tugasAktif = $item->tugas->filter(function ($tugas) {
                return optional($tugas->user)->status !== 'nonaktif'; 
                // ganti 'anggota' sesuai relasi yang kamu gunakan, misalnya 'siswa' atau 'user'
            });
        
            // total juz hanya dari anggota aktif
            $totalJuz = $tugasAktif->count();
        
            // hitung juz selesai juga hanya dari anggota aktif
            $juzSelesai = $tugasAktif->filter(function ($tugas) {
                $progress = $tugas->progress->last();
                return $progress && $progress->status === 'selesai';
            })->count();
        
            // hitung persentase
            $persen = $totalJuz > 0 ? round(($juzSelesai / $totalJuz) * 100, 2) : 0;
        
            // tambahkan ke item
            $item->total_juz = $totalJuz;
            $item->juz_selesai = $juzSelesai;
            $item->persen = $persen;
        
            return $item;
        });


        // Hitung total keseluruhan
        $totalJuzAll = $kelompok->sum('total_juz');
        $juzSelesaiAll = $kelompok->sum('juz_selesai');
        $persenAll = $totalJuzAll > 0 ? round(($juzSelesaiAll / $totalJuzAll) * 100, 2) : 0;

        return view('admin.dashboard', compact(
            'kelompok',
            'periodes',
            'kelompokUser',
            'tugasUser',
            'periodeId',
            'totalJuzAll',
            'juzSelesaiAll',
            'persenAll',
            'periodeAktif'
        ));
    }

    public function tugasKelompok(Request $request)
    {
        $user = Auth::user();

        // ambil semua periode
        $periodes = Periode::orderBy('id', 'desc')->get();
        $semuaKelompok = Kelompok::all(); // ← Tambahan ini

        // ambil periode_id dari request atau default ke periode terakhir
        $periodeId = $request->get('periode_id', $periodes->first()?->id);
        $periodeAktif = $periodes->firstWhere('id', $periodeId);

        // cari tugas user pada periode aktif
        $tugasUser = \App\Models\Tugas::with('kelompok')
            ->where('user_id', $user->id)
            ->where('periode_id', $periodeId)
            ->where('is_additional', false)
            ->orderBy('juz', 'asc')
            ->first();

        // ambil kelompok dari tugas
        $kelompokUser = $tugasUser?->kelompok;

        // ambil kelompok + tugas sesuai periode
        // $kelompok = Kelompok::with(['tugas' => function ($q) use ($periodeId) {
        //     $q->where('periode_id', $periodeId)->with('progress', 'user', 'periode');
        // }])->get();

        // ambil kelompok + hanya tugas utama sesuai periode
        $kelompok = Kelompok::with(['tugas' => function ($q) use ($periodeId) {
            $q->where('periode_id', $periodeId)
                ->where('is_additional', false) // ⬅️ filter hanya tugas utama
                ->with('progress', 'user', 'periode')
                ->orderBy('juz', 'asc'); // ⬅️ urutkan berdasarkan JUZ
        }])->get();

        $kelompok = $kelompok->map(function ($item) {
            // ambil hanya tugas milik anggota aktif
            $tugasAktif = $item->tugas->filter(function ($tugas) {
                return optional($tugas->user)->status !== 'nonaktif'; 
                // ganti 'anggota' sesuai relasi yang kamu gunakan, misalnya 'siswa' atau 'user'
            });
        
            // total juz hanya dari anggota aktif
            $totalJuz = $tugasAktif->count();
        
            // hitung juz selesai juga hanya dari anggota aktif
            $juzSelesai = $tugasAktif->filter(function ($tugas) {
                $progress = $tugas->progress->last();
                return $progress && $progress->status === 'selesai';
            })->count();
        
            // hitung persentase
            $persen = $totalJuz > 0 ? round(($juzSelesai / $totalJuz) * 100, 2) : 0;
        
            // tambahkan ke item
            $item->total_juz = $totalJuz;
            $item->juz_selesai = $juzSelesai;
            $item->persen = $persen;
        
            return $item;
        });

        // Hitung total keseluruhan
        $totalJuzAll = $kelompok->sum('total_juz');
        $juzSelesaiAll = $kelompok->sum('juz_selesai');
        $persenAll = $totalJuzAll > 0 ? round(($juzSelesaiAll / $totalJuzAll) * 100, 2) : 0;

        return view('admin.tugas', compact(
            'kelompok',
            'periodes',
            'kelompokUser',
            'tugasUser',
            'periodeId',
            'totalJuzAll',
            'juzSelesaiAll',
            'persenAll',
            'periodeAktif',
            'semuaKelompok'
        ));
    }



    // app/Http/Controllers/DashboardController.php

    public function generatePeriode(Request $request)
    {
    
        // 1. Buat periode baru
        $today = Carbon::now();
        $tanggalMulai  = $today->startOfWeek(Carbon::MONDAY);
        $tanggalSelesai = (clone $tanggalMulai)->endOfWeek(Carbon::SUNDAY);
    
        $lastPeriode = \App\Models\Periode::orderBy('nomor_pekan', 'desc')->first();
        $nextNumber = $lastPeriode ? $lastPeriode->nomor_pekan + 1 : 30;
    
        $periodeBaru = \App\Models\Periode::create([
            'nomor_pekan'   => $nextNumber,
            'nama_periode'  => 'Pekan ' . $nextNumber,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ]);
    
        // 2. Ambil user aktif dan izin saja
        $anggota = \App\Models\User::whereIn('status', ['aktif', 'izin', 'nonaktif'])->get();
    
        // 3. Jika belum ada tugas sama sekali (periode pertama)
        $adaTugasSebelumnya = \App\Models\Tugas::exists();
    
        if (!$adaTugasSebelumnya) {
            // Hitung jumlah kelompok (30 orang per kelompok)
            $jumlahAnggota = $anggota->count();
            $jumlahKelompokPenuh = intdiv($jumlahAnggota, 30);
            $sisa = $jumlahAnggota % 30;
            $totalKelompok = $jumlahKelompokPenuh + ($sisa > 0 ? 1 : 0);
    
            $kelompokIds = [];
    
            // Buat kelompok otomatis
            for ($i = 1; $i <= $totalKelompok; $i++) {
                $kelompok = \App\Models\Kelompok::firstOrCreate(['nama_kelompok' => 'Kelompok ' . $i]);
                $kelompokIds[] = $kelompok->id;
            }
    
            $jmlKelompok = count($kelompokIds);
    
            // Loop semua anggota dan buat tugas pertama
            foreach ($anggota as $i => $user) {
                // Tentukan kelompok berdasarkan urutan
                $kelompokIndex = intdiv($i, 30);
                if ($kelompokIndex >= $jmlKelompok) $kelompokIndex = $jmlKelompok - 1;
                $kelompokId = $kelompokIds[$kelompokIndex];
    
                // Tentukan juz (1–30 berulang)
                $juz = ($i % 30) + 1;
    
                \App\Models\Tugas::create([
                    'user_id'     => $user->id,
                    'kelompok_id' => $kelompokId,
                    'periode_id'  => $periodeBaru->id,
                    'juz'         => $juz,
                ]);
            }
        } 
        else {
            // Jika sudah ada tugas sebelumnya → lanjutkan seperti biasa
            foreach ($anggota as $user) {
                $lastTask = \App\Models\Tugas::where('user_id', $user->id)
                    ->where('is_additional',false)
                    ->latest('id')->first();
    
                if (!$lastTask || !$lastTask->kelompok_id) continue;
    
                $kelompokId = $lastTask->kelompok_id;
                $juz = $lastTask->juz ? $lastTask->juz + 1 : 1;
                if ($juz > 30) $juz = 1;
    
                \App\Models\Tugas::create([
                    'user_id'     => $user->id,
                    'kelompok_id' => $kelompokId,
                    'periode_id'  => $periodeBaru->id,
                    'juz'         => $juz,
                ]);
            }
        }
    
        // 4. Jika centang WA aktif, kirim notif
        if ($request->has('kirim_wa')) {
            $pesan = "📢 Periode baru telah dibuat!\n\n" .
                "Nama Periode: {$periodeBaru->nama_periode}\n" .
                "Tanggal: {$periodeBaru->tanggal_mulai->format('d M Y')} - {$periodeBaru->tanggal_selesai->format('d M Y')}\n\n" .
                "Silakan cek aplikasi untuk detail pembagian tugas.\n\n" .
                "https://ikan-belida.dedipartijo.biz.id";
    
            \App\Helpers\WhatsappHelper::send(env('WA_GROUP'), $pesan);
        }
    
        return back()->with('success', 'Periode baru berhasil dibuat! Tugas dibentuk otomatis untuk seluruh anggota aktif & izin.');
    }


    public function getAnggotaByKelompok($id, $periode_id)
    {
        $anggota = \App\Models\Tugas::with('user')
            ->where('kelompok_id', $id)
            ->where('periode_id', $periode_id) // 🔥 hanya ambil dari periode aktif
            ->latest('id')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'nama' => $t->user->name,
                    'juz' => $t->juz,
                ];
            });

        return response()->json($anggota);
    }
}
