<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'IKAN BELIDA')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Amiri&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(to bottom, #f1fff2, #ffffff); padding-bottom: 80px; }
        .arabic { font-family: 'Amiri', serif; font-size: 1.4rem; }
        .header { position: sticky; top: 0; background-color: white; z-index: 999; box-shadow: 0 2px 5px rgba(0,0,0,0.05); padding: 10px 15px; }
        .bottom-menu { position: fixed; bottom: 0; left: 0; right: 0; background-color: white; border-top: 1px solid #ccc; display: flex; justify-content: space-around; padding: 10px 0; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); }
        .bottom-menu a { color: #333; text-align: center; text-decoration: none; font-size: 0.8rem; }
        .bottom-menu i { font-size: 1.2rem; display: block; margin-bottom: 3px; }
        .card-custom { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .stat-box { text-align: center; padding: 15px; border-radius: 12px; background-color: #ffffff; box-shadow: 0 1px 5px rgba(0,0,0,0.05); }
        .stat-number { font-size: 1.5rem; font-weight: 600; color: #198754; }



        .progress-circle {
            position: relative;
            width: 70px;  /* sedikit lebih besar */
            height: 70px;
            border-radius: 50%;
            background: conic-gradient(#28a745 var(--deg), #e6e6e6 0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: bold;
            color: #000; /* teks lebih gelap */
            margin-left: auto; /* dorong ke kanan */
            margin-right: 10px; /* beri jarak dari tepi card */
        }

        .progress-circle span {
            position: absolute;
            color: #000;
            text-shadow: 1px 1px 2px #fff; /* biar kontras */
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center py-2 px-3 bg-white shadow-sm sticky-top" style="z-index: 1000;">
    <div>
        <img src="{{ asset('dist/images/logo ikan belida.png') }}" alt="Logo" height="40">
    </div>
    <div class="text-center flex-grow-1">
        <div style="font-family: 'Amiri', serif; font-size: 1.2rem;">السَّلَامُ عَلَيْكُمْ</div>
        <div class="fw-bold">{{ auth()->user()->name ?? 'Tamu' }}</div>
    </div>
    <div>
        @auth
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm" title="Logout"><i class="fas fa-sign-out-alt fa-lg"></i></button>
        </form>
        @endauth
    </div>
</div>

<!-- Konten -->
<div class="container mt-4">
    @yield('content')
</div>

<!-- Bottom Navigation -->
<nav class="navbar fixed-bottom navbar-light bg-light border-top">
    <div class="container d-flex justify-content-around">
        <!-- Home -->
        <a href="{{ route('dashboard') }}" class="nav-link text-center">
            <i class="bi bi-house-door-fill"></i><br>
            Home
        </a>

        <!-- Tugas -->
        <a href="{{ route('tugas.kelompok') }}" class="nav-link text-center">
            <i class="fa fa-tasks"></i><br>
            Tugas
        </a>

        <!-- Tugas -->
        <a href="{{ route('progress.index') }}" class="nav-link text-center">
            <i class="fa fa-line-chart"></i><br>
            Progres
        </a>

        @if(auth()->user()->role == 'admin')
            <!-- Admin menu (hamburger dropdown) -->
            <div class="dropup">
                <a class="nav-link text-center" href="#" id="adminMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list"></i><br>
                    Menu
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                    {{-- <li><a class="dropdown-item" href="{{ route('periode.index') }}">Periode</a></li> --}}
                    {{-- <li><a class="dropdown-item" href="{{ route('kelompok.index') }}">Kelompok</a></li> --}}
                    {{-- <li><a class="dropdown-item" href="{{ route('users.index') }}">Anggota</a></li> --}}
                    <li><a class="dropdown-item" href="{{ route('rekap.belum') }}">Sisa Tilawah</a></li>
                    {{-- <li><a class="dropdown-item" href="{{ route('laporan.index') }}">Laporan</a></li> --}}
                    <li><a class="dropdown-item" href="{{ route('profil.index') }}">Profil</a></li>
                </ul>
            </div>
        @endif

        @if(auth()->user()->role == 'super_admin')
            <!-- Admin menu (hamburger dropdown) -->
            <div class="dropup">
                <a class="nav-link text-center" href="#" id="adminMenu" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-list"></i><br>
                    Menu
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                    {{-- <li><a class="dropdown-item" href="{{ route('periode.index') }}">Periode</a></li> --}}
                    <li><a class="dropdown-item" href="{{ route('kelompok.index') }}">Kelompok</a></li>
                    <li><a class="dropdown-item" href="{{ route('periode.index') }}">Periode</a></li>
                    <li><a class="dropdown-item" href="{{ route('users.index') }}">Anggota</a></li>
                    <li><a class="dropdown-item" href="{{ route('rekap.belum') }}">Sisa Tilawah</a></li>
                    <li><a class="dropdown-item" href="{{ route('laporan.index') }}">Laporan</a></li>
                    <li><a class="dropdown-item" href="{{ route('profil.index') }}">Profil</a></li>
                </ul>
            </div>
        @endif

        @if(auth()->user()->role == 'anggota')
            <!-- Profil untuk anggota -->
            <a href="{{ route('profil.index') }}" class="nav-link text-center">
                <i class="bi bi-person-circle"></i><br>
                Profil
            </a>
        @endif
    </div>
</nav>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
