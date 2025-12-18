@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Tugas</h2>
    <a href="{{ route('tugas.create') }}" class="btn btn-success mb-3">Buat Tugas</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Periode</th>
                <th>Kelompok</th>
                <th>Anggota</th>
                <th>Juz</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tugas as $t)
                <tr>
                    <td>{{ $t->periode->nama_periode }}</td>
                    <td>{{ $t->kelompok->nama_kelompok }}</td>
                    <td>{{ $t->user->name }}</td>
                    <td>{{ $t->juz }}</td>
                    <td>
                        <a href="{{ route('tugas.edit', $t->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('tugas.destroy', $t->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
