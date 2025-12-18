@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Progress Tilawah</h3>

    <form action="{{ route('progress.store') }}" method="POST" id="formProgress">
        @csrf
        <div class="mb-3">
            <label for="tugas_id" class="form-label">Pilih Tugas (Juz)</label>
            <select name="tugas_id" id="tugas_id" class="form-select" required>
                <option value="">-- Pilih Tugas --</option>
                @foreach($tugas as $t)
                    <option value="{{ $t->id }}">
                        Periode {{ $t->periode->nama_periode ?? '' }} - 
                        {{ $t->kelompok->nama_kelompok ?? '' }} - 
                        Juz {{ $t->juz }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="surat" class="form-label">Dari Surat</label>
                <select name="nama_surat_dari" id="surat_dari" class="form-select" required>
                    <option value="">-- Pilih Surat --</option>
                </select>
            </div>
            <div class="col">
                <label for="ayat_dari" class="form-label">Ayat</label>
                <select id="ayat_dari" name="ayat_dari" class="form-select" required>
                    <option value="">-- Pilih Ayat --</option>
                </select>
                {{-- <input type="number" name="ayat_dari" id="ayat_dari" class="form-control" required> --}}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="surat" class="form-label">Sampai Surat</label>
                <select name="nama_surat_sampai" id="surat_sampai" class="form-select" required>
                    <option value="">-- Pilih Surat --</option>
                </select>
            </div>
            <div class="col">
                <label for="ayat_sampai" class="form-label">Ayat</label>
                <select id="ayat_sampai" name="ayat_sampai" class="form-select" required>
                    <option value="">-- Pilih Ayat --</option>
                </select>
                {{-- <input type="number" name="ayat_sampai" id="ayat_sampai" class="form-control" required> --}}
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Total Dibaca:</label>
                <input type="text" id="total_baca" class="form-control" readonly>
            </div>
        </div>
        <button type="submit" id="btnSimpan" class="btn btn-success w-100">
            <i class="fas fa-save"></i> Simpan Progress
        </button>
        
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){
    const tugasSelect = document.getElementById("tugas_id");
    const suratDariSelect = document.getElementById("surat_dari");
    const suratSampaiSelect = document.getElementById("surat_sampai");
    const dariInput = document.getElementById("ayat_dari");
    const sampaiInput = document.getElementById("ayat_sampai");
    const totalInput = document.getElementById("total_baca");

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }
    // Saat pilih tugas (Juz)
    tugasSelect.addEventListener("change", function() {
        const selected = this.options[this.selectedIndex];
        if (!selected) return;

        // Ambil juz dari teks option (misal "Juz 2")
        const juz = selected.text.match(/Juz (\d+)/)[1];

        // reset surat
        suratDariSelect.innerHTML = `<option value="">-- Pilih Surat --</option>`;
        suratSampaiSelect.innerHTML = `<option value="">-- Pilih Surat --</option>`;

        const tugasId = tugasSelect.value;
        fetch(`/get-surat/${juz}?tugas_id=${tugasId}`)
            .then(res => res.json())
            .then(data => {
                data.forEach(s => {
                    suratDariSelect.innerHTML += `<option value="${s.id}">${s.nama}</option>`;
                    suratSampaiSelect.innerHTML += `<option value="${s.id}">${s.nama}</option>`;
                });

                if (data.length === 0) {
                    Swal.fire('Semua ayat sudah disetor!', 'Tidak ada ayat tersisa untuk juz ini.', 'info');
                }
            });
    });


    function hitungTotal() {
        const selectedTugas = tugasSelect.options[tugasSelect.selectedIndex];
        if (!selectedTugas) return;

        const juz = selectedTugas.text.match(/Juz (\d+)/)[1];
        const suratDari   = suratDariSelect.value;
        const suratSampai = suratSampaiSelect.value;
        const ayatDari    = dariInput.value;
        const ayatSampai  = sampaiInput.value;

        if (!suratDari || !suratSampai || !ayatDari || !ayatSampai) {
            totalInput.value = "";
            return;
        }

        fetch(`/hitung-ayat?juz=${juz}&surat_dari=${suratDari}&ayat_dari=${ayatDari}&surat_sampai=${suratSampai}&ayat_sampai=${ayatSampai}`)
            .then(res => res.json())
            .then(data => {
                totalInput.value = data.total + " ayat";
            })
            .catch(err => {
                console.error(err);
                totalInput.value = "Error";
            });
    }


    function loadAyat(juz, surat, targetSelect, placeholder) {
        if (!surat) return;
        fetch(`/api/ayat-by-surat/${juz}/${surat}?tugas_id=${tugasSelect.value}`)
            .then(res => res.json())
            .then(data => {
                resetSelect(targetSelect, placeholder);
                if (data.min && data.max) {
                    for (let i = data.min; i <= data.max; i++) {
                        let opt = document.createElement("option");
                        opt.value = i;
                        opt.textContent = i;
                        targetSelect.appendChild(opt);
                    }
                }
            });
    }

    // event ketika pilih surat_dari
    suratDariSelect.addEventListener("change", function() {
        let juz = tugasSelect.options[tugasSelect.selectedIndex]?.text.split("Juz ")[1];
        loadAyat(juz, this.value, dariInput, "-- Pilih Ayat Dari --");
    });

    // event ketika pilih surat_sampai
    suratSampaiSelect.addEventListener("change", function() {
        let juz = tugasSelect.options[tugasSelect.selectedIndex]?.text.split("Juz ")[1];
        loadAyat(juz, this.value, sampaiInput, "-- Pilih Ayat Sampai --");
    });


    // Event listener juga diperbaiki

    // suratDariSelect.addEventListener("change", hitungTotal);
    // suratSampaiSelect.addEventListener("change", hitungTotal);
    dariInput.addEventListener("input", hitungTotal);
    sampaiInput.addEventListener("input", hitungTotal);
    
    // ✅ Tambahkan SweetAlert loading & disable tombol submit
    const form = document.getElementById('formProgress');
    const btnSimpan = document.getElementById('btnSimpan');

    form.addEventListener('submit', function(e) {
        btnSimpan.disabled = true;
        btnSimpan.classList.add('btn-secondary');
        btnSimpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Tunggu sebentar...';
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Harap tunggu, progress sedang disimpan.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });
    });

});

</script>


@endpush

