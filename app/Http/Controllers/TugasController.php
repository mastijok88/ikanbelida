<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tugas;
use App\Models\Periode;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TugasController extends Controller
{
    public function index()
    {
        $tugas = Tugas::with(['user', 'kelompok', 'periode'])->get();
        return view('tugas.index', compact('tugas'));
    }

    public function create()
    {
        $kelompok = Kelompok::all();
        $periode = Periode::all();
        $users = User::all();
        return view('tugas.create', compact('kelompok', 'periode', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'kelompok_id' => 'required|exists:kelompok,id',
            'user_id' => 'required|exists:users,id',
            'juz' => 'required|integer|min:1|max:30',
        ]);

        Tugas::create($request->all());



        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit(Tugas $tugas)
    {
        $kelompok = Kelompok::all();
        $periode = Periode::all();
        $users = User::all();
        return view('tugas.edit', compact('tugas', 'kelompok', 'periode', 'users'));
    }

    public function update(Request $request, Tugas $tugas)
    {
        $request->validate([
            'periode_id' => 'required|exists:periode,id',
            'kelompok_id' => 'required|exists:kelompok,id',
            'user_id' => 'required|exists:users,id',
            'juz' => 'required|integer|min:1|max:30',
        ]);

        $tugas->update($request->all());

        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil diupdate.');
    }

    public function destroy(Tugas $tugas)
    {
        $tugas->delete();
        return redirect()->route('tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function generate($periodeId)
    {
        $periode = Periode::findOrFail($periodeId);

        // cari periode sebelumnya (langsung sebelum periode ini)
        $previous = Periode::where('id', '<', $periode->id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$previous) {
            return back()->with('error', 'Periode sebelumnya tidak ditemukan.');
        }

        // ambil semua tugas terakhir di periode sebelumnya
        $tugasSebelumnya = Tugas::where('periode_id', $previous->id)
            ->with('user')
            ->get();

        foreach ($tugasSebelumnya as $tugas) {
            $nextJuz = $tugas->juz + 1;
            if ($nextJuz > 30) {
                $nextJuz = 1;
            }

            Tugas::create([
                'user_id'    => $tugas->user_id,
                'kelompok_id' => $tugas->kelompok_id,
                'periode_id' => $periode->id,
                'juz'        => $nextJuz,
                'status'     => 'belum',
            ]);
        }

        return back()->with('success', 'Tugas otomatis berhasil dibuat.');
    }

    public function generateTugasPeriodeBaru()
    {
        $periodeBaru = Periode::create([
            'nama_periode' => 'Pekan ' . (Periode::count() + 1),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addWeek()
        ]);

        $tugasLama = Tugas::with('user')->where('periode_id', Periode::latest('id')->skip(1)->first()->id ?? null)->get();

        foreach ($tugasLama as $tugas) {
            $juzBaru = $tugas->juz + 1;
            if ($juzBaru > 30) {
                $juzBaru = 1;
            }

            Tugas::create([
                'user_id' => $tugas->user_id,
                'kelompok_id' => $tugas->kelompok_id,
                'periode_id' => $periodeBaru->id,
                'juz' => $juzBaru,
                'is_additional' => false,
            ]);
        }

        return back()->with('success', 'Tugas periode baru berhasil dibuat');
    }

    public function ambilTambahan(Request $request, $periodeId, $juz, $tugasId, $kelompokId)
    {


        $user = Auth::user();
        // $kelompokId = $request->kelompok_id;
        // $periodeId  = $periodeId;
        // $juz = $juz;

        // cari juz terakhir dari tugas reguler + tambahan
        // $lastTask = Tugas::where('user_id', $user->id)
        //     ->where('kelompok_id', $kelompokId)
        //     ->where('periode_id', $periodeId)
        //     ->latest('id')
        //     ->first();

        // tentukan juz berikutnya
        // $juz = $lastTask ? $lastTask->juz + 1 : 1;
        // if ($juz > 30) $juz = 1;

        // 1. Update tugas asal → tandai sudah diambil
        $tugasAsal = Tugas::findOrFail($tugasId);
        $tugasAsal->update([
            'diambil_oleh' => $user->id,
        ]);

        //dd($juz);
        // 4. Buat tugas tambahan
        Tugas::create([
            'user_id'       => $user->id,
            'periode_id'    => $periodeId,
            'kelompok_id'   => $kelompokId,
            'juz'           => $juz,
            'is_additional' => true,
            'diambil_oleh'  => null,
        ]);

        return back()->with('success', "Juz $juz berhasil diambil alih oleh {$user->name}");
    }

    public function batal($id)
    {
        $tugasLama = Tugas::findOrFail($id);

        //if (!$tugasBaru->is_additional || $tugasBaru->user_id != auth()->id()) {
        //    return back()->with('error', 'Anda tidak berhak membatalkan tugas ini.');
        //}

        // Cari tugas lama berdasarkan periode, kelompok, dan juz
        $tugasBaru = Tugas::where('periode_id', $tugasLama->periode_id)
            ->where('kelompok_id', $tugasLama->kelompok_id)
            ->where('juz', $tugasLama->juz)
            ->where('is_additional', true)
            ->first();

        if ($tugasLama) {
            $tugasLama->update([
                'diambil_oleh' => null,
            ]);
        }

        // Hapus record tambahan
        $tugasBaru->delete();

        return back()->with('success', 'Tugas tambahan berhasil dibatalkan.');
    }
}
