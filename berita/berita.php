<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include __DIR__ . '/../koneksi.php';

$kategoriList = [];
if ($r = mysqli_query($koneksi, "SELECT id, nama FROM kategori_berita ORDER BY nama")) {
    while ($row = mysqli_fetch_assoc($r)) {
        $kategoriList[] = $row;
    }
}

$filterMode = $_GET['filter'] ?? 'semua';
if (!in_array($filterMode, ['semua', 'bulan', 'hari'], true)) {
    $filterMode = 'semua';
}

$filterBulanInput = $_GET['bulan'] ?? date('Y-m');
$filterHariInput  = $_GET['tanggal'] ?? date('Y-m-d');

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

$totalBerita = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM berita")) {
    $totalBerita = mysqli_fetch_assoc($r)['jml'];
}

$beritaTerbit = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM berita WHERE status = 'terbit'")) {
    $beritaTerbit = mysqli_fetch_assoc($r)['jml'];
}

$beritaDraft = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM berita WHERE status = 'draf'")) {
    $beritaDraft = mysqli_fetch_assoc($r)['jml'];
}

$kategoriAktif = count($kategoriList);

$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Berita berhasil ditambahkan.',      'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Berita berhasil diperbarui.',        'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Berita berhasil dihapus.',           'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Judul dan isi berita wajib diisi.',  'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan berita ke database.', 'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus berita.',            'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Data berita tidak ditemukan.',       'icon' => 'fa-exclamation-circle'],
];

$daftarBerita = [];
$sqlBerita = "SELECT b.id, b.judul, b.slug, b.gambar, b.status, b.kategori_id, b.isi, b.tanggal_publish, b.created_at,
                     k.nama AS kategori_nama
              FROM berita b
              LEFT JOIN kategori_berita k ON k.id = b.kategori_id
              ORDER BY b.created_at DESC";
