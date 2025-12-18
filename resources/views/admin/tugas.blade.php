@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Tombol generate periode baru untuk super admin --}}
    @if(auth()->user()->role === 'super_admin')
        <form action="{{ route('admin.generate.periode') }}" method="POST" class="mb-3">
            @csrf
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="kirim_wa" id="kirim_wa" value="1">
                <label class="form-check-label" for="kirim_wa">
                    Kirim Notifikasi WA
                </label>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-plus"></i> Generate Periode Baru
            </button>
        </form>
    @endif

    {{-- Filter periode --}}
    <form method="GET" action="{{ route('tugas.kelompok') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <select name="periode_id" class="form-select" onchange="this.form.submit()">
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ $periodeId == $p->id ? 'selected' : '' }}>
                            {{ $p->nama_periode }}
                            ({{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y') }}
                            - {{ \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Daftar Kelompok --}}
    <div class="accordion" id="accordionKelompok">
        @foreach($kelompok as $k)
        @php
            $totalProgress = 0;
            $anggotaCount = $k->tugas->count();
            foreach ($k->tugas as $t) {
                $totalAyat = \App\Models\JuzAyat::where('juz', $t->juz)->sum(\DB::raw('ayat_sampai - ayat_dari + 1'));
                $sudahSetor = $t->progress->sum(fn($p) => $p->ayat_sampai - $p->ayat_dari + 1);
                $persen = $totalAyat > 0 ? ($sudahSetor / $totalAyat * 100) : 0;
                $totalProgress += $persen;
            }
            $kelompokProgress = $anggotaCount > 0 ? round($totalProgress / $anggotaCount, 2) : 0;
        @endphp

        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{ $k->id }}">
                <button class="accordion-button collapsed d-flex justify-content-between align-items-center" 
                        type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ $k->id }}" aria-expanded="false" aria-controls="collapse{{ $k->id }}">
                    <span>
                        <strong>{{ $k->nama_kelompok }}</strong>
                        <br><span class="badge bg-success">({{ $k->persen }}%)</span>
                    </span>
                    <div class="progress ms-3 flex-grow-1" style="max-width: 200px; height: 15px; margin-right: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $kelompokProgress }}%;">
                            {{ $kelompokProgress }}%
                        </div>
                    </div>
                    <span style="margin-right: 10px;">{{ $k->juz_selesai }} dari {{ $k->total_juz }} juz</span>
                </button>
            </h2>

            <div id="collapse{{ $k->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $k->id }}" data-bs-parent="#accordionKelompok">
                <div class="accordion-body">
                    @php
                        $tugasMap = $k->tugas->keyBy('juz');
                    @endphp

                    @for($j = 1; $j <= 30; $j++)
                        @php
                            $t = $tugasMap->get($j);
                        @endphp

                        @if($t)
                            {{-- Ada anggota untuk juz ini --}}
                            @php
                                $totalAyat = \App\Models\JuzAyat::where('juz', $t->juz)->sum(\DB::raw('ayat_sampai - ayat_dari + 1'));
                                $sudahSetor = $t->progress->sum(fn($p) => $p->ayat_sampai - $p->ayat_dari + 1);
                                $persenAnggota = $totalAyat > 0 ? round(($sudahSetor / $totalAyat * 100), 2) : 0;
                            @endphp

                            <div class="card mb-2 {{ $t->user->status === 'nonaktif' ? 'border-warning bg-warning-subtle' : '' }}">
                                <div class="card-body d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#detail-{{ $t->id }}">
                                    <div>
                                        <strong>{{ $t->user->name }}</strong><br>
                                        📖 Juz {{ $t->juz }}<br>
                                        Periode: {{ $t->periode->nama_periode }}
                                    </div>
                                    <div class="progress-circle" data-progress="{{ $persenAnggota }}">
                                        <span>{{ $persenAnggota }}%</span>
                                    </div>
                                </div>

                                {{-- Detail progress anggota --}}
                                <div id="detail-{{ $t->id }}" class="collapse">
                                    @if($t->progress && $t->progress->count())
                                        <ul class="list-group list-group-flush">
                                            @foreach($t->progress as $p)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>📖 {{ $p->nama_surat ?? 'Surat ?' }} : {{ $p->ayat_dari }} - {{ $p->ayat_sampai }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted m-2">Belum ada rincian progress</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Juz kosong --}}
                            @if(Auth::user()->role === 'super_admin')
                                <div class="card mb-2 border border-dashed bg-light">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-muted">Kosong</strong><br>
                                            📖 Juz {{ $j }}
                                        </div>
                                        @if(Auth::user()->role === 'super_admin' || Auth::user()->role === 'admin')
                                            <button class="btn btn-sm btn-primary btn-isi" 
                                                    data-juz="{{ $j }}" 
                                                    data-kelompok="{{ $k->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalIsiJuz{{ $k->id }}{{ $j }}">
                                                Isi
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Modal isi juz kosong --}}
                            <div class="modal fade" id="modalIsiJuz{{ $k->id }}{{ $j }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ url('/kelompok/'.$k->id.'/isi-juz') }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Isi Juz Kosong (Juz {{ $j }})</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="juz" value="{{ $j }}">

                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Kelompok Asal</label>
                                                    <select id="kelompokSelect{{ $k->id }}{{ $j }}" class="form-select kelompokSelect" data-target="{{ $k->id }}{{ $j }}">
                                                        <option value="">-- Pilih Kelompok --</option>
                                                        @foreach($semuaKelompok as $kk)
                                                            @if($kk->id != $k->id)
                                                                <option value="{{ $kk->id }}">{{ $kk->nama_kelompok }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Pilih Anggota (berdasarkan Juz)</label>
                                                    <select id="anggotaSelect{{ $k->id }}{{ $j }}" name="tugas_id" class="form-select" required>
                                                        <option value="">-- Pilih Anggota --</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // tampilkan progress circle
    document.querySelectorAll('.progress-circle').forEach(function(el) {
        let persen = el.getAttribute('data-progress') || 0;
        let deg = (persen / 100) * 360 + 'deg';
        el.style.setProperty('--deg', deg);
    });

    // load anggota by kelompok
    document.querySelectorAll('.kelompokSelect').forEach(select => {
        select.addEventListener('change', function() {
            const kelompokId = this.value;
            const target = this.dataset.target;
            const anggotaSelect = document.getElementById('anggotaSelect' + target);

            if (!kelompokId) {
                anggotaSelect.innerHTML = '<option value="">-- Pilih Anggota --</option>';
                return;
            }

            anggotaSelect.innerHTML = '<option>Loading...</option>';

            const periodeId = {{ $periodeId }}; // kirim id periode aktif
            fetch(`/kelompok/${kelompokId}/anggota-by-kelompok/${periodeId}`)
                .then(res => res.json())
                .then(data => {
                    anggotaSelect.innerHTML = '<option value="">-- Pilih Anggota --</option>';
                    data.forEach(a => {
                        anggotaSelect.innerHTML += `<option value="${a.id}">${a.nama} (Juz ${a.juz})</option>`;
                    });
                })
                .catch(() => {
                    anggotaSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                });
        });
    });
});
</script>
@endpush
