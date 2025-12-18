<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Periode;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdminRekapController extends Controller
{
    public function index(Request $request)
    {
        // $periodes = Periode::orderBy('tanggal_mulai', 'asc')->get();
        // $periodeId = $request->input('periode_id') ?? $periodes->last()->id;

        // Ambil semua periode berstatus 'selesai' urut terbaru
        $periodes = Periode::where('status', 'selesai')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $defaultPeriode = $periodes->isNotEmpty()
            ? $periodes->first()->id // ambil periode selesai terakhir
            : null;

        $periodeId = $request->input('periode_id') ?? $defaultPeriode;


        $kelompok = Kelompok::with([
            'tugas' => function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId)->with(['progress']);
            }
        ])->get();

        // kumpulkan yang belum selesai
        $rekap = $kelompok
            ->filter(fn($k) => $k->nama_kelompok !== 'Saber Sawah')
            ->map(function ($k) {
                $belum = $k->tugas
                    ->where('is_additional', false)
                    ->filter(function ($tugas) {
                        $progress = $tugas->progress->last();
                        return !$progress || $progress->status !== 'selesai';
                    })
                    ->sortBy('juz')
                    ->map(function ($tugas) {
                        $progress = $tugas->progress->last();

                        // tambahan info progress terakhir
                        $tugas->progress_terakhir = $progress
                            ? "{$progress->nama_surat} ayat {$progress->ayat_sampai}"
                            : null;

                        // kalau sudah ada yg ambil tambahan → diambil_oleh terisi
                        $tugas->sudah_diambil_oleh = $tugas->pengambil?->name ?? null;

                        return $tugas;
                    })
                    ->values();

                return [
                    'nama'  => $k->nama_kelompok,
                    'belum' => $belum,
                ];
            });


        // dd($rekap->belum);
        // total sisa seluruh kelompok
        $totalBelum = $rekap->reduce(function ($carry, $item) {
            return $carry + $item['belum']->count();
        }, 0);

        return view('admin.rekap-belum', compact('rekap', 'periodes', 'periodeId', 'totalBelum'));
    }



    public function ambilTugas(Request $request)
    {
        $request->validate([
            'juz' => 'required|array',
            'periode_id' => 'required|exists:periodes,id',
        ]);

        foreach ($request->juz as $juz) {
            // buat entri tugas baru untuk admin login
            Tugas::create([
                'user_id'    => Auth::id(),   // admin yg ambil
                'periode_id' => $request->periode_id,
                'kelompok_id' => null,        // opsional, bisa null kalau tidak ikut kelompok
                'juz'        => $juz,
            ]);
        }

        return back()->with('success', 'Tugas tambahan berhasil diambil!');
    }
}
