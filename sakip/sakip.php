<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include __DIR__ . '/../koneksi.php';

// ==== Label kategori ====
$kategoriLabel = [
    'renstra_pk' => 'Renstra & Perjanjian Kinerja',
    'lkjip'      => 'LKjIP',
    'iku'        => 'IKU',
];
$kategoriBadge = [
    'renstra_pk' => 'badge-primary',
    'lkjip'      => 'badge-info',
    'iku'        => 'badge-warning',
];

// ==== Filter dari GET ====
$filterKategori = $_GET['kategori'] ?? '';
$filterTahun    = $_GET['tahun'] ?? '';

$whereParts = [];
if ($filterKategori !== '' && array_key_exists($filterKategori, $kategoriLabel)) {
    $kEsc = mysqli_real_escape_string($koneksi, $filterKategori);
    $whereParts[] = "kategori = '$kEsc'";
}
if ($filterTahun !== '' && ctype_digit($filterTahun)) {
    $tEsc = (int)$filterTahun;
    $whereParts[] = "tahun = $tEsc";
}
$whereSQL = count($whereParts) ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// ==== Pagination ====
$perPage     = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset      = ($currentPage - 1) * $perPage;

$totalDataFilter = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM sakip $whereSQL")) {
    $totalDataFilter = (int)mysqli_fetch_assoc($r)['jml'];
}
$totalHalaman = max(1, (int)ceil($totalDataFilter / $perPage));
if ($currentPage > $totalHalaman) {
    $currentPage = $totalHalaman;
    $offset = ($currentPage - 1) * $perPage;
}

// ==== Statistik ====
$totalDokumen = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM sakip")) $totalDokumen = mysqli_fetch_assoc($r)['jml'];

$totalRenstra = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM sakip WHERE kategori = 'renstra_pk'")) $totalRenstra = mysqli_fetch_assoc($r)['jml'];

$totalLkjip = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM sakip WHERE kategori = 'lkjip'")) $totalLkjip = mysqli_fetch_assoc($r)['jml'];

$totalIku = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM sakip WHERE kategori = 'iku'")) $totalIku = mysqli_fetch_assoc($r)['jml'];

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Dokumen SAKIP berhasil ditambahkan.',   'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Dokumen SAKIP berhasil diperbarui.',      'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Dokumen SAKIP berhasil dihapus.',         'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Kategori, judul, tahun, dan file PDF wajib diisi.', 'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan data ke database.',       'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus dokumen.',                'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Dokumen tidak ditemukan.',                'icon' => 'fa-exclamation-circle'],
];

// ==== Daftar dokumen ====
$daftarSakip = [];
$sql = "SELECT id, kategori, judul, tahun, file, keterangan, created_at
        FROM sakip $whereSQL
        ORDER BY tahun DESC, created_at DESC
        LIMIT $perPage OFFSET $offset";
if ($r = mysqli_query($koneksi, $sql)) {
    while ($row = mysqli_fetch_assoc($r)) $daftarSakip[] = $row;
}

