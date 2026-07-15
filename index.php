<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include 'koneksi.php';

$hariList  = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
$bulanList = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggalHariIni = $hariList[date('w')] . ', ' . date('j') . ' ' . $bulanList[(int)date('n')] . ' ' . date('Y');

$kategoriLabel = [
    'sarana_prasarana' => 'Sarana & Prasarana',
    'kepegawaian'      => 'Kepegawaian',
    'pelayanan'        => 'Pelayanan',
    'lainnya'          => 'Lainnya',
];
$statusBadge = [
    'diajukan'   => ['label' => 'Diajukan',   'class' => 'badge-warning'],
    'diproses'   => ['label' => 'Diproses',   'class' => 'badge-info'],
    'ditanggapi' => ['label' => 'Ditanggapi', 'class' => 'badge-primary'],
    'ditutup'    => ['label' => 'Selesai',    'class' => 'badge-success'],
];

$filterMode = $_GET['filter'] ?? 'semua'; // semua | bulan | hari
if (!in_array($filterMode, ['semua', 'bulan', 'hari'], true)) {
    $filterMode = 'semua';
}

$filterBulanInput = $_GET['bulan'] ?? date('Y-m');
$filterHariInput  = $_GET['tanggal'] ?? date('Y-m-d');

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

$totalBerita = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM berita $whereBerita")) {
    $totalBerita = mysqli_fetch_assoc($r)['jml'];
}

$pengumumanAktif = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengumuman $wherePengumuman")) {
    $pengumumanAktif = mysqli_fetch_assoc($r)['jml'];
}

$pengaduanBaru = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'diajukan' AND $wherePengaduan")) {
    $pengaduanBaru = mysqli_fetch_assoc($r)['jml'];
}

$pengaduanSelesai = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pengaduan WHERE status = 'ditutup' AND $wherePengaduan")) {
    $pengaduanSelesai = mysqli_fetch_assoc($r)['jml'];
}

$batasTampil = ($filterMode === 'semua') ? ' LIMIT 5' : '';
$pengaduanTerbaru = [];
$sqlPengaduanTerbaru = "SELECT id, no_tiket, nama, kategori, status FROM pengaduan WHERE $wherePengaduan ORDER BY created_at DESC" . $batasTampil;
if ($r = mysqli_query($koneksi, $sqlPengaduanTerbaru)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $pengaduanTerbaru[] = $row;
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

    <title>Dashboard Admin - Disdik Sumenep</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon">
                    <i><img src="img/Logo1.png" alt="" style="width: 60px; height: 60px; object-fit: contain;"></i>
                </div>
                <div class="d-flex flex-column" style="color: #fff !important;">
                    <div style="font-size: 0.7rem;">Dinas Pendidikan</div>
                    <div style="font-size: 0.5rem;"><i>Kabupaten Sumenep</i></div>
                </div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Nav Item - Berita -->
            <li class="nav-item">
                <a class="nav-link" href="berita/berita.php">
                    <i class="fas fa-fw fa-newspaper"></i>
                    <span>Berita</span></a>
            </li>

            <!-- Nav Item - Pengumuman -->
            <li class="nav-item">
                <a class="nav-link" href="pengumuman/pengumuman.php">
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
                        <a class="collapse-item" href="galeri/galeri_foto.php">Foto</a>
                        <a class="collapse-item" href="galeri/galeri_prestasi.php">Prestasi</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - Pengaduan -->
            <li class="nav-item">
                <a class="nav-link" href="pengaduan/pengaduan.php">
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
                <a class="nav-link" href="profil/profil.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profil</span></a>
            </li>

            <!-- Nav Item - Pegawai -->
            <li class="nav-item">
                <a class="nav-link" href="pegawai/pegawai.php">
                    <i class="fas fa-fw fa-user-friends"></i>
                    <span>Pegawai</span></a>
            </li>

            <!-- Nav Item - Bidang -->
            <li class="nav-item">
                <a class="nav-link" href="bidang/bidang.php">
                    <i class="fas fa-fw fa-building"></i>
                    <span>Bidang</span></a>
            </li>

            <!-- Nav Item - Kegiatan -->
            <li class="nav-item">
                <a class="nav-link" href="kegiatan/kegiatan.php">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Kegiatan</span></a>
            </li>

            <!-- Nav Item - Sakip -->
            <li class="nav-item">
                <a class="nav-link" href="sakip/sakip.php">
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
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php" data-toggle="modal" data-target="#logoutModal">
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
                            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                            <div class="text-muted small"><?= $tanggalHariIni ?></div>
                        </div>
                    </div>

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
                                        <div class="mt-1"><a href="index.php" class="small">&larr; Reset ke Semua Waktu</a></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Total Berita -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Berita</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalBerita ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengumuman Aktif -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Pengumuman Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pengumumanAktif ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengaduan Baru -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Pengaduan Baru
                                            </div>
                                            <div class="row no-gutters align-items-center">
                                                <div class="col-auto">
                                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?= $pengaduanBaru ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pengaduan Selesai -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Pengaduan Selesai</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pengaduanSelesai ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaduan Masuk -->
                    <div >
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-dark">Pengaduan Masuk</h6>
                                <span class="small text-muted"><?= $filterMode === 'semua' ? '5 pengaduan terbaru' : ('Periode: ' . htmlspecialchars($labelPeriode)) ?></span>
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
                                                <th>No Tiket</th>
                                                <th>Pengadu</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th style="width:120px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>No Tiket</th>
                                                <th>Pengadu</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php if (empty($pengaduanTerbaru)) : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">Tidak ada pengaduan pada periode ini.</td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($pengaduanTerbaru as $p) :
                                                    $kat = $kategoriLabel[$p['kategori']] ?? $p['kategori'];
                                                    $st  = $statusBadge[$p['status']] ?? ['label' => $p['status'], 'class' => 'badge-secondary'];
                                                ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($p['no_tiket']) ?></td>
                                                        <td><?= htmlspecialchars($p['nama']) ?></td>
                                                        <td><?= htmlspecialchars($kat) ?></td>
                                                        <td><span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span></td>
                                                        <td>
                                                            <a href="unduh_laporan_pengaduan.php?id=<?= (int)$p['id'] ?>"
                                                               class="btn btn-sm btn-primary" style="color:#fff;" title="Unduh laporan PDF">
                                                                <i class="fas fa-file-download"></i> Unduh
                                                            </a>
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
                    <a class="btn btn-primary" href="login/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/datatables-demo.js"></script>

</body>

</html>
