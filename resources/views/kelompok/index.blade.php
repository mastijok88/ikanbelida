@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Daftar Kelompok</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Kelompok</th>
                        <th>Jumlah Anggota</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelompok as $k)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $k->nama_kelompok }}</strong></td>
                        {{-- <td>{{ $k->tugas->count() }}</td> --}}
                        <td>{{ $k->tugas->pluck('user')->unique('id')->count() }}</td>
                        <td class="text-end">
                            <a href="{{ route('kelompok.show', $k->id) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                            {{-- <a href="{{ route('kelompok.edit', $k->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('kelompok.destroy', $k->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
