<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\JuzAyat;
use App\Models\Periode;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil semua periode (untuk dropdown)
        $periodes = Periode::orderBy('id')->get();

        // Default: pakai periode terakhir kalau tidak ada filter
        $selectedPeriodeId = $request->get('periode_id', $periodes->last()->id ?? null);

        // Ambil tugas user sesuai periode terpilih
        $tugas = Tugas::with('periode')
            ->where('user_id', $user->id)
            ->where('periode_id', $selectedPeriodeId)
            ->get();

        // Ambil progress sesuai periode
        $progress = Progress::with(['tugas.periode', 'tugas.kelompok'])
            ->where('user_id', $user->id)
            ->whereHas('tugas', function ($q) use ($selectedPeriodeId) {
                $q->where('periode_id', $selectedPeriodeId);
            })
            ->orderBy('id', 'desc')
            ->get();

        // Hitung persentase hafalan
        $totalAyatPerJuz = JuzAyat::select('juz', DB::raw('SUM(ayat_sampai - ayat_dari + 1) as total'))
            ->groupBy('juz')
            ->pluck('total', 'juz');

        foreach ($progress as $p) {
            if (!$p->tugas || !isset($p->tugas->juz)) {
                $p->persen = 0;
                continue;
            }

            $juz = $p->tugas->juz;
            $totalAyatJuz = (int) ($totalAyatPerJuz[$juz] ?? 0);

            $ayatTerkumpul = Progress::where('user_id', $p->user_id)
                ->whereHas('tugas', function ($q) use ($juz, $selectedPeriodeId) {
                    $q->where('juz', $juz)
                        ->where('periode_id', $selectedPeriodeId);
                })
                ->sum(DB::raw('ayat_sampai - ayat_dari + 1'));

            $p->persen = $totalAyatJuz > 0
                ? round(($ayatTerkumpul / $totalAyatJuz) * 100, 1)
                : 0;
        }

        return view('progress.index', compact(
            'progress',
            'user',
            'tugas',
            'periodes',
            'selectedPeriodeId'
        ));
    }


    public function create()
    {
        $user = Auth::user();

        // ambil daftar juz
        $juzAyat = JuzAyat::select('juz')->distinct()->orderBy('juz')->get();

        // ambil tugas user login
        // $tugas = Tugas::with('periode', 'kelompok')
        //     ->where('user_id', $user->id)
        //     ->get();

        // Ambil semua tugas milik user
        $tugas = Tugas::with(['periode', 'kelompok', 'progress'])
        ->where('user_id', $user->id)
        ->get()
        ->filter(function ($t) {
    
            // Ambil progres terakhir
            $lastProgress = $t->progress->last();
    
            // 1️⃣ Jika sudah selesai → jangan tampilkan
            if ($lastProgress && $lastProgress->status === 'selesai') {
                return false;
            }
    
            // 2️⃣ Jika periode ditutup → jangan tampilkan
            if ($t->periode && $t->periode->status === 'selesai') {
    
                // 3️⃣ Kecuali ini tugas tambahan → tampilkan
                if ($t->is_additional ?? false) {
                    return true;
                }
    
                return false;
            }
    
            // Jika belum selesai dan periode masih aktif → tampilkan
            return true;
        });

        // ambil daftar surat & ayat dari tabel juz_ayat
        // $juzAyat = JuzAyat::all();

        return view('progress.create', compact('tugas', 'juzAyat'));
    }

    // public function getSuratByJuz($juz)
    // {
    //     $surat = JuzAyat::where('juz', $juz)
    //         ->select('id as id', 'surat as nama')
    //         ->groupBy('id', 'surat')
    //         ->orderBy('id')
    //         ->get();

    //     return response()->json($surat);
    // }

    public function getSuratByJuz(Request $request, $juz)
    {
        $tugasId = $request->input('tugas_id');

        // surat dari tabel juz_ayat
        $surat = JuzAyat::where('juz', $juz)
            ->select('id', 'surat as nama', 'ayat_dari', 'ayat_sampai')
            ->orderBy('id')
            ->get();

        // ambil progress yang sudah disetor di juz ini
        $setoran = Progress::where('tugas_id', $tugasId)->get(['nama_surat', 'ayat_dari', 'nama_surat', 'ayat_sampai']);

        // kumpulkan surat-ayat yang sudah disetor
        $doneAyat = [];
        foreach ($setoran as $p) {
            if ($p->nama_surat == $p->nama_surat) {
                // dalam 1 surat
                for ($a = $p->ayat_dari; $a <= $p->ayat_sampai; $a++) {
                    $doneAyat[$p->nama_surat][] = $a;
                }
            } else {
                // beberapa surat
                for ($a = $p->ayat_dari; $a <= ($p->ayat_sampai ?? $p->ayat_dari); $a++) {
                    $doneAyat[$p->nama_surat][] = $a;
                }
                $doneAyat[$p->nama_surat][] = $p->ayat_sampai;
            }
        }

        // hanya tampilkan surat yang masih ada ayat kosong
        $filtered = $surat->filter(function ($s) use ($doneAyat) {
            $done = $doneAyat[$s->nama] ?? [];
            $all  = range($s->ayat_dari, $s->ayat_sampai);
            $remaining = array_diff($all, $done);
            return count($remaining) > 0;
        })->values();

        return response()->json($filtered);
    }

    
    public function getAyatBySurat(Request $request, $juz, $id)
    {
        $tugasId = $request->input('tugas_id');

        $data = JuzAyat::where('juz', $juz)
            ->where('id', $id)
            ->first();

        if (!$data) return response()->json([]);

        // ambil progress dari surat ini
        $progress = Progress::where('tugas_id', $tugasId)
            ->where('nama_surat', $data->surat)
            ->get();
            
        //dd($progress);

        $used = [];
        foreach ($progress as $p) {
            for ($a = $p->ayat_dari; $a <= $p->ayat_sampai; $a++) {
                $used[] = $a;
            }
        }

        $all = range($data->ayat_dari, $data->ayat_sampai);
        $remaining = array_values(array_diff($all, $used));

        if (empty($remaining)) {
            return response()->json(['min' => null, 'max' => null, 'kosong' => true]);
        }

        return response()->json([
            'min' => min($remaining),
            'max' => max($remaining),
            'kosong' => false
        ]);
    }


    public function getJumlahAyat($juz, $suratId)
    {
        $jumlahAyat = JuzAyat::where('surat', $suratId)
            ->where('juz', $juz)
            ->max('ayat_sampai');

        return response()->json([
            'jumlah_ayat' => $jumlahAyat ?? 0,
        ]);
    }

    public function hitungAyat(Request $request)
    {
        $juz         = $request->input('juz');
        $suratDari   = (int) $request->input('surat_dari');
        $ayatDari    = (int) $request->input('ayat_dari');
        $suratSampai = (int) $request->input('surat_sampai');
        $ayatSampai  = (int) $request->input('ayat_sampai');

        // ambil jumlah ayat per surat dengan key = id
        $jumlahAyatPerSurat = JuzAyat::select('id', DB::raw('MAX(ayat_sampai) as total_ayat'))
            ->groupBy('id')
            ->pluck('total_ayat', 'id')
            ->toArray();

        $totalAyat = 0;

        if ($suratDari === $suratSampai) {
            // masih dalam satu surat
            $totalAyat = $ayatSampai - $ayatDari + 1;
        } else {
            // surat pertama
            $totalAyat += ($jumlahAyatPerSurat[$suratDari] ?? 0) - $ayatDari + 1;

            // surat di tengah (kalau ada)
            for ($s = $suratDari + 1; $s < $suratSampai; $s++) {
                $totalAyat += $jumlahAyatPerSurat[$s] ?? 0;
            }

            // surat terakhir
            $totalAyat += $ayatSampai;
        }

        return response()->json([
            'total' => $totalAyat,
            'debug' => [
                'juz' => $juz,
                'surat_dari' => $suratDari,
                'ayat_dari' => $ayatDari,
                'surat_sampai' => $suratSampai,
                'ayat_sampai' => $ayatSampai,
            ]
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'tugas_id'              => 'required|exists:tugas,id',
            'nama_surat_dari'      => 'required|integer', // ini adalah id (row id) dari tabel juz_ayat untuk surat awal
            'ayat_dari'            => 'required|integer',
            'nama_surat_sampai'    => 'required|integer', // id (row id) dari tabel juz_ayat untuk surat akhir
            'ayat_sampai'          => 'required|integer',
        ]);

        $userId = auth()->id();
        $tugas  = Tugas::findOrFail($request->tugas_id);
        $juz    = $tugas->juz;

        $suratDariId   = (int) $request->nama_surat_dari;
        $ayatDari      = (int) $request->ayat_dari;
        $suratSampaiId = (int) $request->nama_surat_sampai;
        $ayatSampai    = (int) $request->ayat_sampai;

        // ambil semua baris juz_ayat untuk juz ini, urut berdasarkan id (posisi dalam juz)
        $rows = JuzAyat::where('juz', $juz)->orderBy('id')->get();

        // cari posisi index dari id yang dipilih
        $startIndex = $rows->search(fn($r) => $r->id == $suratDariId);
        $endIndex   = $rows->search(fn($r) => $r->id == $suratSampaiId);

        if ($startIndex === false || $endIndex === false) {
            return back()->with('error', 'Pilihan surat tidak valid untuk juz terpilih.');
        }

        // jika user memilih terbalik (akhir lebih kecil dari awal), kita tukar supaya loop benar
        if ($startIndex > $endIndex) {
            // swap indexes dan juga nilai ayat supaya interpretasi tetap logis
            [$startIndex, $endIndex] = [$endIndex, $startIndex];
            [$suratDariId, $suratSampaiId] = [$suratSampaiId, $suratDariId];
            [$ayatDari, $ayatSampai] = [$ayatSampai, $ayatDari];
        }

        $dataInsert = [];

        for ($i = $startIndex; $i <= $endIndex; $i++) {
            $row = $rows[$i];

            if ($startIndex === $endIndex) {
                // semua dalam 1 surat
                $from = $ayatDari;
                $to   = $ayatSampai;
            } elseif ($i === $startIndex) {
                // surat pertama: dari input sampai akhir surat (nilai ayat_sampai pada row)
                $from = $ayatDari;
                $to   = $row->ayat_sampai;
            } elseif ($i === $endIndex) {
                // surat terakhir: dari awal surat (row->ayat_dari) sampai input akhir
                // biasanya row->ayat_dari biasanya 1, tetapi kita pakai nilai row demi konsistensi
                $from = $row->ayat_dari;
                $to   = $ayatSampai;
            } else {
                // surat-tengah: simpan full surat sesuai range di tabel juz_ayat
                $from = $row->ayat_dari;
                $to   = $row->ayat_sampai;
            }

            $dataInsert[] = [
                'user_id'     => $userId,
                'tugas_id'    => $tugas->id,
                'nama_surat'  => $row->surat,       // simpan nama surat (string)
                'ayat_dari'   => $from,
                'ayat_sampai' => $to,
                'status'      => 'proses',
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // simpan semua
        Progress::insert($dataInsert);

        // recalc status juz (sama seperti yang sudah ada)
        $totalAyatJuz = JuzAyat::where('juz', $juz)
            ->selectRaw('SUM(ayat_sampai - ayat_dari + 1) as total')
            ->value('total');

        $periodeId  = $tugas->periode_id;
        $kelompokId = $tugas->kelompok_id;

        $ayatTerkumpul = Progress::where('user_id', $userId)
            ->whereHas('tugas', function ($q) use ($juz, $periodeId, $kelompokId) {
                $q->where('juz', $juz)
                    ->where('periode_id', $periodeId)
                    ->where('kelompok_id', $kelompokId);
            })
            ->sum(DB::raw('ayat_sampai - ayat_dari + 1'));

        if ($ayatTerkumpul >= $totalAyatJuz) {
            Progress::where('user_id', $userId)
                ->whereHas('tugas', function ($q) use ($juz) {
                    $q->where('juz', $juz);
                })
                ->update(['status' => 'selesai']);
        }

        return redirect()->route('progress.index')->with('success', 'Progress berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $progress = Progress::findOrFail($id);
        $userId   = $progress->user_id;
        $tugasId  = $progress->tugas_id;
        $juz      = $progress->tugas->juz; // ambil juz dari relasi Tugas

        // hapus progress
        $progress->delete();

        // Ambil total ayat di juz ini (dari tabel juz_ayat)
        $totalAyatJuz = JuzAyat::where('juz', $juz)
            ->selectRaw('SUM(ayat_sampai - ayat_dari + 1) as total')
            ->value('total');

        // Hitung ulang ayat yang sudah dibaca user untuk juz ini
        $ayatTerkumpul = Progress::where('user_id', $userId)
            ->whereHas('tugas', function ($q) use ($juz) {
                $q->where('juz', $juz);
            })
            ->sum(DB::raw('ayat_sampai - ayat_dari + 1'));

        // Update status progres juz
        $status = $ayatTerkumpul >= $totalAyatJuz ? 'selesai' : 'proses';

        Progress::where('user_id', $userId)
            ->whereHas('tugas', function ($q) use ($juz) {
                $q->where('juz', $juz);
            })
            ->update(['status' => $status]);

        return redirect()->route('progress.index')
            ->with('success', 'Progress berhasil dihapus dan status diperbarui.');
    }
}