if ($r = mysqli_query($koneksi, $sqlBerita)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $daftarBerita[] = $row;
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

    <title>Manajemen Berita - Disdik Sumenep</title>

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

        .read-more-link {
            color: #0066cc !important;
        }

        #tambahBeritaModal .modal-header {
            background-color: #162F55;
            color: #fff;
        }

        #tambahBeritaModal .modal-header .close {
            color: #fff;
            opacity: 0.85;
            text-shadow: none;
        }

        .berita-thumb {
            width: 80px;
            height: 55px;
            object-fit: cover;
            border-radius: 4px;
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

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php" style="color: #fff !important;">
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
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Berita -->
            <li class="nav-item active">
                <a class="nav-link" href="berita.php">
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
                        <a class="collapse-item" href="../galeri/galeri_prestasi.php">Prestasi</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pengaduan -->
            <li class="nav-item">
                <a class="nav-link" href="../pengaduan/pengaduan.php">
                    <i class="fas fa-fw fa-exclamation-triangle"></i>
                    <span>Pengaduan</span></a>
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
                            <h1 class="h3 mb-0 text-gray-800">Manajemen Berita</h1>
                            <div class="text-muted small">Kelola artikel berita yang tampil di website Dinas Pendidikan Kabupaten Sumenep</div>
                        </div>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white" style="color: #fff !important;"
                            data-toggle="modal" data-target="#tambahBeritaModal">
                            <i class="fas fa-plus fa-sm text-white"></i>  Tambah Berita</a>
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

                        <!-- Total Berita -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                TOTAL BERITA</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalBerita ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dipublikasikan -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                DIPUBLIKASIKAN</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $beritaTerbit ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Draft -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">DRAFT
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?= $beritaDraft ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Berita -->
                    <div>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-dark">Daftar Berita</h6>
                                <span class="small text-muted"><?= $filterMode === 'semua' ? '5 berita terbaru' : ('Periode: ' . htmlspecialchars($labelPeriode)) ?></span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"
                                        data-search-label="Cari berita:"
                                        data-length-label="Tampilkan _MENU_ data"
                                        data-info-label="Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                                        data-prev-label="Previous"
                                        data-next-label="Next">
                                        <thead>
                                            <tr>
                                                <th>Gambar</th>
                                                <th>Judul</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Gambar</th>
                                                <th>Judul</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php if (empty($daftarBerita)) : ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">Belum ada berita. Klik "Tambah Berita" untuk menambahkan.</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($daftarBerita as $b) : ?>
                                                    <tr>
                                                        <td>
                                                            <?php if (!empty($b['gambar']) && file_exists(__DIR__ . '/../img/berita/' . $b['gambar'])) : ?>
                                                                <img class="berita-thumb"
                                                                     src="../img/berita/<?= htmlspecialchars($b['gambar']) ?>"
                                                                     alt="">
                                                            <?php else : ?>
                                                                <img class="berita-thumb"
                                                                     src="../img/undraw_posting_photo.svg"
                                                                     alt="">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?= htmlspecialchars($b['judul']) ?>
                                                            <p class="mb-0"><a class="read-more-link" href="../berita/<?= htmlspecialchars($b['slug']) ?>" target="_blank">Baca Selengkapnya</a></p>
                                                        </td>
                                                        <td><?= htmlspecialchars($b['kategori_nama'] ?? '-') ?></td>
                                                        <td>
                                                            <?php if ($b['status'] === 'terbit') : ?>
                                                                <span class="badge badge-success">Dipublikasikan</span>
                                                            <?php else : ?>
                                                                <span class="badge badge-warning">Draft</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?= $b['tanggal_publish']
                                                                ? date('d-m-Y', strtotime($b['tanggal_publish']))
                                                                : date('d-m-Y', strtotime($b['created_at'])) ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex" style="gap:6px;">
                                                                <button type="button" class="btn btn-sm btn-primary" title="Edit"
                                                                    onclick="openEditBerita(this)"
                                                                    data-id="<?= (int)$b['id'] ?>"
                                                                    data-judul="<?= htmlspecialchars($b['judul'], ENT_QUOTES) ?>"
                                                                    data-kategori="<?= (int)($b['kategori_id'] ?? 0) ?>"
                                                                    data-status="<?= htmlspecialchars($b['status']) ?>"
                                                                    data-gambar="<?= htmlspecialchars($b['gambar'] ?? '') ?>">
                                                                    <i class="fas fa-pen"></i>
                                                                </button>

                                                                <button class="btn btn-danger btn-sm btn-hapus ml-1"
                                                                    data-id="<?= $b['id'] ?>"
                                                                    data-judul="<?= htmlspecialchars($b['judul'], ENT_QUOTES) ?>"
                                                                    title="Hapus" style="padding:4px 8px;">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>

                                                            <textarea id="isi-data-<?= (int)$b['id'] ?>" style="display:none;"><?= htmlspecialchars($b['isi'] ?? '') ?></textarea>
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
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Dinas Pendidikan Kabupaten Sumenep <?= date('Y') ?></span>
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

    <!-- Modal Tambah Berita -->
    <div class="modal fade" id="tambahBeritaModal" tabindex="-1" role="dialog" aria-labelledby="tambahBeritaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahBeritaModalLabel">Tambah Berita</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="proses_tambah_berita.php" enctype="multipart/form-data">
                    <div class="modal-body">

                        <div class="form-group">
                            <label class="font-weight-bold small">Judul Berita</label>
                            <input type="text" class="form-control" name="judul"
                                placeholder="Contoh: Siswa SMPN 1 Sumenep Raih Juara OSN Matematika" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small d-flex justify-content-between align-items-center">
                                    Kategori
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;"
                                        onclick="bukaTambahKategori('tambah')" title="Tambah kategori baru">
                                        <i class="fas fa-plus"></i> Kategori Baru
                                    </button>
                                </label>
                                <select class="form-control" name="kategori_id" id="kategori-tambah">
                                    <option value="">- Pilih Kategori -</option>
                                    <?php foreach ($kategoriList as $k) : ?>
                                        <option value="<?= (int)$k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Status</label>
                                <select class="form-control" name="status">
                                    <option value="draf">Simpan sebagai Draft</option>
                                    <option value="terbit">Terbitkan Sekarang</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">Gambar Sampul</label>
                            <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp">
                            <small class="form-text text-muted">Format JPG/PNG/WEBP, opsional.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Isi Berita</label>
                            <textarea class="form-control" name="isi" rows="6" placeholder="Tulis isi berita di sini..." required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                        <button class="btn btn-primary" type="submit">Simpan Berita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Berita -->
    <div class="modal fade" id="editBeritaModal" tabindex="-1" role="dialog" aria-labelledby="editBeritaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header btn-primary">
                    <h5 class="modal-title" id="editBeritaModalLabel">Edit Berita</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="proses_edit_berita.php" enctype="multipart/form-data">
                    <div class="modal-body">

                        <input type="hidden" name="id" id="edit-id">

                        <div class="form-group">
                            <label class="font-weight-bold small">Judul Berita</label>
                            <input type="text" class="form-control" name="judul" id="edit-judul" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small d-flex justify-content-between align-items-center">
                                    Kategori
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;"
                                        onclick="bukaTambahKategori('edit')" title="Tambah kategori baru">
                                        <i class="fas fa-plus"></i> Kategori Baru
                                    </button>
                                </label>
                                <select class="form-control" name="kategori_id" id="edit-kategori">
                                    <option value="">- Pilih Kategori -</option>
                                    <?php foreach ($kategoriList as $k) : ?>
                                        <option value="<?= (int)$k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Status</label>
                                <select class="form-control" name="status" id="edit-status">
                                    <option value="draf">Simpan sebagai Draft</option>
                                    <option value="terbit">Terbitkan Sekarang</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">Gambar Sampul</label>
                            <div class="d-flex align-items-center mb-2" style="gap:12px;">
                                <img id="edit-gambar-preview" src="../img/undraw_posting_photo.svg" alt=""
                                    style="width:70px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #eee0e3;">
                                <small class="text-muted">Gambar saat ini. Unggah file baru untuk menggantinya.</small>
                            </div>
                            <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp">
                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Isi Berita</label>
                            <textarea class="form-control" name="isi" id="edit-isi" rows="6" required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                        <button class="btn btn-primary" type="submit">Perbarui Berita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#e74a3b;">
                    <h5 class="modal-title text-white"><i class="fas fa-trash mr-2"></i>Hapus Berita</h5>
                    <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <p>Yakin ingin menghapus berita:</p>
                    <p class="font-weight-bold" id="hapusJudul"></p>
                    <p class="text-muted small">Tindakan ini tidak bisa dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <form action="hapus_berita.php" method="POST">
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

    <!-- Modal Tambah Kategori Baru -->
    <div class="modal fade" id="tambahKategoriModal" tabindex="-1" role="dialog" aria-labelledby="tambahKategoriModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahKategoriModalLabel">Tambah Kategori Baru</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="kategoriError" class="alert alert-danger py-2 small d-none"></div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Nama Kategori</label>
                        <input type="text" class="form-control" id="kategoriBaruNama" placeholder="Contoh: Prestasi Siswa">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="button" id="btnSimpanKategori" onclick="simpanKategoriBaru()">
                        <i class="fas fa-save mr-1"></i> Simpan Kategori
                    </button>
                </div>
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
                    <a class="btn btn-primary" href="../login/logout.php">Logout</a>
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

    <!-- Script Edit Berita & Notifikasi -->
    <script>
        function openEditBerita(btn) {
            var d = btn.dataset;

            document.getElementById('edit-id').value = d.id;
            document.getElementById('edit-judul').value = d.judul;
            document.getElementById('edit-kategori').value = d.kategori || '';
            document.getElementById('edit-status').value = d.status;

            var isiEl = document.getElementById('isi-data-' + d.id);
            document.getElementById('edit-isi').value = isiEl ? isiEl.value : '';

            var preview = document.getElementById('edit-gambar-preview');
            preview.src = d.gambar ? '../' + d.gambar : '../img/undraw_posting_photo.svg';

            $('#editBeritaModal').modal('show');
        }

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

        
        var sumberKategoriModal = null;

        function bukaTambahKategori(sumber) {
            sumberKategoriModal = sumber; // 'tambah' atau 'edit'

            document.getElementById('kategoriBaruNama').value = '';
            var errBox = document.getElementById('kategoriError');
            errBox.classList.add('d-none');
            errBox.textContent = '';

            // Sembunyikan modal asal dulu, lalu tampilkan modal kategori
            var modalAsalId = (sumber === 'edit') ? '#editBeritaModal' : '#tambahBeritaModal';
            $(modalAsalId).modal('hide');
            $('#tambahKategoriModal').modal('show');
        }

        function simpanKategoriBaru() {
            var nama = document.getElementById('kategoriBaruNama').value.trim();
            var errBox = document.getElementById('kategoriError');
            var btn = document.getElementById('btnSimpanKategori');

            if (nama === '') {
                errBox.textContent = 'Nama kategori wajib diisi.';
                errBox.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';

            var formData = new FormData();
            formData.append('nama', nama);

            fetch('tambah_kategori.php', {
                method: 'POST',
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Kategori';

                    if (!data.success) {
                        errBox.textContent = data.message || 'Gagal menyimpan kategori.';
                        errBox.classList.remove('d-none');
                        return;
                    }

                    // Tambahkan opsi baru ke kedua dropdown kategori (tambah & edit)
                    ['kategori-tambah', 'edit-kategori'].forEach(function (selectId) {
                        var select = document.getElementById(selectId);
                        if (!select) return;
                        var opt = document.createElement('option');
                        opt.value = data.id;
                        opt.textContent = data.nama;
                        select.appendChild(opt);
                    });

                    // Pilih otomatis kategori baru di modal asal
                    var selectAsalId = (sumberKategoriModal === 'edit') ? 'edit-kategori' : 'kategori-tambah';
                    document.getElementById(selectAsalId).value = data.id;

                    // Tutup modal kategori, tampilkan lagi modal asal
                    $('#tambahKategoriModal').modal('hide');
                    var modalAsalId = (sumberKategoriModal === 'edit') ? '#editBeritaModal' : '#tambahBeritaModal';
                    $(modalAsalId).modal('show');
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Kategori';
                    errBox.textContent = 'Terjadi kesalahan koneksi. Coba lagi.';
                    errBox.classList.remove('d-none');
                });
        }

        $('.btn-hapus').on('click', function () {
            $('#hapusId').val($(this).data('id'));
            $('#hapusJudul').text($(this).data('judul'));
            $('#modalHapus').modal('show');
        });
    </script>

</body>
</html>