@extends('layouts.app')

@section('content')
<div class="container">
        <div class="alert alert-secondary " style="text-align: justify">
            {{-- <i class="bi bi-info-circle"></i> --}}
            Bapak/Ibu, Saat ini IKAN BELIDA memasuki  <strong>{{ $periodeAktif->nama_periode?? '' }}</strong>. 
            di Periode saat ini, anda masuk dalam <strong>{{ $kelompokUser->nama_kelompok?? '' }} </strong>mendapatkan tugas untuk membaca  <strong>Juz {{ $tugasUser->juz?? '' }}</strong>.
            <br>Silahkan Laporkan dengan Klik menu <strong>Progres</strong> di bawah lalu klik <strong>Tambah Progres</strong>.
        </div>
        {{-- <h2>Daftar Kelompok & Tugas</h2> --}}
        <div class="mb-4 p-3 bg-light rounded border">
            <strong>Progres Keseluruhan:</strong><br>
            Selesai {{ $juzSelesaiAll }} dari {{ $totalJuzAll }} juz 
            ({{ $persenAll }}%)
            <div class="progress mt-2" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" 
                    style="width: {{ $persenAll }}%;" 
                    aria-valuenow="{{ $persenAll }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $persenAll }}%
                </div>
            </div>
        </div>

    <!-- Tombol buka tutup detail -->
    <p>
        <a class="btn btn-sm btn-primary"  href="{{ route('tugas.kelompok') }}" role="button">
            Lihat Seluruh Tugas dan Progres
        </a>
    </p>

    
</div>
@endsection
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.progress-circle').forEach(function(el) {
        let persen = el.getAttribute('data-progress') || 0;
        // ubah persen ke derajat (360 * persen/100)
        let deg = (persen / 100) * 360 + 'deg';
        el.style.setProperty('--deg', deg);
    });

    
});
</script>
@endpush