@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan {{ $periode->nama_periode }}</h3>

    {{-- Dropdown pilih periode --}}
    <form method="GET" action="{{ route('laporan.index') }}" class="mb-3">
        <label for="periode">Pilih Periode:</label>
        <select id="periode" name="periodeId" class="form-select d-inline-block w-auto"
                onchange="window.location.href='{{ route('laporan.index') }}/'+this.value">
            @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ $p->id == $periode->id ? 'selected' : '' }}>
                    {{ $p->nama_periode }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="card mb-3">
    <div class="card-body">
        <div style="white-space: pre-line; word-break: break-word;">
            {{ $laporan }}
        </div>
    </div>
</div>

    <form action="{{ route('laporan.kirim', $periode->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">Kirim ke Grup WA</button>
    </form>
</div>
@endsection
