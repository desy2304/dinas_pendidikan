<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include __DIR__ . '/../koneksi.php';

// ==== Statistik ====
$totalPegawai = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pegawai")) {
    $totalPegawai = mysqli_fetch_assoc($r)['jml'];
}
$pegawaiAktif = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pegawai WHERE status = 'aktif'")) {
    $pegawaiAktif = mysqli_fetch_assoc($r)['jml'];
}
$pegawaiNonaktif = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pegawai WHERE status = 'nonaktif'")) {
    $pegawaiNonaktif = mysqli_fetch_assoc($r)['jml'];
}
$jumlahBidang = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bidang")) {
    $jumlahBidang = mysqli_fetch_assoc($r)['jml'];
}

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah'    => ['type' => 'success', 'text' => 'Data pegawai berhasil ditambahkan.',                 'icon' => 'fa-check-circle'],
    'sukses_edit'      => ['type' => 'success', 'text' => 'Data pegawai berhasil diperbarui.',                   'icon' => 'fa-check-circle'],
    'sukses_hapus'     => ['type' => 'success', 'text' => 'Data pegawai berhasil dihapus.',                      'icon' => 'fa-check-circle'],
    'gagal_kosong'     => ['type' => 'danger',  'text' => 'Nama, NIP, dan jabatan wajib diisi.',                 'icon' => 'fa-exclamation-circle'],
    'gagal_nip'        => ['type' => 'danger',  'text' => 'NIP sudah dipakai oleh pegawai lain.',                'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'     => ['type' => 'danger',  'text' => 'Gagal menyimpan data pegawai ke database.',           'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'      => ['type' => 'danger',  'text' => 'Gagal menghapus data pegawai.',                       'icon' => 'fa-exclamation-circle'],
    'not_found'        => ['type' => 'danger',  'text' => 'Data pegawai tidak ditemukan.',                       'icon' => 'fa-exclamation-circle'],
];

// ==== Dropdown Bidang (ambil langsung dari tabel bidang, bukan teks hardcode) ====
$daftarBidang = [];
if ($r = mysqli_query($koneksi, "SELECT id, nama FROM bidang ORDER BY nama ASC")) {
    while ($row = mysqli_fetch_assoc($r)) {
        $daftarBidang[] = $row;
    }
}

// ==== Daftar pegawai (join ke bidang supaya nama bidang ikut tampil) ====
$daftarPegawai = [];
$sqlPegawai = "SELECT pegawai.id, pegawai.nip, pegawai.nama, pegawai.jabatan, pegawai.foto,
                      pegawai.email, pegawai.status, pegawai.bidang_id,
                      bidang.nama AS bidang_nama
               FROM pegawai
               LEFT JOIN bidang ON pegawai.bidang_id = bidang.id
               ORDER BY pegawai.nama ASC";
if ($r = mysqli_query($koneksi, $sqlPegawai)) {
    while ($row = mysqli_fetch_assoc($r)) {
        $daftarPegawai[] = $row;
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

    <title>SB Admin 2 - Pegawai</title>

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

        a:not(.page-link):not(.read-more-link):not(.dataTables_wrapper .dataTables_paginate a),
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

        /* ==== Foto Pegawai di Tabel ==== */
        .pegawai-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #eee0e3;
        }
        .pegawai-nama {
            font-weight: 700;
            color: #4d1e2a;
            margin-bottom: 0;
        }
        .pegawai-nip {
            font-size: 0.75rem;
            color: #9a8a8e;
        }

        /* ==== Modal Tambah/Edit Pegawai ==== */
        #pegawaiModal .modal-header {
            background-color: #162F55;
            color: #fff;
            border-bottom: none;
        }
        #pegawaiModal .modal-header .close {
            color: #fff;
            opacity: 0.85;
            text-shadow: none;
        }
        .upload-photo-box {
            border: 1.5px dashed #d9c3c9;
            border-radius: 0.35rem;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            background: #fff5f7;
        }
        .upload-photo-box:hover {
            border-color: #162F55;
        }
        .upload-photo-preview {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #eee0e3;
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
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
            <li class="nav-item active">
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

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                        </li>

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
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
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
                            <h1 class="h3 mb-0 text-gray-800">Data Pegawai</h1>
                            <div class="text-muted small">Kelola data pegawai Dinas Pendidikan Kabupaten Sumenep.</div>
                        </div>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white"
                            style="color: #fff !important;" data-toggle="modal" data-target="#pegawaiModal"
                            onclick="openPegawaiModal('tambah')">
                            <i class="fas fa-plus fa-sm text-white"></i>  Tambah Pegawai
                        </a>
                    </div>

                    <?php if ($notif && isset($notifMsg[$notif])) : ?>
                        <div id="notifAlert" class="alert alert-<?= $notifMsg[$notif]['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas <?= $notifMsg[$notif]['icon'] ?> mr-2"></i><?= htmlspecialchars($notifMsg[$notif]['text']) ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                Total Pegawai</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPegawai ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-friends fa-2x text-gray-300"></i>
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
                                                Pegawai Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pegawaiAktif ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                                Non-Aktif</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pegawaiNonaktif ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                Jumlah Bidang</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $jumlahBidang ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-building fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Pegawai -->
                    <div>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
                                <h6 class="m-0 font-weight-bold text-dark">Daftar Pegawai</h6>
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    <label for="filterBidang" class="mb-0 small font-weight-bold text-muted">Filter Bidang:</label>
                                    <select id="filterBidang" class="form-control form-control-sm" style="width:auto; min-width:190px;">
                                        <option value="">-- Semua Bidang --</option>
                                        <?php foreach ($daftarBidang as $b) : ?>
                                            <option value="<?= htmlspecialchars($b['nama'], ENT_QUOTES) ?>"><?= htmlspecialchars($b['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0"
                                        data-search-label="Cari pegawai:"
                                        data-length-label="Tampilkan _MENU_ data"
                                        data-info-label="Menampilkan _START_ sampai _END_ dari _TOTAL_ data"
                                        data-prev-label="Previous"
                                        data-next-label="Next">
                                        <thead>
                                            <tr>
                                                <th style="width:60px">Foto</th>
                                                <th>Nama / NIP</th>
                                                <th>Jabatan</th>
                                                <th>Bidang</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th style="width:110px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Foto</th>
                                                <th>Nama / NIP</th>
                                                <th>Jabatan</th>
                                                <th>Bidang</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php if (empty($daftarPegawai)) : ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        Belum ada data pegawai. Klik "Tambah Pegawai" untuk menambahkan.
                                                    </td>
                                                </tr>
                                            <?php else : ?>
                                                <?php foreach ($daftarPegawai as $p) :
                                                    $fotoPath = !empty($p['foto']) ? '../img/pegawai/' . $p['foto'] : '../img/undraw_profile.svg';
                                                ?>
                                                    <tr>
                                                        <td><img class="pegawai-avatar" src="<?= htmlspecialchars($fotoPath) ?>" alt=""></td>
                                                        <td>
                                                            <p class="pegawai-nama"><?= htmlspecialchars($p['nama']) ?></p>
                                                            <div class="pegawai-nip">NIP. <?= htmlspecialchars($p['nip'] ?: '-') ?></div>
                                                        </td>
                                                        <td><?= htmlspecialchars($p['jabatan']) ?></td>
                                                        <td><?= htmlspecialchars($p['bidang_nama'] ?? '-') ?></td>
                                                        <td><?= htmlspecialchars($p['email'] ?: '-') ?></td>
                                                        <td>
                                                            <?php if ($p['status'] === 'aktif') : ?>
                                                                <span class="badge badge-success">Aktif</span>
                                                            <?php else : ?>
                                                                <span class="badge badge-secondary">Non-Aktif</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary" title="Edit"
                                                                onclick="openPegawaiModal('edit', this)"
                                                                data-id="<?= (int)$p['id'] ?>"
                                                                data-nama="<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>"
                                                                data-nip="<?= htmlspecialchars($p['nip'] ?? '', ENT_QUOTES) ?>"
                                                                data-jabatan="<?= htmlspecialchars($p['jabatan'], ENT_QUOTES) ?>"
                                                                data-bidang="<?= (int)($p['bidang_id'] ?? 0) ?>"
                                                                data-email="<?= htmlspecialchars($p['email'] ?? '', ENT_QUOTES) ?>"
                                                                data-status="<?= htmlspecialchars($p['status']) ?>"
                                                                data-foto="<?= htmlspecialchars($fotoPath) ?>">
                                                                <i class="fas fa-pen"></i>
                                                            </button>

                                                            <form method="POST" action="hapus_pegawai.php" style="display:inline;"
                                                                onsubmit="return confirm('Yakin ingin menghapus data pegawai \'<?= htmlspecialchars(addslashes($p['nama'])) ?>\'? Tindakan ini tidak bisa dibatalkan.');">
                                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
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

    <!-- Modal Tambah/Edit Pegawai -->
    <div class="modal fade" id="pegawaiModal" tabindex="-1" role="dialog" aria-labelledby="pegawaiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pegawaiModalLabel">Tambah Pegawai</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formPegawai" method="POST" action="proses_tambah_pegawai.php" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="pg-id">
                    <div class="modal-body">

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="font-weight-bold small">Nama Lengkap &amp; Gelar</label>
                                <input type="text" class="form-control" name="nama" id="pg-nama"
                                    placeholder="Contoh: Nurul Hidayati, S.Pd." required>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold small">NIP</label>
                                <input type="text" class="form-control" name="nip" id="pg-nip" placeholder="198309172006042012">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Jabatan</label>
                                <input type="text" class="form-control" name="jabatan" id="pg-jabatan"
                                    placeholder="Contoh: Kepala Bidang Pembinaan SD" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Bidang</label>
                                <select class="form-control" name="bidang_id" id="pg-bidang">
                                    <option value="">- Tanpa bidang -</option>
                                    <?php foreach ($daftarBidang as $b) : ?>
                                        <option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($daftarBidang)) : ?>
                                    <small class="text-muted">Belum ada data bidang. Tambahkan dulu di menu Bidang.</small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Email</label>
                                <input type="email" class="form-control" name="email" id="pg-email"
                                    placeholder="nama@disdik.sumenepkab.go.id">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-bold small">Status Kepegawaian</label>
                                <select class="form-control" name="status" id="pg-status">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non-Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold small">Foto Pegawai</label>
                            <div class="d-flex align-items-center" style="gap:16px;">
                                <img id="pg-photo-preview" class="upload-photo-preview"
                                    src="../img/undraw_profile.svg" alt="">
                                <div class="upload-photo-box flex-fill" onclick="document.getElementById('pg-foto-input').click()">
                                    <i class="fas fa-camera mr-1"></i>
                                    Klik untuk pilih foto — JPG/PNG/WEBP, maks. 2MB
                                </div>
                                <input type="file" name="foto" id="pg-foto-input" accept="image/png, image/jpeg, image/webp" style="display:none;">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                        <button class="btn btn-primary" type="submit" id="pg-submit">Simpan Data Pegawai</button>
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
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/demo/datatables-demo.js"></script>

    <!-- Script Tambah/Edit Pegawai -->
    <script>
        function openPegawaiModal(mode, btn) {
            var title = document.getElementById('pegawaiModalLabel');
            var submitBtn = document.getElementById('pg-submit');
            var form = document.getElementById('formPegawai');

            if (mode === 'tambah') {
                title.textContent = 'Tambah Pegawai';
                submitBtn.textContent = 'Simpan Data Pegawai';
                form.action = 'proses_tambah_pegawai.php';
                form.reset();
                document.getElementById('pg-id').value = '';
                document.getElementById('pg-photo-preview').src = '../img/undraw_profile.svg';
            } else if (mode === 'edit' && btn) {
                var d = btn.dataset;
                title.textContent = 'Edit Data Pegawai';
                submitBtn.textContent = 'Perbarui Data Pegawai';
                form.action = 'proses_edit_pegawai.php';

                document.getElementById('pg-id').value = d.id || '';
                document.getElementById('pg-nama').value = d.nama || '';
                document.getElementById('pg-nip').value = d.nip || '';
                document.getElementById('pg-jabatan').value = d.jabatan || '';
                document.getElementById('pg-bidang').value = d.bidang || '';
                document.getElementById('pg-email').value = d.email || '';
                document.getElementById('pg-status').value = d.status || 'aktif';
                document.getElementById('pg-photo-preview').src = d.foto || '../img/undraw_profile.svg';

                $('#pegawaiModal').modal('show');
            }
        }

        // Preview foto saat file baru dipilih (tambah maupun edit)
        document.getElementById('pg-foto-input').addEventListener('change', function () {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('pg-photo-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

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

        // ==== Filter per Bidang (memakai API kolom DataTables, kolom ke-4 = "Bidang") ====
        $(document).ready(function () {
            var table = $('#dataTable').DataTable();

            $('#filterBidang').on('change', function () {
                var nilai = this.value;
                if (nilai === '') {
                    table.column(3).search('').draw();
                } else {
                    // Exact match supaya "Pembinaan SD" tidak ikut menampilkan "Pembinaan SD Luar Biasa"
                    var regex = '^' + $.fn.dataTable.util.escapeRegex(nilai) + '$';
                    table.column(3).search(regex, true, false).draw();
                }
            });
        });
    </script>
</body>

</html>
