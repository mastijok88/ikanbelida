@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Daftar Periode Tilawah</h4>

        <div>
            @if (auth()->user()->role === 'super_admin')
                <form action="{{ route('admin.generate.periode') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Generate Periode Baru
                    </button>
                </form>
            @endif
        </div>
    </div>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama Periode</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periodes as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->nama_periode }}</td>
                            <td>{{ $p->tanggal_mulai->format('d M Y') }} - {{ $p->tanggal_selesai->format('d M Y') }}</td>
                            <td>
                                @if ($p->status === 'aktif')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Selesai</span>
                                @endif
                            </td>
                            <td>
                                {{-- <a href="{{ route('periode.show', $p->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a> --}}

                                @if (auth()->user()->role === 'super_admin')
                                    {{-- Tombol Tutup Periode --}}
                                    @if ($p->status == 'aktif')
                                        <form action="{{ route('periode.tutup', $p->id) }}" method="POST" class="d-inline form-tutup">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-danger btn-sm btn-tutup" data-nama="{{ $p->nama_periode }}">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Tombol Buka Periode --}}
                                        <form action="{{ route('periode.buka', $p->id) }}" method="POST" class="d-inline form-buka">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-success btn-sm btn-buka" data-nama="{{ $p->nama_periode }}">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    @endif
                            
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('periode.destroy', $p->id) }}" method="POST" class="d-inline form-hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-hapus" data-nama="{{ $p->nama_periode }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                                
                            </td>
                            

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada periode</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tutup periode
    document.querySelectorAll('.btn-tutup').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nama = btn.dataset.nama;
            Swal.fire({
                title: 'Tutup Periode?',
                text: `Periode "${nama}" akan ditutup.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, tutup!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // Buka periode
    document.querySelectorAll('.btn-buka').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nama = btn.dataset.nama;
            Swal.fire({
                title: 'Buka Periode?',
                text: `Periode "${nama}" akan dibuka kembali.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, buka!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // Hapus periode
    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nama = btn.dataset.nama;
            Swal.fire({
                title: 'Hapus Periode?',
                text: `Seluruh tugas dan progress dari "${nama}" juga akan terhapus!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
    
    
    // === ALERT BERHASIL ===
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    @endif

    // === ALERT GAGAL (opsional) ===
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
        });
    @endif
});
</script>

@endpush

