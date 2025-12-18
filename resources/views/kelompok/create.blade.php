@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ isset($kelompok) ? 'Edit' : 'Tambah' }} Kelompok</h2>
    <form action="{{ isset($kelompok) ? route('kelompok.update', $kelompok->id) : route('kelompok.store') }}" method="POST">
        @csrf
        @if(isset($kelompok))
            @method('PUT')
        @endif
        <div class="mb-3">
            <label>Nama Kelompok</label>
            <input type="text" name="nama_kelompok" class="form-control" 
                   value="{{ $kelompok->nama_kelompok ?? '' }}" required>
        </div>
        <button class="btn btn-success">Simpan</button>
        <a href="{{ route('kelompok.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
