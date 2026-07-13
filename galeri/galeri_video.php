<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// ==== Filter per bulan dari GET ====
$filterBulan = $_GET['bulan'] ?? ''; // format: YYYY-MM

// ==== Bangun WHERE clause: halaman ini SELALU dikunci ke kategori = 'video' ====
$whereParts = ["kategori = 'video'"];
if ($filterBulan !== '') {
    $parts = explode('-', $filterBulan);
    if (count($parts) === 2) {
        $y = (int)$parts[0];
        $m = (int)$parts[1];
        $whereParts[] = "YEAR(tanggal) = $y AND MONTH(tanggal) = $m";
    }
}
$whereSQL = 'WHERE ' . implode(' AND ', $whereParts);

// ==== Pagination ====
$perPage     = 10;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset      = ($currentPage - 1) * $perPage;

$totalDataFilter = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM galeri $whereSQL")) {
    $totalDataFilter = (int)mysqli_fetch_assoc($r)['jml'];
}
$totalHalaman = max(1, (int)ceil($totalDataFilter / $perPage));
if ($currentPage > $totalHalaman) {
    $currentPage = $totalHalaman;
    $offset = ($currentPage - 1) * $perPage;
}

// ==== Statistik — selalu dari data kategori video saja ====
$totalVideo = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM galeri WHERE kategori = 'video'"))
    $totalVideo = mysqli_fetch_assoc($r)['jml'];

$videoBulanIni = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM galeri WHERE kategori = 'video' AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())"))
    $videoBulanIni = mysqli_fetch_assoc($r)['jml'];

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Video berhasil ditambahkan ke galeri.',    'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Data video berhasil diperbarui.',           'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Video berhasil dihapus dari galeri.',       'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Judul, tanggal, thumbnail, dan link video wajib diisi.', 'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan data ke database.',        'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus video.',                   'icon' => 'fa-exclamation-circle'],
    'not_found'     => ['type' => 'danger',  'text' => 'Data video tidak ditemukan.',              'icon' => 'fa-exclamation-circle'],
];

// ==== Daftar galeri (kategori video saja) dengan filter bulan + pagination ====
$daftarVideo = [];
$sqlGaleri = "SELECT id, judul, gambar, video, keterangan, tanggal FROM galeri $whereSQL ORDER BY tanggal DESC, created_at DESC LIMIT $perPage OFFSET $offset";
if ($r = mysqli_query($koneksi, $sqlGaleri)) {
    while ($row = mysqli_fetch_assoc($r))
        $daftarVideo[] = $row;
}

// ==== Daftar bulan tersedia untuk dropdown (kategori video saja) ====
$daftarBulanTersedia = [];
if ($r = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(tanggal, '%Y-%m') AS bulan_key, DATE_FORMAT(tanggal, '%M %Y') AS bulan_label FROM galeri WHERE kategori = 'video' ORDER BY bulan_key DESC")) {
    while ($row = mysqli_fetch_assoc($r))
        $daftarBulanTersedia[] = $row;
}

// Label bulan aktif untuk ditampilkan
$labelBulanAktif = '';
if ($filterBulan !== '') {
    $bulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $parts = explode('-', $filterBulan);
    $labelBulanAktif = ($bulanNama[(int)($parts[1] ?? 1)] ?? '') . ' ' . ($parts[0] ?? '');
}

$bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
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
    <title>Galeri Video - Disdik Sumenep</title>
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

        #tambahVideoModal .modal-header,
        #editVideoModal .modal-header { background-color: #162F55; color: #fff; }
        #tambahVideoModal .modal-header .close,
        #editVideoModal .modal-header .close { color: #fff; opacity: .85; text-shadow: none; }

        /* Gallery */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-bottom: 8px; }
        .gallery-item { border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); transition: transform .3s ease; background: #fff; }
        .gallery-item:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
        .gallery-item-thumb { position: relative; display: block; }
        .gallery-item-thumb img { width: 100%; height: 220px; object-fit: cover; display: block; }
        .gallery-item-thumb .play-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(11,31,58,0.25);
            transition: background .2s ease;
        }
        .gallery-item-thumb:hover .play-overlay { background: rgba(11,31,58,0.45); }
        .gallery-item-thumb .play-overlay i {
            font-size: 2.6rem; color: #fff; text-shadow: 0 2px 6px rgba(0,0,0,.4);
        }
        .gallery-item-info { padding: 12px; background: white; }
        .gallery-item-title { font-size: 1rem; font-weight: bold; color: #333; margin: 0 0 5px; }
        .gallery-item-date { font-size: .875rem; color: #6c757d; margin: 0; }
        .gallery-item-keterangan { font-size: .8rem; color: #7f6267; margin: 4px 0 0; }
        .gallery-item-actions { margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap; }
        .gallery-item-actions .btn { font-size: .875rem; padding: 5px 10px; }

        /* Filter bar */
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

        .badge-filter {
            display: inline-flex; align-items: center; gap: 6px;
            background: #162F55; color: #fff;
            padding: 4px 12px; border-radius: 20px; font-size: .78rem; font-weight: 600;
        }
        .badge-filter a { color: #fff !important; text-decoration: none; opacity: .8; margin-left: 2px; }
        .badge-filter a:hover { opacity: 1; }

        .jumlah-hasil { font-size: .82rem; color: #6B7280; }

        /* Pagination */
        .pagination .page-link { color: #162F55; }
        .pagination .page-item.active .page-link { background-color: #162F55; border-color: #162F55; color: #fff; }

        /* Modal preview video */
        #previewVideoModal .modal-body { padding: 0; background: #000; }
        #previewVideoModal .embed-responsive { border-radius: 0 0 0.3rem 0.3rem; overflow: hidden; }
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
            <div class="d-flex flex-column" style="color:white;">
                <div style="font-size:.7rem;">Dinas Pendidikan</div>
                <div style="font-size:.5rem;"><i>Kabupaten Sumenep</i></div>
            </div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="../dashboard/index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../berita/berita.php"><i class="fas fa-fw fa-newspaper"></i><span>Berita</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pengumuman/pengumuman.php"><i class="fas fa-fw fa-bullhorn"></i><span>Pengumuman</span></a></li>
        <!-- Nav Item - Galeri -->
        <li class="nav-item active">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="true" aria-controls="collapseTwo">
                <i class="fas fa-fw fa-images"></i>
                <span>Galeri</span>
            </a>
            <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="../galeri/galeri_foto.php">Foto</a>
                    <a class="collapse-item active" href="../galeri/galeri_video.php">Video</a>
                    <a class="collapse-item" href="../galeri/galeri_prestasi.php">Prestasi</a>
                </div>
            </div>
        </li>
        <!-- Nav Item - Sakip -->
        <li class="nav-item">
            <a class="nav-link" href="../sakip/sakip.php">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>Sakip</span></a>
        </li>
        <li class="nav-item"><a class="nav-link" href="../pengaduan/pengaduan.php"><i class="fas fa-fw fa-exclamation-triangle"></i><span>Pengaduan</span></a></li>
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
                    <h1 class="h3 mb-0 text-gray-800">Kelola Galeri &mdash; Video</h1>
                    <div class="text-muted small">Kelola video kegiatan yang tampil di halaman galeri website Dinas Pendidikan Kabupaten Sumenep</div>
                </div>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white"
                    data-toggle="modal" data-target="#tambahVideoModal">
                    <i class="fas fa-plus fa-sm text-white"></i> Tambah Video
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
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Total Video</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalVideo ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-video fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Ditambahkan Bulan Ini</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $videoBulanIni ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-calendar-alt fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galeri Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="border-left:4px solid #162F55;">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-play-circle mr-2" style="color:#162F55;"></i>Video Kegiatan
                    </h6>
                </div>
                <div class="card-body">

                    <!-- ===== FILTER BAR ===== -->
                    <div class="filter-bar">
                        <form method="GET" action="galeri_video.php" id="formFilter">
                            <div class="row align-items-end">

                                <!-- Search -->
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-search mr-1"></i>Cari judul video</label>
                                    <input type="text" id="gallerySearch" class="form-control"
                                           placeholder="Ketik judul atau tanggal...">
                                </div>

                                <!-- Filter Bulan -->
                                <div class="col-md-4 mb-2 mb-md-0">
                                    <label><i class="fas fa-calendar-alt mr-1"></i>Filter per bulan</label>
                                    <select name="bulan" class="form-control" onchange="this.form.submit();">
                                        <option value="">-- Semua bulan --</option>
                                        <?php foreach ($daftarBulanTersedia as $bln): ?>
                                        <option value="<?= htmlspecialchars($bln['bulan_key']) ?>"
                                            <?= $filterBulan === $bln['bulan_key'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($bln['bulan_label']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Reset -->
                                <div class="col-md-4">
                                    <a href="galeri_video.php" class="btn btn-outline-secondary btn-block"
                                       style="border-color:#b8c8df; color:#0B1F3A;">
                                        <i class="fas fa-times mr-1"></i>Reset filter
                                    </a>
                                </div>

                            </div>
                        </form>

                        <!-- Label filter aktif + jumlah hasil -->
                        <div class="d-flex align-items-center mt-3" style="gap:10px; flex-wrap:wrap;">
                            <?php if ($labelBulanAktif): ?>
                            <span class="badge-filter">
                                <i class="fas fa-calendar-alt fa-sm"></i>
                                <?= htmlspecialchars($labelBulanAktif) ?>
                                <a href="galeri_video.php" title="Hapus filter">&times;</a>
                            </span>
                            <?php endif; ?>
                            <span class="jumlah-hasil">
                                Menampilkan <strong><?= count($daftarVideo) ?></strong> dari <strong><?= $totalDataFilter ?></strong> video
                                <?= $labelBulanAktif ? 'di bulan ' . htmlspecialchars($labelBulanAktif) : 'dari semua bulan' ?>
                                &middot; Halaman <?= $currentPage ?> dari <?= $totalHalaman ?>
                            </span>
                        </div>
                    </div>
                    <!-- ===== END FILTER BAR ===== -->

                    <!-- Gallery Grid -->
                    <div class="gallery-grid" id="galleryGrid">
                        <?php if (empty($daftarVideo)): ?>
                        <div style="grid-column:1/-1;" class="text-center text-muted py-5">
                            <i class="fas fa-video fa-3x mb-3 d-block text-gray-300"></i>
                            <?= $labelBulanAktif
                                ? 'Tidak ada video di bulan <strong>' . htmlspecialchars($labelBulanAktif) . '</strong>.'
                                : 'Belum ada video di galeri. Klik <strong>Tambah Video</strong> untuk menambahkan.' ?>
                        </div>
                        <?php else: ?>
                        <?php foreach ($daftarVideo as $g):
                            $tglTampil = formatTanggalIndo($g['tanggal'], $bulanIndo);
                            $gambarAda = !empty($g['gambar']) && file_exists(__DIR__ . '/../' . $g['gambar']);
                            $videoUrl  = $g['video'] ?? '';
                        ?>
                        <div class="gallery-item"
                             data-title="<?= htmlspecialchars(strtolower($g['judul']), ENT_QUOTES) ?>"
                             data-date="<?= htmlspecialchars(strtolower($tglTampil), ENT_QUOTES) ?>">
                            <div class="gallery-item-thumb" style="cursor:pointer;"
                                 onclick="bukaPreviewVideo('<?= htmlspecialchars($videoUrl, ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($g['judul'])) ?>')">
                                <img src="<?= $gambarAda ? '../' . htmlspecialchars($g['gambar']) : '../img/undraw_posting_photo.svg' ?>"
                                     alt="<?= htmlspecialchars($g['judul']) ?>">
                                <div class="play-overlay"><i class="fas fa-play-circle"></i></div>
                            </div>
                            <div class="gallery-item-info">
                                <p class="gallery-item-title"><?= htmlspecialchars($g['judul']) ?></p>
                                <p class="gallery-item-date"><?= $tglTampil ?></p>
                                <?php if (!empty($g['keterangan'])): ?>
                                <p class="gallery-item-keterangan"><?= htmlspecialchars($g['keterangan']) ?></p>
                                <?php endif; ?>
                                <div class="gallery-item-actions">
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="openEditVideo(this)"
                                        data-id="<?= (int)$g['id'] ?>"
                                        data-judul="<?= htmlspecialchars($g['judul'], ENT_QUOTES) ?>"
                                        data-tanggal="<?= htmlspecialchars($g['tanggal']) ?>"
                                        data-keterangan="<?= htmlspecialchars($g['keterangan'] ?? '', ENT_QUOTES) ?>"
                                        data-video="<?= htmlspecialchars($videoUrl, ENT_QUOTES) ?>"
                                        data-gambar="<?= $gambarAda ? htmlspecialchars('../' . $g['gambar']) : '' ?>">
                                        Edit
                                    </button>
                                    <form method="POST" action="hapus_video.php" style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus video \'<?= htmlspecialchars(addslashes($g['judul'])) ?>\'?');">
                                        <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <!-- ===== END GALLERY GRID ===== -->

                    <!-- ===== PAGINATION ===== -->
                    <?php if ($totalHalaman > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center mb-0">

                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $filterBulan !== '' ? '&bulan=' . urlencode($filterBulan) : '' ?>">&laquo; Sebelumnya</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $filterBulan !== '' ? '&bulan=' . urlencode($filterBulan) : '' ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $currentPage >= $totalHalaman ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $filterBulan !== '' ? '&bulan=' . urlencode($filterBulan) : '' ?>">Selanjutnya &raquo;</a>
                            </li>

                        </ul>
                    </nav>
                    <?php endif; ?>
                    <!-- ===== END PAGINATION ===== -->

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

<!-- Modal Tambah Video -->
<div class="modal fade" id="tambahVideoModal" tabindex="-1" role="dialog" aria-labelledby="tambahVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahVideoModalLabel">Tambah Video Galeri</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_tambah_video.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="kategori" value="video">
                    <div class="form-group">
                        <label class="font-weight-bold small">Judul</label>
                        <input type="text" class="form-control" name="judul" placeholder="Contoh: Dokumentasi Rakor Kepala Sekolah 2026" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Link Video (YouTube/URL)</label>
                        <input type="url" class="form-control" name="video" placeholder="https://www.youtube.com/watch?v=..." required>
                        <small class="form-text text-muted">Tempel link YouTube atau URL video langsung.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Thumbnail / Gambar Sampul</label>
                        <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp" required>
                        <small class="form-text text-muted">Format JPG/PNG/WEBP, maks. 2MB. Wajib diisi (dipakai sebagai sampul video).</small>
                    </div>
                    <div class="form-group mb-0 mt-3">
                        <label class="font-weight-bold small">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
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

<!-- Modal Edit Video -->
<div class="modal fade" id="editVideoModal" tabindex="-1" role="dialog" aria-labelledby="editVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVideoModalLabel">Edit Video Galeri</h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="proses_edit_video.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label class="font-weight-bold small">Judul</label>
                        <input type="text" class="form-control" name="judul" id="edit-judul" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="edit-tanggal" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Link Video (YouTube/URL)</label>
                        <input type="url" class="form-control" name="video" id="edit-video" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Thumbnail / Gambar Sampul</label>
                        <div class="d-flex align-items-center mb-2" style="gap:12px;">
                            <img id="edit-gambar-preview" src="../img/undraw_posting_photo.svg" alt=""
                                style="width:70px;height:48px;object-fit:cover;border-radius:4px;border:1px solid #eee0e3;">
                            <small class="text-muted">Thumbnail saat ini. Unggah file baru untuk menggantinya.</small>
                        </div>
                        <input type="file" class="form-control-file" name="gambar" accept="image/png, image/jpeg, image/webp">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah thumbnail.</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Keterangan</label>
                        <textarea class="form-control" name="keterangan" id="edit-keterangan" rows="3"></textarea>
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

<!-- Modal Preview Video -->
<div class="modal fade" id="previewVideoModal" tabindex="-1" role="dialog" aria-labelledby="previewVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#162F55; color:#fff;">
                <h5 class="modal-title" id="previewVideoModalLabel">Preview Video</h5>
                <button class="close" type="button" data-dismiss="modal" style="color:#fff;"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="embed-responsive embed-responsive-16by9" id="previewVideoWrap"></div>
            </div>
            <div class="modal-footer">
                <a href="#" id="previewVideoLink" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                </a>
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Tutup</button>
            </div>
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
// Search bar — filter tampilan kartu tanpa reload
document.getElementById('gallerySearch').addEventListener('keyup', function () {
    var term = this.value.toLowerCase();
    document.querySelectorAll('.gallery-item').forEach(function (item) {
        var title = item.getAttribute('data-title') || '';
        var date  = item.getAttribute('data-date')  || '';
        item.style.display = (title.includes(term) || date.includes(term)) ? '' : 'none';
    });
});

function openEditVideo(btn) {
    var d = btn.dataset;
    document.getElementById('edit-id').value          = d.id;
    document.getElementById('edit-judul').value       = d.judul;
    document.getElementById('edit-tanggal').value     = d.tanggal;
    document.getElementById('edit-keterangan').value  = d.keterangan || '';
    document.getElementById('edit-video').value       = d.video || '';
    document.getElementById('edit-gambar-preview').src = d.gambar || '../img/undraw_posting_photo.svg';
    $('#editVideoModal').modal('show');
}

// Ubah berbagai format URL YouTube jadi embed URL; selain itu pakai apa adanya
function toEmbedUrl(url) {
    if (!url) return '';
    var m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{6,})/);
    if (m && m[1]) {
        return 'https://www.youtube.com/embed/' + m[1];
    }
    return url; // fallback: coba tampilkan langsung sebagai iframe/link
}

function bukaPreviewVideo(url, judul) {
    var wrap = document.getElementById('previewVideoWrap');
    var link = document.getElementById('previewVideoLink');
    document.getElementById('previewVideoModalLabel').textContent = judul || 'Preview Video';
    link.href = url;

    wrap.innerHTML = '';
    var embedUrl = toEmbedUrl(url);
    if (embedUrl) {
        var iframe = document.createElement('iframe');
        iframe.className = 'embed-responsive-item';
        iframe.src = embedUrl;
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
        wrap.appendChild(iframe);
    } else {
        wrap.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-white p-4">Tidak dapat menampilkan preview. Gunakan tombol "Buka di Tab Baru".</div>';
    }

    $('#previewVideoModal').modal('show');
}

// Notifikasi auto-close
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
