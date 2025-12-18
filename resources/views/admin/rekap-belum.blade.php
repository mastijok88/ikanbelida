@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Rekap Sisa Tilawah</h3>

    @if($periodes->isEmpty())
        <div class="alert alert-secondary">
            <i class="bi bi-info-circle"></i>
            Belum ada periode yang selesai. 
            Tutup periode aktif terlebih dahulu untuk melihat sisa tilawah.
        </div>
    @else

        <div class="alert alert-secondary">
            <i class="bi bi-info-circle"></i>
            Default menampilkan <strong>periode terakhir yang telah ditutup.</strong>. 
            Silakan pilih periode lain dari dropdown untuk melihat rekap berbeda.
        </div>
        <form method="GET" action="{{ route('rekap.belum') }}" class="mb-3">
            <label for="periode_id">Periode:</label>
            <select name="periode_id" id="periode_id" onchange="this.form.submit()">
                @foreach ($periodes as $periode)
                    <option value="{{ $periode->id }}" {{ $periodeId == $periode->id ? 'selected' : '' }}>
                        {{ $periode->nama_periode }}
                    </option>
                @endforeach
            </select>
        </form>
        @foreach ($rekap as $r)
            <h5 class="mt-3">{{ $r['nama'] }}</h5>
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th width="180px">Juz</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($r['belum'] as $tugas)
                        <tr>
                            <td>Juz {{ $tugas->juz }}
                                @if($tugas->progress_terakhir)
                                    <br><small>terakhir dibaca :<br> {{ $tugas->progress_terakhir }}</small>
                                @else
                                    <br><small>Belum ada Progres</small>
                                @endif
                            </td>
                            <td>
                                @if ($tugas->diambil_oleh)
                                    <span class="text-success">diambil {{ $tugas->pengambil->name }}
                                        @php
                                            // cek progress di tugas lama
                                            $adaProgressLama = $tugas->progress->count() > 0;
                                        
                                            // cari tugas tambahan yang sama periode, kelompok, juz
                                            $tugasTambahan = \App\Models\Tugas::where('periode_id', $tugas->periode_id)
                                                ->where('kelompok_id', $tugas->kelompok_id)
                                                ->where('juz', $tugas->juz)
                                                ->where('is_additional', true)
                                                ->first();
                                        
                                            // cek progress di tugas tambahan kalau ada
                                            $adaProgressTambahan = false;
                                            if ($tugasTambahan) {
                                                $adaProgressTambahan = $tugasTambahan->progress->count() > 0;
                                            }
                                        @endphp
                                        @if ($tugas->diambil_oleh == auth()->id() && !$adaProgressTambahan)
                                            <form id="batal-{{ $tugas->id }}" action="{{ route('tugas.batal', $tugas->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="konfirmasiBatal('{{ $tugas->id }}')">
                                                    Batal
                                                </button>
                                            </form>
                                        @endif
                                    </span>
                                @else
                                    <form  id="ambil-{{ $tugas->id }}" action="{{ route('tugas.ambil', [
                                        'periode' => $periodeId,
                                        'juz' => $tugas->juz,
                                        'tugasId' => $tugas->id,
                                        'kelompokId' => $tugas->kelompok_id,
                                    ]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick="konfirmasiAmbil('{{ $tugas->id }}')">
                                            Ambil
                                        </button>
                                        {{-- Periode :{{ $periodeId }}, Tugas Id :{{ $tugas->id }}, Juz : {{ $tugas->juz }} --}}
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted">Tidak ada tugas belum selesai</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
    <script>
        function konfirmasiAmbil(id) {
            Swal.fire({
                title: 'Ambil Tugas?',
                text: "Anda yakin akan mengambil alih tugas ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, ambil!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('ambil-' + id).submit();
                }
            })
        }
        
        
        function konfirmasiBatal(id) {
            Swal.fire({
                title: 'Batalkan Tugas?',
                text: "Batalkan tugas tambahan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, batal!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('batal-' + id).submit();
                }
            })
        }
    </script>
@endpush
