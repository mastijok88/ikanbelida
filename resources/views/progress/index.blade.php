@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Progress Tilawah</h2>

    @if(session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
    @if(session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '{{ session('error') }}',
            });
        </script>
    @endif
    {{-- Dropdown Periode --}}
    <form method="GET" action="{{ route('progress.index') }}" class="mb-3">
        <label for="periode_id">Pilih Periode:</label>
        <select name="periode_id" id="periode_id" class="form-select d-inline w-auto" onchange="this.form.submit()">
            @foreach($periodes as $periode)
                <option value="{{ $periode->id }}" {{ $selectedPeriodeId == $periode->id ? 'selected' : '' }}>
                    {{ $periode->nama_periode }}
                </option>
            @endforeach
        </select>
    </form>

    {{-- Tabel Daftar Tugas --}}
    <h5>Daftar Tugas</h5>
    <table class="table table-bordered mb-5">
        <thead>
            <tr>
                <th>Periode</th>
                <th>Tugas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tugas as $t)
                <tr>
                    <td>{{ $t->periode->nama_periode }}</td>
                    <td>Juz {{ $t->juz }}
                        @if($t->is_additional)
                            <span class="badge bg-primary ms-2">Add</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $progressSelesai = $t->progress()->where('status', 'selesai')->count();
                            $progressTotal   = $t->progress()->count();
                        @endphp

                        @if($progressTotal == 0)
                            <span class="badge bg-secondary">Belum Mulai</span>
                        @elseif($progressSelesai > 0 && $progressSelesai == $progressTotal)
                            <span class="badge bg-primary">Selesai</span>
                        @else
                            <span class="badge bg-success">Sedang Berjalan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Belum ada tugas di periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tombol tambah progress --}}
    <a href="{{ route('progress.create') }}" class="btn btn-primary mb-3">+ Tambah Progress</a>

    {{-- Tabel Progress Detail --}}
    <h5>Rincian Progress</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                {{--<th>Periode</th>--}}
                <th>Tugas</th>
                <th>Nama Surat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($progress as $p)
                <tr>
                    {{--<td>{{ $p->tugas->periode->nama_periode }}</td>--}}
                    <td>Juz {{ $p->tugas->juz }}</td>
                    <td>{{ $p->nama_surat }}, dari ayat 
                        {{ $p->ayat_dari }} s.d. ayat
                        {{ $p->ayat_sampai }}
                    </td>
                    <td>
                        <form id="hapus-{{ $p->id }}" action="{{ route('progress.destroy', $p->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button  type="button" class="btn btn-danger btn-sm"  onclick="konfirmasiHapus('{{ $p->id }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Belum ada progress</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Hapus Progress?',
            text: "Data progress ini akan dihapus, yakin?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading saat proses penghapusan
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Submit form
                document.getElementById('hapus-' + id).submit();
            }
        });
    }

    // Saat tombol simpan ditekan (di form progress.create)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action*="progress"] button[type="submit"]');
        if (form) {
            form.addEventListener('click', function(e) {
                const btn = e.target;
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Tunggu sebentar...';
                btn.closest('form').submit();
            });
        }
    });
</script>
@endpush
