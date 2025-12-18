<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\User;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PeriodeController extends Controller
{
    public function index()
    {
        $periodes = Periode::orderBy('nomor_pekan', 'desc')->get();
        return view('periode.index', compact('periodes'));
    }

    public function generate(Request $request)
    {
        $today = Carbon::now();
        $tanggalMulai  = $today->startOfWeek(Carbon::MONDAY);
        $tanggalSelesai = (clone $tanggalMulai)->endOfWeek(Carbon::SUNDAY);

        $lastPeriode = Periode::orderBy('nomor_pekan', 'desc')->first();
        $nextNumber = $lastPeriode ? $lastPeriode->nomor_pekan + 1 : 1;

        $periodeBaru = Periode::create([
            'nomor_pekan'   => $nextNumber,
            'nama_periode'  => 'Pekan ' . $nextNumber,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status'        => 'aktif',
        ]);

        return back()->with('success', "Periode {$periodeBaru->nama_periode} berhasil dibuat!");
    }

    public function tutup(Periode $periode)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Akses ditolak.');
        }

        $periode->update(['status' => 'selesai']);

        return back()->with('success', "Periode {$periode->nama_periode} berhasil ditutup.");
    }
    
    public function buka($id)
    {
        $periode = Periode::findOrFail($id);
        $periode->update(['status' => 'aktif']);
        return back()->with('success', 'Periode berhasil dibuka kembali.');
    }

    public function show(Periode $periode)
    {
        $tugas = Tugas::with(['user', 'kelompok'])
            ->where('periode_id', $periode->id)
            ->orderBy('kelompok_id')
            ->get();

        return view('periode.show', compact('periode', 'tugas'));
    }
    
    public function destroy($id)
    {
        $periode = Periode::find($id);
    
        if (!$periode) {
            return back()->with('error', 'Periode tidak ditemukan.');
        }
    
        try {
            // Ambil semua tugas pada periode ini
            $tugas = \App\Models\Tugas::where('periode_id', $periode->id)->get();
    
            foreach ($tugas as $t) {
                // Hapus semua progress terkait tugas ini
                \App\Models\Progress::where('tugas_id', $t->id)->delete();
                // Hapus tugasnya
                $t->delete();
            }
    
            // Hapus periodenya
            $periode->delete();
    
            return back()->with('success', 'Periode dan seluruh data terkait berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus periode: ' . $e->getMessage());
        }
    }

}
