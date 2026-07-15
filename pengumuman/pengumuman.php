<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include __DIR__ . '/../koneksi.php';

$filterMode = $_GET['filter'] ?? 'semua';
if (!in_array($filterMode, ['semua', 'bulan', 'hari'], true)) {
    $filterMode = 'semua';
}

$filterBulanInput = $_GET['bulan'] ?? date('Y-m');
$filterHariInput  = $_GET['tanggal'] ?? date('Y-m-d');

$hariList  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
$bulanList = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

$whereBerita     = '';
$wherePengumuman = "WHERE status = 'terbit'";
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

$totalPengumuman = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengumuman")) {
    $totalPengumuman = mysqli_fetch_assoc($r)['jml'];
}
$pengumumanTerbit = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengumuman WHERE status = 'terbit'")) {
    $pengumumanTerbit = mysqli_fetch_assoc($r)['jml'];
}
$pengumumanDraf = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengumuman WHERE status = 'draf'")) {
    $pengumumanDraf = mysqli_fetch_assoc($r)['jml'];
}

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Pengumuman berhasil ditambahkan.',      'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Pengumuman berhasil diperbarui.',        'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Pengumuman berhasil dihapus.',           'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Judul dan isi pengumuman wajib diisi.',  'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan pengumuman.',            'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus pengumuman.',            'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Data pengumuman tidak ditemukan.',       'icon' => 'fa-exclamation-circle'],
];

$editData = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $res = mysqli_query($koneksi, "SELECT * FROM pengumuman WHERE id = $editId LIMIT 1");
    if ($res) $editData = mysqli_fetch_assoc($res);
}

