<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// ==== Daftar bidang untuk dropdown ====
$daftarBidang = [];
if ($r = mysqli_query($koneksi, "SELECT id, nama FROM bidang ORDER BY nama")) {
    while ($row = mysqli_fetch_assoc($r)) $daftarBidang[] = $row;
}

// ==== Filter dari GET ====
$filterBidang = $_GET['bidang'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$whereParts = [];
if ($filterBidang !== '' && ctype_digit($filterBidang)) {
    $whereParts[] = "k.bidang_id = " . (int)$filterBidang;
}
if (in_array($filterStatus, ['draf', 'terbit'], true)) {
    $whereParts[] = "k.status = '" . mysqli_real_escape_string($koneksi, $filterStatus) . "'";
}
$whereSQL = count($whereParts) ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// ==== Pagination ====
$perPage     = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset      = ($currentPage - 1) * $perPage;

$totalDataFilter = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kegiatan k $whereSQL")) {
    $totalDataFilter = (int)mysqli_fetch_assoc($r)['jml'];
}
$totalHalaman = max(1, (int)ceil($totalDataFilter / $perPage));
if ($currentPage > $totalHalaman) {
    $currentPage = $totalHalaman;
    $offset = ($currentPage - 1) * $perPage;
}

// ==== Statistik ====
$totalKegiatan = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kegiatan")) $totalKegiatan = mysqli_fetch_assoc($r)['jml'];

$totalTerbit = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kegiatan WHERE status = 'terbit'")) $totalTerbit = mysqli_fetch_assoc($r)['jml'];

$totalDraft = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kegiatan WHERE status = 'draf'")) $totalDraft = mysqli_fetch_assoc($r)['jml'];

$totalBerlangsung = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kegiatan WHERE CURDATE() BETWEEN tanggal_mulai AND tanggal_selesai")) $totalBerlangsung = mysqli_fetch_assoc($r)['jml'];

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Kegiatan berhasil ditambahkan.',            'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Data kegiatan berhasil diperbarui.',          'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Kegiatan berhasil dihapus.',                  'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Bidang, judul, dan tanggal wajib diisi.',     'icon' => 'fa-exclamation-circle'],
    'gagal_tanggal' => ['type' => 'danger',  'text' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan data ke database.',           'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus kegiatan.',                   'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Data kegiatan tidak ditemukan.',              'icon' => 'fa-exclamation-circle'],
];

// ==== Daftar kegiatan (join ke bidang untuk nama) ====
$daftarKegiatan = [];
$sql = "SELECT k.id, k.judul, k.slug, k.deskripsi, k.gambar, k.tanggal_mulai, k.tanggal_selesai, k.status,
               k.bidang_id, b.nama AS bidang_nama
        FROM kegiatan k
        LEFT JOIN bidang b ON b.id = k.bidang_id
        $whereSQL
        ORDER BY k.tanggal_mulai DESC, k.created_at DESC
        LIMIT $perPage OFFSET $offset";
if ($r = mysqli_query($koneksi, $sql)) {
    while ($row = mysqli_fetch_assoc($r)) $daftarKegiatan[] = $row;
}

$bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
function formatTanggalIndo($tgl, $bulanIndo) {
    $ts = strtotime($tgl);
    return date('d', $ts) . ' ' . $bulanIndo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kegiatan - Disdik Sumenep</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0B1F3A; --navy-mid: #162F55; --navy-light: #1E3F70;
            --gold: #C89B3C; --gold-light: #E8BF6A; --cream: #F7F4EE;
        }

        a:not(.btn):not(.page-link):not(.dataTables_wrapper .dataTables_paginate a),
        .text-primary { color: inherit !important; }

        .sidebar .nav-link, .sidebar .collapse-item,
        .sidebar .sidebar-brand, .sidebar .sidebar-heading,
        .sidebar .nav-link span { color: white !important; }

        .bg-gradient-primary, .btn-primary,
        .sidebar .nav-item.active .nav-link,
        .page-item.active .page-link,
        .progress-bar.bg-info {
            background-color: #162F55 !important;
            border-color: #162F55 !important;
            background-image: none !important;
        }
        .btn-primary:hover { background-color: #0B1F3A !important; }

        #tambahKegiatanModal .modal-header,
        #editKegiatanModal .modal-header { background-color: #162F55; color: #fff; }
        #tambahKegiatanModal .modal-header .close,
        #editKegiatanModal .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }

        .filter-bar {
            background: #f0f4fa;
            border: 1px solid #d0daea;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 18px;
        }
        .filter-bar label { font-size: .78rem; font-weight: 700; color: #0B1F3A; margin-bottom: 4px; }
        .filter-bar .form-control { font-size: .85rem; border-color: #b8c8df; }
        .filter-bar .form-control:focus { border-color: #162F55; box-shadow: 0 0 0 .15rem rgba(22,47,85,.15); }

        .jumlah-hasil { font-size: .82rem; color: #6B7280; }

        .kegiatan-thumb { width: 70px; height: 48px; object-fit: cover; border-radius: 4px; }

        .badge-berlangsung {
            display:inline-block; padding:3px 9px; border-radius:20px;
            background:#e6f7ec; color:#1a7d3a; font-size:.7rem; font-weight:700;
        }

        .pagination .page-link { color: #162F55; }
        .pagination .page-item.active .page-link { background-color: #162F55; border-color: #162F55; color: #fff; }

        .sidebar.toggled .sidebar-brand-text {
            display: none !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

    <!-- SIDEBAR -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../dashboard/index.php">
            <div class="sidebar-brand-icon">
                <i><img src="../img/Logo1.png" alt="" style="width:60px;height:60px;object-fit:contain;"></i>
            </div>
            <div class="sidebar-brand-text d-flex flex-column" style="color:white;">
                <div style="font-size:.7rem;">Dinas Pendidikan</div>
                <div style="font-size:.5rem;"><i>Kabupaten Sumenep</i></div>
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

        <li class="nav-item">
            <a class="nav-link" href="../pengumuman/pengumuman.php">
                <i class="fas fa-fw fa-bullhorn"></i>
                <span>Pengumuman</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
                <i class="fas fa-fw fa-images"></i>
                <span>Galeri</span>
            </a>
            <div id="collapseTwo" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="../galeri/galeri_foto.php">Foto</a>
                    <a class="collapse-item" href="../galeri/galeri_video.php">Video</a>
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

        <li class="nav-item active">
            <a class="nav-link active" href="../kegiatan/kegiatan.php">
                <i class="fas fa-fw fa-calendar-check"></i>
                <span>Kegiatan</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="../sakip/sakip.php">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>SAKIP</span>
            </a>
        </li>
    </ul>

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
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- PAGE CONTENT -->
        <div class="container-fluid">

            <!-- Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Kegiatan per Bidang</h1>
                    <div class="text-muted small">Kelola kegiatan yang dilaksanakan oleh masing-masing bidang di Dinas Pendidikan Kabupaten Sumenep.</div>
                </div>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white"
                    data-toggle="modal" data-target="#tambahKegiatanModal">
                    <i class="fas fa-plus fa-sm text-white"></i> Tambah Kegiatan
                </a>
            </div>

            <!-- Notifikasi -->
            <?php if ($notif && isset($notifMsg[$notif])): ?>
            <div id="notifAlert" class="alert alert-<?= $notifMsg[$notif]['type'] ?> alert-dismissible fade show shadow-sm mb-4">
                <i class="fas <?= $notifMsg[$notif]['icon'] ?> mr-2"></i>
                <?= htmlspecialchars($notifMsg[$notif]['text']) ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Kegiatan</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalKegiatan ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-calendar-check fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Terbit</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalTerbit ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Draft</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDraft ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-file-alt fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Utama -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="border-left:4px solid #162F55;">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-calendar-check mr-2" style="color:#162F55;"></i>Daftar Kegiatan
                    </h6>
                </div>
                <div class="card-body">

                    <!-- FILTER BAR -->
                    <div class="filter-bar">
                        <form method="GET" action="kegiatan.php" id="formFilter">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-building mr-1"></i>Filter per Bidang</label>
                                    <select name="bidang" class="form-control" onchange="this.form.submit();">
                                        <option value="">-- Semua Bidang --</option>
                                        <?php foreach ($daftarBidang as $b): ?>
                                        <option value="<?= (int)$b['id'] ?>" <?= $filterBidang == $b['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($b['nama']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-toggle-on mr-1"></i>Status</label>
                                    <select name="status" class="form-control" onchange="this.form.submit();">
                                        <option value="">-- Semua Status --</option>
                                        <option value="terbit" <?= $filterStatus === 'terbit' ? 'selected' : '' ?>>Terbit</option>
                                        <option value="draf" <?= $filterStatus === 'draf' ? 'selected' : '' ?>>Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <a href="kegiatan.php" class="btn btn-outline-secondary btn-block" style="border-color:#b8c8df; color:#0B1F3A;">
                                        <i class="fas fa-times mr-1"></i>Reset filter
                                    </a>
                                </div>
                            </div>
                        </form>
                        <div class="mt-3">
                            <span class="jumlah-hasil">
                                Menampilkan <strong><?= count($daftarKegiatan) ?></strong> dari <strong><?= $totalDataFilter ?></strong> kegiatan
                                &middot; Halaman <?= $currentPage ?> dari <?= $totalHalaman ?>
                            </span>
                        </div>
                    </div>

                    <!-- TABEL -->
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Gambar</th>
                                    <th>Judul Kegiatan</th>
                                    <th>Bidang</th>
                                    <th>Periode Pelaksanaan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($daftarKegiatan)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada kegiatan. Klik "Tambah Kegiatan" untuk menambahkan.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($daftarKegiatan as $k):
                                    $gambarAda = !empty($k['gambar']) && file_exists(__DIR__ . '/../' . $k['gambar']);
                                    $sedangBerlangsung = (strtotime(date('Y-m-d')) >= strtotime($k['tanggal_mulai']) && strtotime(date('Y-m-d')) <= strtotime($k['tanggal_selesai']));
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($gambarAda): ?>
                                        <img class="kegiatan-thumb" src="../<?= htmlspecialchars($k['gambar']) ?>" alt="">
                                        <?php else: ?>
                                        <img class="kegiatan-thumb" src="../img/undraw_posting_photo.svg" alt="">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($k['judul']) ?>
                                        <?php if ($sedangBerlangsung): ?>
                                        <div class="mt-1"><span class="badge-berlangsung"><i class="fas fa-circle fa-xs mr-1"></i>Sedang Berlangsung</span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($k['bidang_nama'] ?? '-') ?></td>
                                    <td>
                                        <?= formatTanggalIndo($k['tanggal_mulai'], $bulanIndo) ?>
                                        &ndash;
                                        <?= formatTanggalIndo($k['tanggal_selesai'], $bulanIndo) ?>
                                    </td>
                                    <td>
                                        <?php if ($k['status'] === 'terbit'): ?>
                                        <span class="badge badge-success">Terbit</span>
                                        <?php else: ?>
                                        <span class="badge badge-warning">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex" style="gap:6px;">
                                            <button type="button" class="btn btn-sm btn-primary" title="Edit"
                                                onclick="openEditKegiatan(this)"
                                                data-id="<?= (int)$k['id'] ?>"
                                                data-bidang="<?= (int)$k['bidang_id'] ?>"
                                                data-judul="<?= htmlspecialchars($k['judul'], ENT_QUOTES) ?>"
                                                data-mulai="<?= htmlspecialchars($k['tanggal_mulai']) ?>"
                                                data-selesai="<?= htmlspecialchars($k['tanggal_selesai']) ?>"
                                                data-status="<?= htmlspecialchars($k['status']) ?>"
                                                data-gambar="<?= $gambarAda ? htmlspecialchars('../' . $k['gambar']) : '' ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="hapus_kegiatan.php" style="display:inline;"
                                                onsubmit="return confirm('Yakin ingin menghapus kegiatan \'<?= htmlspecialchars(addslashes($k['judul'])) ?>\'?');">
                                                <input type="hidden" name="id" value="<?= (int)$k['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                        <textarea id="deskripsi-data-<?= (int)$k['id'] ?>" style="display:none;"><?= htmlspecialchars($k['deskripsi'] ?? '') ?></textarea>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($totalHalaman > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <?php
                            $qs = [];
                            if ($filterBidang !== '') $qs[] = 'bidang=' . urlencode($filterBidang);
                            if ($filterStatus !== '') $qs[] = 'status=' . urlencode($filterStatus);
                            $qsStr = count($qs) ? '&' . implode('&', $qs) : '';
                            ?>
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $qsStr ?>">&laquo; Sebelumnya</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $qsStr ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $currentPage >= $totalHalaman ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $qsStr ?>">Selanjutnya &raquo;</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

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

<!-- Modal Tambah Kegiatan -->
<div class="modal fade" id="tambahKegiatanModal" tabindex="-1" role="dialog" aria-labelledby="tambahKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahKegiatanModalLabel">Tambah Kegiatan</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_tambah_kegiatan.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold small">Judul Kegiatan</label>
                            <input type="text" class="form-control" name="judul" placeholder="Contoh: Workshop Kurikulum Merdeka" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold small">Bidang</label>
                            <select class="form-control" name="bidang_id" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($daftarBidang as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Gambar</label>
                        <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp">
                        <small class="form-text text-muted">Format JPG/PNG/WEBP, maks. 2MB (opsional).</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Status</label>
                        <select class="form-control" name="status">
                            <option value="draf">Simpan sebagai Draft</option>
                            <option value="terbit">Terbitkan Sekarang</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="4" placeholder="Tulis deskripsi kegiatan di sini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kegiatan -->
<div class="modal fade" id="editKegiatanModal" tabindex="-1" role="dialog" aria-labelledby="editKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKegiatanModalLabel">Edit Kegiatan</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_edit_kegiatan.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold small">Judul Kegiatan</label>
                            <input type="text" class="form-control" name="judul" id="edit-judul" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold small">Bidang</label>
                            <select class="form-control" name="bidang_id" id="edit-bidang" required>
                                <?php foreach ($daftarBidang as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="tanggal_mulai" id="edit-mulai" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="font-weight-bold small">Tanggal Selesai</label>
                            <input type="date" class="form-control" name="tanggal_selesai" id="edit-selesai" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Gambar</label>
                        <div class="d-flex align-items-center mb-2" style="gap:12px;">
                            <img id="edit-gambar-preview" src="../img/undraw_posting_photo.svg" alt=""
                                style="width:70px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #eee0e3;">
                            <small class="text-muted">Gambar saat ini. Unggah file baru untuk menggantinya.</small>
                        </div>
                        <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah gambar.</small>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Status</label>
                        <select class="form-control" name="status" id="edit-status">
                            <option value="draf">Simpan sebagai Draft</option>
                            <option value="terbit">Terbitkan Sekarang</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Perbarui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Logout Modal -->
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
                <a class="btn btn-primary" href="../login/login.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

<script>
function openEditKegiatan(btn) {
    var d = btn.dataset;
    document.getElementById('edit-id').value        = d.id;
    document.getElementById('edit-bidang').value     = d.bidang;
    document.getElementById('edit-judul').value      = d.judul;
    document.getElementById('edit-mulai').value      = d.mulai;
    document.getElementById('edit-selesai').value    = d.selesai;
    document.getElementById('edit-status').value     = d.status;

    var deskripsiEl = document.getElementById('deskripsi-data-' + d.id);
    document.getElementById('edit-deskripsi').value = deskripsiEl ? deskripsiEl.value : '';

    document.getElementById('edit-gambar-preview').src = d.gambar || '../img/undraw_posting_photo.svg';

    $('#editKegiatanModal').modal('show');
}

(function () {
    var alertBox = document.getElementById('notifAlert');
    if (alertBox) setTimeout(function () { $(alertBox).alert('close'); }, 4000);
    if (window.history.replaceState && window.location.search.indexOf('notif=') !== -1) {
        window.history.replaceState(null, '', window.location.pathname);
    }
})();
</script>

</body>
</html>
