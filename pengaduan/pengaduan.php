<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// ==== Label kategori & status sesuai ENUM di database ====
$kategoriLabel = [
    'sarana_prasarana' => 'Sarana & Prasarana',
    'kepegawaian'      => 'Kepegawaian',
    'pelayanan'        => 'Pelayanan',
    'lainnya'          => 'Lainnya',
];
$statusInfo = [
    'diajukan'   => ['label' => 'Diajukan',   'badge' => 'badge-warning'],
    'diproses'   => ['label' => 'Diproses',   'badge' => 'badge-info'],
    'ditanggapi' => ['label' => 'Ditanggapi', 'badge' => 'badge-primary'],
    'ditutup'    => ['label' => 'Selesai',    'badge' => 'badge-success'],
];

// ==================================================================
// ==== FILTER PERIODE: Semua Waktu / Per Bulan / Per Hari ====
// ==================================================================
$filterMode = $_GET['filter'] ?? 'semua'; // semua | bulan | hari
if (!in_array($filterMode, ['semua', 'bulan', 'hari'], true)) {
    $filterMode = 'semua';
}

$filterBulanInput = $_GET['bulan'] ?? date('Y-m');
$filterHariInput  = $_GET['tanggal'] ?? date('Y-m-d');

// Klausa WHERE untuk masing-masing tabel (default: tanpa filter / semua waktu)
$hariList  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
$bulanList = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];


$whereBerita     = '';
$wherePengumuman = "WHERE status = 'terbit' AND status = 'draf'";
$wherePengaduan  = '1=1';
$labelPeriode    = 'Semua Waktu';

if ($filterMode === 'bulan' && preg_match('/^\d{4}-\d{2}$/', $filterBulanInput)) {
    [$thn, $bln] = explode('-', $filterBulanInput);
    $thn = (int)$thn;
    $bln = (int)$bln;

    $whereBerita     = "WHERE MONTH(created_at) = $bln AND YEAR(created_at) = $thn";
    $wherePengumuman = "WHERE status = 'terbit' AND MONTH(tanggal) = $bln AND YEAR(tanggal) = $thn";
    $wherePengaduan  = "MONTH(created_at) = $bln AND YEAR(created_at) = $thn";
    $labelPeriode    = 'Bulan ' . ($bulanList[$bln] ?? $bln) . ' ' . $thn;
} elseif ($filterMode === 'hari' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterHariInput)) {
    $hariEsc = mysqli_real_escape_string($koneksi, $filterHariInput);

    $whereBerita     = "WHERE DATE(created_at) = '$hariEsc'";
    $wherePengumuman = "WHERE status = 'terbit' AND tanggal = '$hariEsc'";
    $wherePengaduan  = "DATE(created_at) = '$hariEsc'";

    $ts = strtotime($filterHariInput);
    $labelPeriode = $hariList[date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $bulanList[(int)date('n', $ts)] . ' ' . date('Y', $ts);
} else {
    $filterMode = 'semua';
}

// ==== Statistik kartu (ikut menyesuaikan filter yang aktif) ====
$jmlDiajukan = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'diajukan'")) {
    $jmlDiajukan = mysqli_fetch_assoc($r)['jml'];
}
$jmlDiproses = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'diproses'")) {
    $jmlDiproses = mysqli_fetch_assoc($r)['jml'];
}
$jmlDitanggapi = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'ditanggapi'")) {
    $jmlDitanggapi = mysqli_fetch_assoc($r)['jml'];
}
$jmlSelesai = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'ditutup'")) {
    $jmlSelesai = mysqli_fetch_assoc($r)['jml'];
}

// ==== Notifikasi hasil aksi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_balas'  => ['type' => 'success', 'text' => 'Tanggapan terkirim dan status berhasil diperbarui.', 'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Isi tanggapan wajib diisi.',                          'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan tanggapan ke database.',              'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Data pengaduan tidak ditemukan.',                     'icon' => 'fa-exclamation-circle'],
];

// ==== Ambil semua tanggapan sekaligus, dikelompokkan per pengaduan_id ====
$threadByPengaduan = [];
$sqlThread = "SELECT t.pengaduan_id, t.isi, t.created_at, a.name AS admin_name
              FROM tanggapan_pengaduan t
              LEFT JOIN admin a ON a.id = t.admin_id
              ORDER BY t.created_at ASC";
if ($r = mysqli_query($koneksi, $sqlThread)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $threadByPengaduan[$row['pengaduan_id']][] = [
            'admin' => $row['admin_name'] ?: 'Admin',
            'waktu' => date('d-m-Y H:i', strtotime($row['created_at'])),
            'isi'   => $row['isi'],
        ];
    }
}

