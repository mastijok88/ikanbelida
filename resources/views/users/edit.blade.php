@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Anggota</h2>
    <form action="{{ route('users.update',$user->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="mb-3">
            <label>No HP</label>
            <input type="text" name="no_hp" class="form-control" value="{{ $user->no_hp }}" required>
        </div>
        {{-- <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" value="">
        </div> --}}
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                @if(auth()->user()->role === 'super_admin')
                <option value="super_admin" {{ $user->role === "super_admin" ? "selected" : "" }}>Super Admin</option>
                @endif
                <option value="admin" {{ $user->role === "admin" ? "selected" : "" }}>Admin</option>
                <option value="anggota" {{ $user->role === "anggota" ? "selected" : "" }}>Anggota</option>
            </select>
        </div>

        @if(auth()->user()->role === 'super_admin')
            <div class="mb-3">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="aktif" {{ $user->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="izin" {{ $user->status == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="nonaktif" {{ $user->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        @endif
        {{-- <div class="mb-3">
            <label>Kelompok</label>
            <select name="kelompok_id" class="form-control" required>
                @foreach($kelompok as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelompok }} {{ $k->id == $user->kelompok_id ? "selected" : "" }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Juz Terakhir</label>
            <input type="number" name="juz_terakhir" class="form-control" value="1" value="{{ $user->juz_terakhir }}">
        </div> --}}
        <button class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
