<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tugas;
use App\Models\Periode;
use App\Models\Kelompok;
use Illuminate\Http\Request;

class KelompokController extends Controller
{
    public function index()
    {
        $kelompok = Kelompok::withCount('users')->get();
        return view('kelompok.index', compact('kelompok'));
    }

    public function create()
    {
        return view('kelompok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelompok' => 'required|string|max:255'
        ]);

        Kelompok::create($request->all());

        return redirect()->route('kelompok.index')->with('success', 'Kelompok berhasil ditambahkan');
    }

    public function show(Kelompok $kelompok)
    {
        $periodeTerakhir = \App\Models\Periode::latest('id')->first();

        // ambil user unik berdasarkan tugas di periode terakhir
        $users = \App\Models\User::whereHas('tugas', function ($q) use ($kelompok, $periodeTerakhir) {
            $q->where('kelompok_id', $kelompok->id)
                ->where('periode_id', $periodeTerakhir->id);
        })->get();

        $kelompokLain = Kelompok::where('id', '!=', $kelompok->id)->get();

        return view('kelompok.show', compact('kelompok', 'users', 'kelompokLain'));
    }


    public function edit(Kelompok $kelompok)
    {
        return view('kelompok.edit', compact('kelompok'));
    }

    public function update(Request $request, Kelompok $kelompok)
    {
        $request->validate([
            'nama_kelompok' => 'required|string|max:255'
        ]);

        $kelompok->update($request->all());

        return redirect()->route('kelompok.index')->with('success', 'Kelompok berhasil diperbarui');
    }

    public function destroy(Kelompok $kelompok)
    {
        $kelompok->delete();
        return redirect()->route('kelompok.index')->with('success', 'Kelompok berhasil dihapus');
    }

    // app/Http/Controllers/KelompokController.php

    public function pindahAnggota(Request $request, $tugasId)
    {
        $request->validate([
            'kelompok_id' => 'required|exists:kelompok,id',
        ]);

        $tugas = Tugas::findOrFail($tugasId);
        $tugas->kelompok_id = $request->kelompok_id;
        $tugas->save();

        return back()->with('success', 'Anggota berhasil dipindahkan ke kelompok baru.');
    }
    
    public function isiJuz(Request $request, $kelompokId)
    {
        $request->validate([
            'tugas_id' => 'required|exists:tugas,id',
            'juz' => 'required|integer|min:1|max:30',
        ]);

        $tugasAsal = \App\Models\Tugas::findOrFail($request->tugas_id);

        // duplikat ke kelompok baru dengan juz sesuai kosong
        \App\Models\Tugas::create([
            'user_id'     => $tugasAsal->user_id,
            'kelompok_id' => $kelompokId,
            'periode_id'  => $tugasAsal->periode_id, // tetap periode aktif
            'juz'         => $request->juz, // ganti ke juz kosong
        ]);

        // opsional: hapus tugas lama biar tidak double
        $tugasAsal->delete();

        return back()->with('success', 'Anggota berhasil dipindahkan ke kelompok baru pada juz ' . $request->juz);
    }
}
