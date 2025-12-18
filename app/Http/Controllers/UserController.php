<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tugas;
use App\Models\Periode;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Fitur pencarian
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            });
        }

        // Pagination 10 data per halaman
        $users = $query->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $kelompok = Kelompok::all();
        return view('users.create', compact('kelompok'));
    }

    public function show()
    {
        // $kelompok = Kelompok::all();
        // return view('users.create', compact('kelompok'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'no_hp' => 'required|unique:users',
        ]);

        User::create([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'password' => Hash::make('password'),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $kelompok = Kelompok::all();
        return view('users.edit', compact('user', 'kelompok'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'no_hp' => 'required|unique:users,no_hp,' . $user->id,
            'role' => 'required',
            // tambahkan validasi status hanya untuk super_admin
            'status' => auth()->user()->role === 'super_admin' ? 'required|in:aktif,izin,nonaktif' : '',
        ]);

        $user->update([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'role' => $request->role,
        ]);

        // jika super_admin, update juga status user
        if (auth()->user()->role === 'super_admin') {
            $user->update([
                'status' => $request->status,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function makeAdmin(User $user)
    {
        // hanya admin boleh akses
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Tidak diizinkan.');
        }

        $user->update([
            'role' => 'admin'
        ]);

        return back()->with('success', "{$user->name} sekarang menjadi admin.");
    }

    public function addTask(User $user)
    {
        // cari periode aktif
        $periode = Periode::latest()->first();
        if (!$periode) {
            return back()->with('error', 'Belum ada periode aktif.');
        }
    
        // cek apakah user sudah punya tugas di periode ini
        $cek = Tugas::where('user_id', $user->id)
            ->where('periode_id', $periode->id)
            ->first();
        if ($cek) {
            return back()->with('warning', 'User ini sudah punya tugas di periode ini.');
        }
    
        // ambil semua kelompok urut berdasarkan ID (kelompok 1 dulu)
        $semuaKelompok = Kelompok::orderBy('id')->get();
    
        $kelompokTerpilih = null;
        $juzKosong = null;
    
        foreach ($semuaKelompok as $kelompok) {
            // ambil semua juz yang sudah dipakai di kelompok ini pada periode aktif
            $juzDipakai = Tugas::where('kelompok_id', $kelompok->id)
                ->where('periode_id', $periode->id)
                ->pluck('juz')
                ->toArray();
    
            // cari juz kosong dari 1 sampai 30
            for ($j = 1; $j <= 30; $j++) {
                if (!in_array($j, $juzDipakai)) {
                    $kelompokTerpilih = $kelompok;
                    $juzKosong = $j;
                    break 2; // keluar dari kedua loop
                }
            }
        }
    
        // jika semua kelompok sudah penuh
        if (!$kelompokTerpilih || !$juzKosong) {
            return back()->with('warning', 'Semua kelompok sudah memiliki juz lengkap (1–30).');
        }
    
        // buat tugas baru
        Tugas::create([
            'user_id'     => $user->id,
            'kelompok_id' => $kelompokTerpilih->id,
            'periode_id'  => $periode->id,
            'juz'         => $juzKosong,
        ]);
    
        return back()->with('success', "Tugas untuk {$user->name} berhasil ditambahkan ke {$kelompokTerpilih->nama_kelompok} (Juz {$juzKosong}).");
    }

}