$daftarPengumuman = [];
$sql = "SELECT * FROM pengumuman ORDER BY created_at DESC";
if ($r = mysqli_query($koneksi, $sql)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $daftarPengumuman[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kelola Pengumuman - Disdik Sumenep</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
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

        .btn-primary:hover {
            background-color: #162F55 !important; 
            border-color: #162F55 !important;
        }

        .stat-card {
            border-left: 4px solid #162F55;
        }

        .badge-terbit {
            background: #d4edda; 
            color: #155724; 
            padding: 4px 10px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: 500;
        }

        .badge-draf {
            background: #f8d7da; 
            color: #721c24;
            padding: 4px 10px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: 500;
        }

        .img-thumb {
            width: 70px; 
            height: 50px; 
            object-fit: cover; 
            border-radius: 6px; 
            background: #f0f0f0;
        }

        .img-thumb-placeholder {
            width: 70px; 
            height: 50px; 
            background: #e9ecef; 
            border-radius: 6px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: #adb5bd; 
            font-size: 20px; 
        }

        .form-panel {
            border-left: 4px solid #162F55; 
        }

        #previewGambar {
            max-width: 100%; 
            max-height: 200px; 
            object-fit: cover; 
            border-radius: 8px; 
            display: none; 
            margin-top: 8px; 
        }

        #previewGambarEdit {
            max-width: 100%; 
            max-height: 200px; 
            object-fit: cover; 
            border-radius: 8px; 
            margin-top: 8px; 
        }

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
    <div id="wrapper">

        <!-- SIDEBAR -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
                <div class="sidebar-brand-icon">
                    <i><img src="../img/Logo1.png" alt="" style="width:60px;height:60px;object-fit:contain;"></i>
                </div>
                <div class="d-flex flex-column" style="color:#fff !important;">
                    <div style="font-size:0.7rem;">Dinas Pendidikan</div>
                    <div style="font-size:0.5rem;"><i>Kabupaten Sumenep</i></div>
                </div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../berita/berita.php">
                    <i class="fas fa-fw fa-newspaper"></i>
                    <span>Berita</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="../pengumuman/pengumuman.php">
                    <i class="fas fa-fw fa-bullhorn"></i>
                    <span>Pengumuman</span>
                </a>
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
                        <a class="collapse-item" href="../galeri/galeri_prestasi.php">Prestasi</a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../pengaduan/pengaduan.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Pengaduan</span>
                </a>
            </li>

            <hr class="sidebar-divider">

            <div class="sidebar-heading">Instansi</div>

            <li class="nav-item">
                <a class="nav-link" href="../profil/profil.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../pegawai/pegawai.php">
                    <i class="fas fa-fw fa-user-friends"></i>
                    <span>Pegawai</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="../bidang/bidang.php">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Bidang</span>
                </a>
            </li>

            <!-- Nav Item - Kegiatan -->
            <li class="nav-item">
                <a class="nav-link" href="../kegiatan/kegiatan.php">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Kegiatan</span></a>
            </li>

            <!-- Nav Item - Sakip -->
            <li class="nav-item">
                <a class="nav-link" href="../sakip/sakip.php">
                    <i class="fas fa-fw fa-file-contract"></i>
                    <span>SAKIP</span></a>
            </li>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- END SIDEBAR -->

        <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">

            <!-- TOPBAR -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3"><i class="fa fa-bars"></i></button>
                <ul class="navbar-nav ml-auto">
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?>
                            </span>
                            <img class="img-profile rounded-circle" src="../img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- END TOPBAR -->

            <!-- PAGE CONTENT -->
            <div class="container-fluid">

                <!-- Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">Kelola Pengumuman</h1>
                        <div class="text-muted small">Kelola pengumuman yang tampil di website Dinas Pendidikan Kabupaten Sumenep</div>
                    </div>
                    <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#modalTambah">
                        <i class="fas fa-plus fa-sm text-white"></i> Tambah Pengumuman
                    </button>
                </div>

                <!-- Notifikasi -->
                <?php if ($notif && isset($notifMsg[$notif])): ?>
                <div id="notifAlert" class="alert alert-<?= $notifMsg[$notif]['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas <?= $notifMsg[$notif]['icon'] ?> mr-2"></i>
                    <?= $notifMsg[$notif]['text'] ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
                </div>
                <?php endif; ?>
                
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
                                    <div class="mt-1"><a href="pengumuman.php" class="small">&larr; Reset ke Semua Waktu</a></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="row mb-4">
                    <div class="col-xl-4 col-md-4 mb-4">
                        <div class="card shadow h-100 py-2 stat-card">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #162F55;">Total Pengumuman</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPengumuman ?></div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-bullhorn fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 mb-4">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #1cc88a;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Terbit</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pengumumanTerbit ?></div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-4 mb-4">
                        <div class="card shadow h-100 py-2" style="border-left:4px solid #e74a3b;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Draf</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pengumumanDraf ?></div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="border-left:4px solid #162F55;">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-list mr-2" style="color: #162F55;"></i>Daftar Pengumuman
                        </h6>
                        <span class="small text-muted"><?= $filterMode === 'semua' ? '5 pengumuman terbaru' : ('Periode: ' . htmlspecialchars($labelPeriode)) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0"
                                data-search-label="Cari pengumuman:"
                                data-length-label="Tampilkan _MENU_ data"
                                data-info-label="Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                                data-prev-label="Sebelumnya"
                                data-next-label="Berikutnya">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="60">#</th>
                                        <th width="90">Gambar</th>
                                        <th>Judul Pengumuman</th>
                                        <th width="110">Status</th>
                                        <th width="120">Tanggal</th>
                                        <th width="130" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($daftarPengumuman)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Belum ada pengumuman. Klik <strong>+ Tambah Pengumuman</strong> untuk menambahkan.
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach ($daftarPengumuman as $i => $row): ?>
                                    <tr>
                                        <td class="text-center align-middle text-muted small"><?= $i + 1 ?></td>
                                        <td class="text-center align-middle">
                                            <?php if (!empty($row['gambar']) && file_exists(__DIR__ . '/../img/pengumuman/' . $row['gambar'])): ?>
                                                <img src="../img/pengumuman/<?= htmlspecialchars($row['gambar']) ?>"
                                                    alt="Gambar" class="img-thumb">
                                            <?php else: ?>
                                                <div class="img-thumb-placeholder mx-auto">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold text-gray-800"><?= htmlspecialchars($row['judul']) ?></div>
                                            <?php if (!empty($row['isi'])): ?>
                                            <div class="text-muted small mt-1" style="max-width:400px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                                                <?= htmlspecialchars(strip_tags($row['isi'])) ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php if ($row['status'] === 'terbit'): ?>
                                                <span class="badge-terbit">Terbit</span>
                                            <?php else: ?>
                                                <span class="badge-draf">Draf</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center small text-muted">
                                            <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <button class="btn btn-sm btn-edit btn-primary"
                                                data-id="<?= $row['id'] ?>"
                                                data-judul="<?= htmlspecialchars($row['judul'], ENT_QUOTES) ?>"
                                                data-isi="<?= htmlspecialchars($row['isi'], ENT_QUOTES) ?>"
                                                data-tanggal="<?= $row['tanggal'] ?>"
                                                data-status="<?= $row['status'] ?>"
                                                data-gambar="<?= htmlspecialchars($row['gambar'] ?? '') ?>"
                                                title="Edit" style="padding:4px 8px;">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm btn-hapus ml-1"
                                                data-id="<?= $row['id'] ?>"
                                                data-judul="<?= htmlspecialchars($row['judul'], ENT_QUOTES) ?>"
                                                title="Hapus" style="padding:4px 8px;">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
            <!-- END PAGE CONTENT -->
        </div>

        <!-- FOOTER -->
        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Dinas Pendidikan Kabupaten Sumenep <?= date('Y') ?></span>
                </div>
            </div>
        </footer>

        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #162F55;">
                    <h5 class="modal-title text-white" id="modalTambahLabel">
                        <i class="fas fa-plus-circle mr-2"></i>Tambah Pengumuman
                    </h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="proses_tambah_pengumuman.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Judul Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Masukkan judul pengumuman..." required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Isi Pengumuman <span class="text-danger">*</span></label>
                            <textarea name="isi" class="form-control" rows="5" placeholder="Tulis isi pengumuman..." required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="terbit">Terbit</option>
                                        <option value="draf">Draf</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Gambar <span class="text-muted font-weight-normal">(opsional)</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="inputGambarTambah" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="inputGambarTambah">Pilih gambar...</label>
                            </div>
                            <small class="text-muted">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                            <img id="previewGambar" src="" alt="Preview">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i>Simpan Pengumuman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header btn-primary">
                    <h5 class="modal-title text-white" id="modalEditLabel">Edit Pengumuman</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form action="proses_edit_pengumuman.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <input type="hidden" name="gambar_lama" id="editGambarLama">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Judul Pengumuman <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="editJudul" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Isi Pengumuman <span class="text-danger">*</span></label>
                            <textarea name="isi" id="editIsi" class="form-control" rows="5" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" id="editTanggal" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Status</label>
                                    <select name="status" id="editStatus" class="form-control">
                                        <option value="terbit">Terbit</option>
                                        <option value="draf">Draf</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Ganti Gambar <span class="text-muted font-weight-normal">(kosongkan jika tidak ingin ganti)</span></label>
                            <div id="gambarLamaContainer" class="mb-2" style="display:none;">
                                <small class="text-muted">Gambar saat ini:</small><br>
                                <img id="previewGambarEdit" src="" alt="Gambar saat ini" style="max-height:150px;border-radius:6px;">
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="inputGambarEdit" name="gambar" accept="image/*">
                                <label class="custom-file-label" for="inputGambarEdit">Pilih gambar baru...</label>
                            </div>
                            <small class="text-muted">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#e74a3b;">
                    <h5 class="modal-title text-white"><i class="fas fa-trash mr-2"></i>Hapus Pengumuman</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p>Yakin ingin menghapus pengumuman:</p>
                    <p class="font-weight-bold" id="hapusJudul"></p>
                    <p class="text-muted small">Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <form action="hapus_pengumuman.php" method="POST">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" id="hapusId">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Logout</h5>
                    <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">Apakah Anda yakin ingin keluar dari sistem?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="../login/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>

    <script>
    document.getElementById('inputGambarTambah').addEventListener('change', function () {
        const file = this.files[0];
        const label = this.nextElementSibling;
        const preview = document.getElementById('previewGambar');
        if (file) {
            label.textContent = file.name;
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('inputGambarEdit').addEventListener('change', function () {
        const file = this.files[0];
        const label = this.nextElementSibling;
        if (file) {
            label.textContent = file.name;
        }
    });

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id      = this.dataset.id;
            const judul   = this.dataset.judul;
            const isi     = this.dataset.isi;
            const tanggal = this.dataset.tanggal;
            const status  = this.dataset.status;
            const gambar  = this.dataset.gambar;

            document.getElementById('editId').value        = id;
            document.getElementById('editJudul').value     = judul;
            document.getElementById('editIsi').value       = isi;
            document.getElementById('editTanggal').value   = tanggal;
            document.getElementById('editStatus').value    = status;
            document.getElementById('editGambarLama').value = gambar;

            const gambarContainer = document.getElementById('gambarLamaContainer');
            const gambarPreview   = document.getElementById('previewGambarEdit');
            if (gambar) {
                gambarContainer.style.display = 'block';
                gambarPreview.src = '../img/pengumuman/' + gambar;
            } else {
                gambarContainer.style.display = 'none';
            }

            $('#modalEdit').modal('show');
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('hapusId').value    = this.dataset.id;
            document.getElementById('hapusJudul').textContent = '"' + this.dataset.judul + '"';
            $('#modalHapus').modal('show');
        });
    });

    $('.custom-file-input').on('change', function () {
        const fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').text(fileName || 'Pilih file...');
    });

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
