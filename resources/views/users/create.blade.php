@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Anggota</h2>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>No HP</label>
            <input type="text" name="no_hp" class="form-control" required>
        </div>
        {{-- <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div> --}}
        {{-- <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="admin">Admin</option>
                <option value="anggota">Anggota</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Kelompok</label>
            <select name="kelompok_id" class="form-control" required>
                @foreach($kelompok as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelompok }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Juz Terakhir</label>
            <input type="number" name="juz_terakhir" class="form-control" value="1">
        </div> --}}
        <button class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