// ==== Daftar pengaduan ====
$daftarPengaduan = [];
$sqlPengaduan = "SELECT id, no_tiket, nama, email, telepon, kategori, judul, isi, lampiran, status, created_at
                  FROM pengaduan
                  WHERE $wherePengaduan
                  ORDER BY created_at DESC";
if ($r = mysqli_query($koneksi, $sqlPengaduan)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $daftarPengaduan[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Pengaduan</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <style>
        :root{
            --navy: #0B1F3A;
            --navy-mid: #162F55;
            --navy-light: #1E3F70;
            --gold: #C89B3C;
            --gold-light: #E8BF6A;
            --cream: #F7F4EE;
            --white: #FFFFFF;
            --text: #1A1A2E;
            --muted: #6B7280;
            --line: #E5E1D8;
            --reveal-easing:cubic-bezier(.2,.9,.2,1);
        }

        a:not(.btn):not(.page-link):not(.read-more-link):not(.dataTables_wrapper .dataTables_paginate a),
        .text-primary {
            color: inherit !important;
        }

        .sidebar .nav-link,
        .sidebar .collapse-item,
        .sidebar .sidebar-brand,
        .sidebar .sidebar-heading,
        .sidebar .nav-link span {
            color: white !important;
        }

        .bg-gradient-primary,
        .btn-primary,
        .sidebar .nav-item.active .nav-link,
        .page-item.active .page-link,
        .progress-bar.bg-info {
            background-color: #162F55 !important;
            border-color: #162F55 !important;
            background-image: none !important;
        }

        /* ==== Modal Detail Pengaduan ==== */
        #detailPengaduanModal .modal-header {
            background-color: #162F55;
            color: #fff;
            border-bottom: none;
        }
        #detailPengaduanModal .modal-header .close {
            color: #fff;
            opacity: 0.85;
            text-shadow: none;
        }
        #detailPengaduanModal .info-box {
            background: #fff5f7;
            border-radius: 0.35rem;
            padding: 10px 14px;
            height: 100%;
        }
        #detailPengaduanModal .info-box .lbl {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: #b48a94;
            text-transform: uppercase;
        }
        #detailPengaduanModal .info-box .val {
            font-size: 0.9rem;
            font-weight: 700;
            color: #4d1e2a;
            margin-top: 2px;
        }
        #detailPengaduanModal .section-title {
            font-size: 0.78rem;
            font-weight: 800;
            color: #162F55;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 18px 0 8px;
        }
        #detailPengaduanModal .isi-box {
            background: #fff5f7;
            border-radius: 0.35rem;
            padding: 14px;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #4d1e2a;
        }
        #detailPengaduanModal .lampiran-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #f0e3e7;
            padding: 6px 12px;
            border-radius: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #7b1e3d;
        }
        #detailPengaduanModal .thread-item {
            border-bottom: 1px solid #eee0e3;
            padding: 10px 0;
        }
        #detailPengaduanModal .thread-item:last-child {
            border-bottom: none;
        }
        #detailPengaduanModal .thread-item .t-head {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            margin-bottom: 4px;
        }
        #detailPengaduanModal .thread-item .t-head b {
            color: #162F55;
        }
        #detailPengaduanModal .thread-item .t-head span {
            color: #9a8a8e;
        }
        #detailPengaduanModal .thread-item .t-body {
            font-size: 0.85rem;
            color: #4d1e2a;
        }
        #detailPengaduanModal .no-thread {
            font-size: 0.85rem;
            color: #9a8a8e;
            font-style: italic;
        }

        /* ==== Filter Periode ==== */
        .filter-periode-card .form-inline .form-control {
            min-width: 150px;
        }
        .filter-periode-label {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .filter-periode-active {
            font-weight: 800;
            color: var(--navy-mid);
        }
        .filter-mode-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--cream);
            border: 1px solid var(--line);
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--navy-mid);
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../dashboard/index.php">
                <div class="sidebar-brand-icon">
                    <i><img src="../img/Logo1.png" alt="" style="width: 60px; height: 60px; object-fit: contain;"></i>
                </div>
                <div class="d-flex flex-column" style="color: #fff !important;">
                    <div style="font-size: 0.7rem;">Dinas Pendidikan</div>
                    <div style="font-size: 0.5rem;"><i>Kabupaten Sumenep</i></div>
                </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../dashboard/index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Berita -->
            <li class="nav-item">
                <a class="nav-link" href="../berita/berita.php">
                    <i class="fas fa-fw fa-newspaper"></i>
                    <span>Berita</span></a>
            </li>

            <!-- Nav Item - Pengumuman -->
            <li class="nav-item">
                <a class="nav-link" href="../pengumuman/pengumuman.php">
                    <i class="fas fa-fw fa-bullhorn"></i>
                    <span>Pengumuman</span></a>
            </li>

            <!-- Nav Item - Galeri -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                    aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-images"></i>
                    <span>Galeri</span>
                </a>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="../galeri/galeri_foto.php">Foto</a>
                        <a class="collapse-item" href="../galeri/galeri_video.php">Video</a>
                        <a class="collapse-item" href="../galeri/galeri_prestasi.php">Prestasi</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pengaduan -->
            <li class="nav-item active">
                <a class="nav-link" href="../pengaduan/pengaduan.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Pengaduan</span></a>
            </li>

            <!-- Nav Item - Sakip -->
            <li class="nav-item">
                <a class="nav-link" href="../sakip/sakip.php">
                    <i class="fas fa-fw fa-file-contract"></i>
                    <span>Sakip</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Instansi
            </div>

            <!-- Nav Item - Profil -->
            <li class="nav-item">
                <a class="nav-link" href="../profil/profil.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profil</span></a>
            </li>

            <!-- Nav Item - Pegawai -->
            <li class="nav-item">
                <a class="nav-link" href="../pegawai/pegawai.php">
                    <i class="fas fa-fw fa-user-friends"></i>
                    <span>Pegawai</span></a>
            </li>

            <!-- Nav Item - Bidang -->
            <li class="nav-item">
                <a class="nav-link" href="../bidang/bidang.php">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Bidang</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?></span>
                                <img class="img-profile rounded-circle"
                                    src="../img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h1 class="h3 mb-0 text-gray-800">Pengaduan</h1>
                            <div class="text-muted small">Kelola pengaduan masyarakat yang masuk melalui website Dinas Pendidikan Kabupaten Sumenep.</div>
                        </div>
                    </div>

                    <?php if ($notif && isset($notifMsg[$notif])) : ?>
                        <div id="notifAlert" class="alert alert-<?= $notifMsg[$notif]['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas <?= $notifMsg[$notif]['icon'] ?> mr-2"></i><?= htmlspecialchars($notifMsg[$notif]['text']) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter Periode -->
                    <div class="card shadow mb-4 filter-periode-card">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="filter-periode-label">Filter per Bulan</div>
                                    <form method="GET" class="form-inline">
                                        <input type="hidden" name="filter" value="bulan">
                                        <input type="month" name="bulan" class="form-control form-control-sm mr-2"
                                            value="<?= htmlspecialchars($filterMode === 'bulan' ? $filterBulanInput : date('Y-m')) ?>">
                                        <button class="btn btn-sm btn-primary" type="submit">Terapkan</button>
                                    </form>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="filter-periode-label">Filter per Hari</div>
                                    <form method="GET" class="form-inline">
                                        <input type="hidden" name="filter" value="hari">
                                        <input type="date" name="tanggal" class="form-control form-control-sm mr-2"
                                            value="<?= htmlspecialchars($filterMode === 'hari' ? $filterHariInput : date('Y-m-d')) ?>">
                                        <button class="btn btn-sm btn-primary" type="submit">Terapkan</button>
                                    </form>
                                </div>
                                <div class="col-md-4 text-md-right">
                                    <div class="filter-periode-label">Menampilkan Data</div>
                                    <span class="filter-mode-badge"><?= htmlspecialchars($labelPeriode) ?></span>
                                    <?php if ($filterMode !== 'semua') : ?>
                                        <div class="mt-1"><a href="berita.php" class="small">&larr; Reset ke Semua Waktu</a></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                DIAJUKAN</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jmlDiajukan ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                DIPROSES</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jmlDiproses ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                DITANGGAPI</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jmlDitanggapi ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                SELESAI</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jmlSelesai ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Pengaduan -->
                    <div>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-dark">Daftar Pengaduan</h6>
                                <span class="small text-muted"><?= $filterMode === 'semua' ? '5 berita terbaru' : ('Periode: ' . htmlspecialchars($labelPeriode)) ?></span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"
                                        data-search-label="Cari pengaduan:"
                                        data-length-label="Tampilkan _MENU_ data"
                                        data-info-label="Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                                        data-prev-label="Previous"
                                        data-next-label="Next">
                                        <thead>
                                            <tr>
                                                <th>No.Tiket</th>
                                                <th>Pengadu</th>
                                                <th>Judul Pengaduan</th>
                                                <th>Kategori</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>No. Tiket</th>
                                                <th>Pengadu</th>
                                                <th>Judul Pengaduan</th>
                                                <th>Kategori</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php if (empty($daftarPengaduan)) : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Belum ada pengaduan masuk.</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($daftarPengaduan as $p) :
                                                    $statusData  = $statusInfo[$p['status']] ?? ['label' => $p['status'], 'badge' => 'badge-secondary'];
                                                    $kategoriTxt = $kategoriLabel[$p['kategori']] ?? $p['kategori'];
                                                    $thread      = $threadByPengaduan[$p['id']] ?? [];
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($p['no_tiket']) ?></td>
                                                        <td>
                                                            <?= htmlspecialchars($p['nama']) ?>
                                                            <div class="text-muted small"><?= htmlspecialchars($p['email'] ?? '-') ?></div>
                                                        </td>
                                                        <td><?= htmlspecialchars($p['judul']) ?></td>
                                                        <td><?= htmlspecialchars($kategoriTxt) ?></td>
                                                        <td><?= date('d-m-Y', strtotime($p['created_at'])) ?></td>
                                                        <td>
                                                            <span class="badge <?= $statusData['badge'] ?>"><?= htmlspecialchars($statusData['label']) ?></span>
                                                        </td>
                                                        <td>
                                                            <button style="margin: 7px; padding: 0px 16px;" class="btn btn-sm btn-primary"
                                                                onclick="showDetailPengaduan(this)"
                                                                data-id="<?= (int)$p['id'] ?>"
                                                                data-tiket="<?= htmlspecialchars($p['no_tiket']) ?>"
                                                                data-nama="<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>"
                                                                data-email="<?= htmlspecialchars($p['email'] ?? '-', ENT_QUOTES) ?>"
                                                                data-telepon="<?= htmlspecialchars($p['telepon'] ?? '-', ENT_QUOTES) ?>"
                                                                data-judul="<?= htmlspecialchars($p['judul'], ENT_QUOTES) ?>"
                                                                data-kategori="<?= htmlspecialchars($kategoriTxt, ENT_QUOTES) ?>"
                                                                data-tanggal="<?= date('d-m-Y', strtotime($p['created_at'])) ?>"
                                                                data-status="<?= htmlspecialchars($p['status']) ?>"
                                                                data-statuslabel="<?= htmlspecialchars($statusData['label']) ?>"
                                                                data-status-class="<?= $statusData['badge'] ?>"
                                                                data-lampiran="<?= htmlspecialchars($p['lampiran'] ?: '-', ENT_QUOTES) ?>"
                                                                >Detail</button>

                                                            <textarea id="isi-data-<?= (int)$p['id'] ?>" style="display:none;"><?= htmlspecialchars($p['isi'] ?? '') ?></textarea>
                                                            <textarea id="thread-data-<?= (int)$p['id'] ?>" style="display:none;"><?= htmlspecialchars(json_encode($thread), ENT_QUOTES) ?></textarea>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2020</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Modal Detail Pengaduan -->
    <div class="modal fade" id="detailPengaduanModal" tabindex="-1" role="dialog"
        aria-labelledby="detailPengaduanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" action="proses_balas_pengaduan.php">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailPengaduanModalLabel">Detail Pengaduan &mdash; <span id="dp-tiket"></span></h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <input type="hidden" name="id" id="dp-id">

                        <div class="row no-gutters" style="gap: 10px 0;">
                            <div class="col-md-3 pr-md-2 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Nama Pengadu</div>
                                    <div class="val" id="dp-nama"></div>
                                </div>
                            </div>
                            <div class="col-md-3 px-md-1 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Email</div>
                                    <div class="val" id="dp-email" style="font-size:0.8rem;"></div>
                                </div>
                            </div>
                            <div class="col-md-3 px-md-1 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Telepon</div>
                                    <div class="val" id="dp-telepon"></div>
                                </div>
                            </div>
                            <div class="col-md-3 pl-md-2 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Kategori</div>
                                    <div class="val" id="dp-kategori" style="font-size:0.8rem;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row no-gutters mt-1">
                            <div class="col-md-6 pr-md-2 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Tanggal Masuk</div>
                                    <div class="val" id="dp-tanggal"></div>
                                </div>
                            </div>
                            <div class="col-md-6 pl-md-2 mb-2">
                                <div class="info-box">
                                    <div class="lbl">Status Saat Ini</div>
                                    <div class="val"><span class="badge" id="dp-status-badge"></span></div>
                                </div>
                            </div>
                        </div>

                        <div class="section-title">Judul Pengaduan</div>
                        <div class="isi-box" id="dp-judul" style="font-weight:700;"></div>

                        <div class="section-title">Isi Pengaduan</div>
                        <div class="isi-box" id="dp-isi"></div>

                        <div class="section-title">Lampiran</div>
                        <div id="dp-lampiran-wrap"></div>

                        <div class="section-title">Riwayat Tanggapan</div>
                        <div id="dp-thread"></div>

                        <div class="section-title">Kirim Tanggapan</div>
                        <textarea class="form-control" name="isi_tanggapan" id="dp-reply" rows="3" placeholder="Tulis tanggapan untuk pengadu..."></textarea>

                        <div class="form-group mt-3 mb-0">
                            <label class="font-weight-bold small mb-1">Ubah Status</label>
                            <select class="form-control" name="status" id="dp-status-select">
                                <option value="diajukan">Diajukan</option>
                                <option value="diproses">Diproses</option>
                                <option value="ditanggapi">Ditanggapi</option>
                                <option value="ditutup">Selesai</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Tutup</button>
                        <button class="btn btn-primary" type="submit">Kirim Tanggapan &amp; Perbarui Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="../login/login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>
    <script src="../js/demo/chart-bar-demo.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>

    <!-- Script Detail Pengaduan -->
    <script>
        function showDetailPengaduan(btn) {
            var d = btn.dataset;

            document.getElementById('dp-id').value = d.id;
            document.getElementById('dp-tiket').textContent = d.tiket;
            document.getElementById('dp-nama').textContent = d.nama;
            document.getElementById('dp-email').textContent = d.email;
            document.getElementById('dp-telepon').textContent = d.telepon;
            document.getElementById('dp-kategori').textContent = d.kategori;
            document.getElementById('dp-tanggal').textContent = d.tanggal;
            document.getElementById('dp-judul').textContent = d.judul;

            var isiEl = document.getElementById('isi-data-' + d.id);
            document.getElementById('dp-isi').textContent = isiEl ? isiEl.value : '';

            // Status badge & dropdown
            var badge = document.getElementById('dp-status-badge');
            badge.textContent = d.statuslabel;
            badge.className = 'badge ' + d.statusClass;
            document.getElementById('dp-status-select').value = d.status;

            // Lampiran
            var lampiranWrap = document.getElementById('dp-lampiran-wrap');
            if (d.lampiran && d.lampiran !== '-') {
                lampiranWrap.innerHTML = '<span class="lampiran-chip"><i class="fas fa-paperclip"></i> ' + d.lampiran + '</span>';
            } else {
                lampiranWrap.innerHTML = '<span class="no-thread">Tidak ada lampiran.</span>';
            }

            // Riwayat tanggapan
            var threadWrap = document.getElementById('dp-thread');
            threadWrap.innerHTML = '';
            var threadEl = document.getElementById('thread-data-' + d.id);
            var thread = [];
            try { thread = JSON.parse(threadEl ? threadEl.value : '[]'); } catch (e) { thread = []; }

            if (thread.length === 0) {
                threadWrap.innerHTML = '<div class="no-thread">Belum ada tanggapan untuk pengaduan ini.</div>';
            } else {
                thread.forEach(function (item) {
                    var el = document.createElement('div');
                    el.className = 'thread-item';
                    var namaAdmin = document.createElement('b');
                    namaAdmin.textContent = item.admin;
                    var waktu = document.createElement('span');
                    waktu.textContent = item.waktu;
                    var head = document.createElement('div');
                    head.className = 't-head';
                    head.appendChild(namaAdmin);
                    head.appendChild(waktu);
                    var body = document.createElement('div');
                    body.className = 't-body';
                    body.textContent = item.isi;
                    el.appendChild(head);
                    el.appendChild(body);
                    threadWrap.appendChild(el);
                });
            }

            // Reset kolom balasan
            document.getElementById('dp-reply').value = '';

            $('#detailPengaduanModal').modal('show');
        }

        // Notifikasi otomatis hilang setelah beberapa detik + bersihkan URL
        (function () {
            var alertBox = document.getElementById('notifAlert');
            if (alertBox) {
                setTimeout(function () {
                    $(alertBox).alert('close');
                }, 4000);
            }
            if (window.history.replaceState && window.location.search.indexOf('notif=') !== -1) {
                var cleanUrl = window.location.pathname;
                window.history.replaceState(null, '', cleanUrl);
            }
        })();
    </script>
</body>

</html>
