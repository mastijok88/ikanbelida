@extends('layouts.app')

@section('content')
<div class="container">
    @if(auth()->user()->role === 'super_admin')
    <h3>Import Users</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('import.users.post') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Upload File Excel</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button class="btn btn-primary">Import</button>
    </form>

    @endif
    
    <h2 class="mb-4">Daftar Anggota</h2>
    <p>Total: <strong>{{ $users->total() }}</strong> anggota</p> {{-- total anggota --}}

    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">+ Tambah Anggota</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Form Pencarian -->
    <form method="GET" action="{{ route('users.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau no HP" value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Cari</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                {{-- <th>Email</th> --}}
                <th>No HP</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $index => $user)
                <tr>
                    <td>{{ $users->firstItem() + $index }}</td>
                    <td>{{ $user->name }}</td>
                    {{-- <td>{{ $user->email }}</td> --}}
                    <td>{{ $user->no_hp }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-list"></i> {{-- ikon burger pakai Bootstrap Icons --}}
                            </button>
                            <ul class="dropdown-menu">
                                <!-- Edit -->
                                <li>
                                    <a href="{{ route('users.edit', $user->id) }}" class="dropdown-item">
                                        ✏️ Edit
                                    </a>
                                </li>

                                <!-- Hapus -->
                                <li>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            🗑 Hapus
                                        </button>
                                    </form>
                                </li>

                                <!-- Jadikan Admin -->
                                @if(auth()->user()->role === 'admin' && $user->role !== 'admin')
                                    <li>
                                        <form action="{{ route('users.makeAdmin', $user->id) }}" method="POST" onsubmit="return confirm('Jadikan admin?')">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                ⭐ Jadikan Admin
                                            </button>
                                        </form>
                                    </li>
                                @endif

                                <!-- Tambahkan Tugas -->
                                @php
                                    $periodeAktif = \App\Models\Periode::latest()->first();
                                    $sudahPunyaTugas = $periodeAktif
                                        ? \App\Models\Tugas::where('user_id', $user->id)
                                            ->where('periode_id', $periodeAktif->id)
                                            ->exists()
                                        : false;
                                @endphp

                                @if(!$sudahPunyaTugas)
                                    <li>
                                        <form action="{{ route('users.addTask', $user->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                📖 Tambahkan Tugas
                                            </button>
                                        </form>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $users->links() }}
    </div>
</div>
@endsection
