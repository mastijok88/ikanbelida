@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Profil Anggota</h3>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Nama:</strong> {{ $user->name }}</p>
            <p><strong>Nomor HP:</strong> {{ $user->no_hp ?? '-' }}</p>
            <p><strong>Menjadi Anggota Sejak:</strong> {{ $user->created_at->format('d M Y') }}</p>
            <p><strong>Status:</strong> 
                @if(auth()->user()->role === 'super_admin')
                    <span class="badge bg-warning">Super Admin</span>
                @elseif(auth()->user()->role === 'super_admin')
                    <span class="badge bg-success">Admin</span>
                @else
                    <span class="badge bg-secondary">Anggota</span>
                @endif
            </p>
        </div>
    </div>

    <h4>Ubah Password</h4>
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('profil.updatePassword') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="mb-3">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
