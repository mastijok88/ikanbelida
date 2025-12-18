@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Buat Tugas Baru</h2>
    <form action="{{ route('tugas.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Periode</label>
            <select name="periode_id" class="form-control" required>
                @foreach($periode as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_periode }}</option>
                @endforeach
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
            <label>Anggota</label>
            <select name="user_id" class="form-control" required>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Juz</label>
            <input type="number" name="juz" class="form-control" min="1" max="30" required>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