// ==== Daftar tahun tersedia untuk dropdown filter ====
$daftarTahun = [];
if ($r = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM sakip ORDER BY tahun DESC")) {
    while ($row = mysqli_fetch_assoc($r)) $daftarTahun[] = $row['tahun'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SAKIP - Disdik Sumenep</title>
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

        #tambahSakipModal .modal-header,
        #editSakipModal .modal-header { background-color: #162F55; color: #fff; }
        #tambahSakipModal .modal-header .close,
        #editSakipModal .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }

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

        .file-icon-pdf {
            color: #c0392b; font-size: 1.3rem;
        }
        .btn-unduh {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .8rem; font-weight: 600;
            color: #162F55 !important; text-decoration: none;
        }
        .btn-unduh:hover { text-decoration: underline; }

        .pagination .page-link { color: #162F55; }
        .pagination .page-item.active .page-link { background-color: #162F55; border-color: #162F55; color: #fff; }
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
            <div class="d-flex flex-column" style="color:white;">
                <div style="font-size:.7rem;">Dinas Pendidikan</div>
                <div style="font-size:.5rem;"><i>Kabupaten Sumenep</i></div>
            </div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../berita/berita.php"><i class="fas fa-fw fa-newspaper"></i><span>Berita</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pengumuman/pengumuman.php"><i class="fas fa-fw fa-bullhorn"></i><span>Pengumuman</span></a></li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo">
                <i class="fas fa-fw fa-images"></i><span>Galeri</span>
            </a>
            <div id="collapseTwo" class="collapse" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="../galeri/galeri_foto.php">Foto</a>
                    <a class="collapse-item" href="../galeri/galeri_prestasi.php">Prestasi</a>
                </div>
            </div>
        </li>
        <li class="nav-item"><a class="nav-link" href="../pengaduan/pengaduan.php"><i class="fas fa-fw fa-exclamation-triangle"></i><span>Pengaduan</span></a></li>
        <!-- Nav Item - Sakip -->
        <li class="nav-item active">
            <a class="nav-link" href="../sakip/sakip.php">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>Sakip</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Instansi</div>
        <li class="nav-item"><a class="nav-link" href="../profil/profil.php"><i class="fas fa-fw fa-user"></i><span>Profil</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pegawai/pegawai.php"><i class="fas fa-fw fa-user-friends"></i><span>Pegawai</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../bidang/bidang.php"><i class="fas fa-fw fa-building"></i><span>Bidang</span></a></li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
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
                        <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                        <div class="dropdown-divider"></div>
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
                    <h1 class="h3 mb-0 text-gray-800">SAKIP</h1>
                    <div class="text-muted small">Sistem Akuntabilitas Kinerja Instansi Pemerintah &mdash; arsip dokumen Renstra &amp; Perjanjian Kinerja, LKjIP, dan IKU.</div>
                </div>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white"
                    data-toggle="modal" data-target="#tambahSakipModal">
                    <i class="fas fa-plus fa-sm text-white"></i> Tambah Dokumen
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
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Dokumen</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalDokumen ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-folder-open fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Renstra &amp; PK</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalRenstra ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-file-signature fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">LKjIP</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalLkjip ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-chart-line fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">IKU</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalIku ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-bullseye fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arsip Dokumen -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="border-left:4px solid #162F55;">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-file-contract mr-2" style="color:#162F55;"></i>Arsip Dokumen SAKIP
                    </h6>
                </div>
                <div class="card-body">

                    <!-- FILTER BAR -->
                    <div class="filter-bar">
                        <form method="GET" action="sakip.php" id="formFilter">
                            <div class="row align-items-end">
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-tags mr-1"></i>Kategori</label>
                                    <select name="kategori" class="form-control" onchange="this.form.submit();">
                                        <option value="">-- Semua kategori --</option>
                                        <?php foreach ($kategoriLabel as $key => $label): ?>
                                        <option value="<?= $key ?>" <?= $filterKategori === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-calendar-alt mr-1"></i>Tahun</label>
                                    <select name="tahun" class="form-control" onchange="this.form.submit();">
                                        <option value="">-- Semua tahun --</option>
                                        <?php foreach ($daftarTahun as $th): ?>
                                        <option value="<?= $th ?>" <?= $filterTahun == $th ? 'selected' : '' ?>><?= $th ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <a href="sakip.php" class="btn btn-outline-secondary btn-block" style="border-color:#b8c8df; color:#0B1F3A;">
                                        <i class="fas fa-times mr-1"></i>Reset filter
                                    </a>
                                </div>
                            </div>
                        </form>
                        <div class="mt-3">
                            <span class="jumlah-hasil">
                                Menampilkan <strong><?= count($daftarSakip) ?></strong> dari <strong><?= $totalDataFilter ?></strong> dokumen
                                &middot; Halaman <?= $currentPage ?> dari <?= $totalHalaman ?>
                            </span>
                        </div>
                    </div>

                    <!-- TABEL -->
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Judul Dokumen</th>
                                    <th>Tahun</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($daftarSakip)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada dokumen SAKIP. Klik "Tambah Dokumen" untuk menambahkan.</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($daftarSakip as $s):
                                    $fileAda = !empty($s['file']) && file_exists(__DIR__ . '/../' . $s['file']);
                                ?>
                                <tr>
                                    <td><span class="badge <?= $kategoriBadge[$s['kategori']] ?? 'badge-secondary' ?>"><?= htmlspecialchars($kategoriLabel[$s['kategori']] ?? $s['kategori']) ?></span></td>
                                    <td>
                                        <?= htmlspecialchars($s['judul']) ?>
                                        <?php if (!empty($s['keterangan'])): ?>
                                        <div class="text-muted small"><?= htmlspecialchars($s['keterangan']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int)$s['tahun'] ?></td>
                                    <td>
                                        <?php if ($fileAda): ?>
                                        <a class="btn-unduh" href="../<?= htmlspecialchars($s['file']) ?>" target="_blank">
                                            <i class="fas fa-file-pdf file-icon-pdf"></i> Unduh PDF
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">File tidak ditemukan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex" style="gap:6px;">
                                            <button type="button" class="btn btn-sm btn-primary" title="Edit"
                                                onclick="openEditSakip(this)"
                                                data-id="<?= (int)$s['id'] ?>"
                                                data-kategori="<?= htmlspecialchars($s['kategori']) ?>"
                                                data-judul="<?= htmlspecialchars($s['judul'], ENT_QUOTES) ?>"
                                                data-tahun="<?= (int)$s['tahun'] ?>"
                                                data-keterangan="<?= htmlspecialchars($s['keterangan'] ?? '', ENT_QUOTES) ?>"
                                                data-file="<?= $fileAda ? htmlspecialchars(basename($s['file'])) : '' ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="hapus_sakip.php" style="display:inline;"
                                                onsubmit="return confirm('Yakin ingin menghapus dokumen \'<?= htmlspecialchars(addslashes($s['judul'])) ?>\'?');">
                                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
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
                            if ($filterKategori !== '') $qs[] = 'kategori=' . urlencode($filterKategori);
                            if ($filterTahun !== '') $qs[] = 'tahun=' . urlencode($filterTahun);
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

<!-- Modal Tambah Dokumen -->
<div class="modal fade" id="tambahSakipModal" tabindex="-1" role="dialog" aria-labelledby="tambahSakipModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahSakipModalLabel">Tambah Dokumen SAKIP</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_tambah_sakip.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Kategori</label>
                        <select class="form-control" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategoriLabel as $key => $label): ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Judul Dokumen</label>
                        <input type="text" class="form-control" name="judul" placeholder="Contoh: Perjanjian Kinerja Tahun 2026" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tahun</label>
                        <input type="number" class="form-control" name="tahun" min="2000" max="2100" value="<?= date('Y') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">File PDF</label>
                        <input type="file" class="form-control-file" name="file" accept="application/pdf" required>
                        <small class="form-text text-muted">Hanya file PDF, maks. 5MB.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
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

<!-- Modal Edit Dokumen -->
<div class="modal fade" id="editSakipModal" tabindex="-1" role="dialog" aria-labelledby="editSakipModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSakipModalLabel">Edit Dokumen SAKIP</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_edit_sakip.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label class="font-weight-bold small">Kategori</label>
                        <select class="form-control" name="kategori" id="edit-kategori" required>
                            <?php foreach ($kategoriLabel as $key => $label): ?>
                            <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Judul Dokumen</label>
                        <input type="text" class="form-control" name="judul" id="edit-judul" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tahun</label>
                        <input type="number" class="form-control" name="tahun" id="edit-tahun" min="2000" max="2100" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">File PDF</label>
                        <div class="mb-2 small text-muted">File saat ini: <span id="edit-file-current" class="font-weight-bold"></span></div>
                        <input type="file" class="form-control-file" name="file" accept="application/pdf">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit-keterangan" rows="2"></textarea>
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
                <h5 class="modal-title">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
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
function openEditSakip(btn) {
    var d = btn.dataset;
    document.getElementById('edit-id').value          = d.id;
    document.getElementById('edit-kategori').value    = d.kategori;
    document.getElementById('edit-judul').value       = d.judul;
    document.getElementById('edit-tahun').value       = d.tahun;
    document.getElementById('edit-keterangan').value  = d.keterangan || '';
    document.getElementById('edit-file-current').textContent = d.file || '(tidak ada)';
    $('#editSakipModal').modal('show');
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
