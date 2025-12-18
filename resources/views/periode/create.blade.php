@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Periode</h2>
    <form action="{{ route('periode.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama Periode</label>
            <input type="text" name="nama_periode" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" class="form-control" required>
        </div>
        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('periode.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
