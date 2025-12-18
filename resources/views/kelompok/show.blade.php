@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detail Kelompok: {{ $kelompok->nama_kelompok }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Nama Anggota</th>
                <th>Nomor HP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->no_hp ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-muted">Belum ada anggota di periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <a href="{{ route('kelompok.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
