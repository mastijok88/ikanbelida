@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Periode</h2>
    <form action="{{ route('periode.update', $periode->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Nama Periode</label>
            <input type="text" name="nama_periode" class="form-control" value="{{ $periode->nama_periode }}" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control" value="{{ $periode->tanggal_mulai }}" required>
        </div>
        <div class="mb-3">
            <label>Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" class="form-control" value="{{ $periode->tanggal_selesai }}" required>
        </div>
        <button class="btn btn-success">Update</button>
        <a href="{{ route('periode.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
