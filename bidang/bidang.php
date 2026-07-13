<?php
session_start();
include_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// ==== Notifikasi ====
$notif = $_GET['notif'] ?? null;
$notifMsg = [
    'sukses_tambah' => ['type' => 'success', 'text' => 'Bidang berhasil ditambahkan.',      'icon' => 'fa-check-circle'],
    'sukses_edit'   => ['type' => 'success', 'text' => 'Bidang berhasil diperbarui.',        'icon' => 'fa-check-circle'],
    'sukses_hapus'  => ['type' => 'success', 'text' => 'Bidang berhasil dihapus.',           'icon' => 'fa-check-circle'],
    'gagal_kosong'  => ['type' => 'danger',  'text' => 'Nama bidang wajib diisi.',           'icon' => 'fa-exclamation-circle'],
    'gagal_simpan'  => ['type' => 'danger',  'text' => 'Gagal menyimpan data bidang.',       'icon' => 'fa-exclamation-circle'],
    'gagal_hapus'   => ['type' => 'danger',  'text' => 'Gagal menghapus bidang.',            'icon' => 'fa-exclamation-circle'],
    'gagal_pegawai' => ['type' => 'warning', 'text' => 'Bidang tidak bisa dihapus karena masih memiliki pegawai aktif.', 'icon' => 'fa-exclamation-triangle'],
];

// ==== Statistik ====
$totalBidang = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM bidang"))
    $totalBidang = mysqli_fetch_assoc($r)['jml'];

$totalPegawai = 0;
if ($r = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pegawai WHERE status = 'aktif'"))
    $totalPegawai = mysqli_fetch_assoc($r)['jml'];

$rataRata = $totalBidang > 0 ? round($totalPegawai / $totalBidang) : 0;

// ==== Ambil daftar bidang + jumlah pegawai tiap bidang ====
$daftarBidang = [];
$sql = "SELECT b.*,
            COUNT(p.id) AS jumlah_pegawai
        FROM bidang b
        LEFT JOIN pegawai p ON p.bidang_id = b.id AND p.status = 'aktif'
        GROUP BY b.id
        ORDER BY b.created_at ASC";
if ($r = mysqli_query($koneksi, $sql)) {
    while ($row = mysqli_fetch_assoc($r))
        $daftarBidang[] = $row;
}

// Icon per urutan bidang (berulang jika lebih dari 6)
$icons = ['fa-sitemap','fa-child','fa-user-graduate','fa-laptop-code','fa-coins','fa-users'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Data Bidang - Disdik Sumenep</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
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
        .btn-primary:hover { background-color: #8a1535 !important; }

        /* Card Bidang */
        .bidang-card { border:1px solid #eee0e3; border-radius:.5rem; margin-bottom:18px; overflow:hidden; }
        .bidang-card-header { background:#fff5f7; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; flex-wrap:wrap; gap:10px; }
        .bidang-card-header:hover { background:#fce9ed; }
        .bidang-icon { width:44px; height:44px; border-radius:.5rem; background:#162F55; color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .bidang-nama { font-weight:800; color:#4d1e2a; font-size:1rem; margin-bottom:2px; }
        .bidang-meta { font-size:.78rem; color:#9a8a8e; }
        .bidang-pegawai-count { font-size:.75rem; font-weight:700; background:#162F55; color:#fff; padding:4px 11px; border-radius:20px; }
        .bidang-card-body { padding:18px 20px; border-top:1px solid #eee0e3; }
        .bidang-section-lbl { font-size:.72rem; font-weight:800; color:#162F55; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .bidang-section-text { font-size:.88rem; color:#4d1e2a; line-height:1.6; margin-bottom:16px; }
        .bidang-actions { display:flex; gap:8px; }
        .chevron-toggle { transition:transform .2s; color:#9a8a8e; }
        .bidang-card-header.expanded .chevron-toggle { transform:rotate(180deg); }

        /* Modal header */
        .modal-header-merah { background:#162F55; }
        .modal-header-merah .modal-title,
        .modal-header-merah .close { color:#fff; text-shadow:none; opacity:1; }

        .alert-notif { border-radius:8px; border:none; }

        /* Stat border */
        .bl-merah  { border-left:4px solid #162F55 !important; }
        .bl-kuning { border-left:4px solid #f6c23e !important; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

    <!-- ===== SIDEBAR ===== -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../dashboard/index.php">
            <div class="sidebar-brand-icon">
                <i><img src="../img/Logo1.png" alt="" style="width:60px;height:60px;object-fit:contain;"></i>
            </div>
            <div class="d-flex flex-column" style="color:#fff !important;">
                <div style="font-size:.7rem;">Dinas Pendidikan</div>
                <div style="font-size:.5rem;"><i>Kabupaten Sumenep</i></div>
            </div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="../dashboard/index.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../berita/berita.php"><i class="fas fa-fw fa-newspaper"></i><span>Berita</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pengumuman/pengumuman.php"><i class="fas fa-fw fa-bullhorn"></i><span>Pengumuman</span></a></li>
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
        <li class="nav-item"><a class="nav-link" href="../pengaduan/pengaduan.php"><i class="fas fa-fw fa-exclamation-triangle"></i><span>Pengaduan</span></a></li>
        <!-- Nav Item - Sakip -->
        <li class="nav-item">
            <a class="nav-link" href="../sakip/sakip.php">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>Sakip</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Instansi</div>
        <li class="nav-item"><a class="nav-link" href="../profil/profil.php"><i class="fas fa-fw fa-user"></i><span>Profil</span></a></li>
        <li class="nav-item"><a class="nav-link" href="../pegawai/pegawai.php"><i class="fas fa-fw fa-user-friends"></i><span>Pegawai</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="../bidang/bidang.php"><i class="fas fa-fw fa-building"></i><span>Bidang</span></a></li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>
    <!-- ===== END SIDEBAR ===== -->

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
                    <h1 class="h3 mb-0 text-gray-800">Data Bidang</h1>
                    <div class="text-muted small">Kelola bidang / unit kerja di lingkungan Dinas Pendidikan Kabupaten Sumenep.</div>
                </div>
                <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white"
                    data-toggle="modal" data-target="#modalTambah">
                    <i class="fas fa-plus fa-sm text-white"></i> Tambah Bidang
                </button>
            </div>

            <!-- Notifikasi -->
            <?php if ($notif && isset($notifMsg[$notif])): ?>
            <div class="alert alert-<?= $notifMsg[$notif]['type'] ?> alert-dismissible alert-notif shadow-sm mb-4">
                <i class="fas <?= $notifMsg[$notif]['icon'] ?> mr-2"></i>
                <?= $notifMsg[$notif]['text'] ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card bl-merah shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#162F55;">Total Bidang</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalBidang ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-building fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card bl-merah shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#162F55;">Total Pegawai Aktif</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPegawai ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-user-friends fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card bl-kuning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rata-rata Pegawai / Bidang</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $rataRata ?></div>
                                </div>
                                <div class="col-auto"><i class="fas fa-chart-pie fa-2x text-gray-300"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Bidang -->
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="border-left:4px solid #162F55;">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="fas fa-list mr-2" style="color:#162F55;"></i>Daftar Bidang
                    </h6>
                </div>
                <div class="card-body">

                    <?php if (empty($daftarBidang)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3 d-block text-gray-300"></i>
                        Belum ada data bidang.<br>
                        <button class="btn btn-primary btn-sm mt-2" data-toggle="modal" data-target="#modalTambah">
                            <i class="fas fa-plus mr-1"></i>Tambah bidang sekarang
                        </button>
                    </div>
                    <?php else: ?>
                    <?php foreach ($daftarBidang as $i => $b): ?>
                    <div class="bidang-card">
                        <div class="bidang-card-header" onclick="toggleBidang(this)">
                            <div class="d-flex align-items-center" style="gap:14px;">
                                <div class="bidang-icon">
                                    <i class="fas <?= $icons[$i % count($icons)] ?>"></i>
                                </div>
                                <div>
                                    <div class="bidang-nama"><?= htmlspecialchars($b['nama']) ?></div>
                                    <div class="bidang-meta">
                                        Dibuat <?= date('d M Y', strtotime($b['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center" style="gap:12px;">
                                <span class="bidang-pegawai-count">
                                    <i class="fas fa-user-friends mr-1"></i>
                                    <?= $b['jumlah_pegawai'] ?> Pegawai
                                </span>
                                <i class="fas fa-chevron-down chevron-toggle"></i>
                            </div>
                        </div>
                        <div class="bidang-card-body" style="display:none;">
                            <div class="bidang-section-lbl">Tugas</div>
                            <div class="bidang-section-text">
                                <?= !empty($b['tugas']) ? nl2br(htmlspecialchars($b['tugas'])) : '<span class="text-muted">Belum diisi</span>' ?>
                            </div>
                            <div class="bidang-section-lbl">Fungsi</div>
                            <div class="bidang-section-text">
                                <?= !empty($b['fungsi']) ? nl2br(htmlspecialchars($b['fungsi'])) : '<span class="text-muted">Belum diisi</span>' ?>
                            </div>
                            <div class="bidang-actions">
                                <button class="btn btn-sm btn-warning btn-edit"
                                    onclick="event.stopPropagation();"
                                    data-id="<?= $b['id'] ?>"
                                    data-nama="<?= htmlspecialchars($b['nama'], ENT_QUOTES) ?>"
                                    data-tugas="<?= htmlspecialchars($b['tugas'] ?? '', ENT_QUOTES) ?>"
                                    data-fungsi="<?= htmlspecialchars($b['fungsi'] ?? '', ENT_QUOTES) ?>">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger btn-hapus"
                                    onclick="event.stopPropagation();"
                                    data-id="<?= $b['id'] ?>"
                                    data-nama="<?= htmlspecialchars($b['nama'], ENT_QUOTES) ?>"
                                    data-jumlah="<?= $b['jumlah_pegawai'] ?>">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>

        </div>
        <!-- END PAGE CONTENT -->

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

<!-- ===== MODAL TAMBAH ===== -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="labelTambah" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-merah">
                <h5 class="modal-title" id="labelTambah">
                    <i class="fas fa-plus-circle mr-2"></i>Tambah Bidang
                </h5>
                <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="proses_bidang.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Nama Bidang <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control"
                               placeholder="cth. Pembinaan SD" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tugas</label>
                        <textarea name="tugas" class="form-control" rows="3"
                                  placeholder="Uraikan tugas pokok bidang ini..."></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Fungsi</label>
                        <textarea name="fungsi" class="form-control" rows="3"
                                  placeholder="Uraikan fungsi bidang ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan Bidang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDIT ===== -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="labelEdit" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#f6a000;">
                <h5 class="modal-title text-white" id="labelEdit">
                    <i class="fas fa-edit mr-2"></i>Edit Bidang
                </h5>
                <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="proses_bidang.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold small">Nama Bidang <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="editNama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Tugas</label>
                        <textarea name="tugas" id="editTugas" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Fungsi</label>
                        <textarea name="fungsi" id="editFungsi" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save mr-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL HAPUS ===== -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#e74a3b;">
                <h5 class="modal-title text-white"><i class="fas fa-trash mr-2"></i>Hapus Bidang</h5>
                <button class="close text-white" type="button" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p>Yakin ingin menghapus bidang:</p>
                <p class="font-weight-bold" id="hapusNama"></p>
                <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <form action="proses_bidang.php" method="POST">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" id="hapusId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger ml-2">
                        <i class="fas fa-trash mr-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL LOGOUT ===== -->
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
// Toggle accordion bidang
function toggleBidang(headerEl) {
    var body = headerEl.nextElementSibling;
    var isOpen = body.style.display === 'block';
    body.style.display = isOpen ? 'none' : 'block';
    headerEl.classList.toggle('expanded', !isOpen);
}

// Tombol Edit — isi modal edit
document.querySelectorAll('.btn-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('editId').value    = this.dataset.id;
        document.getElementById('editNama').value  = this.dataset.nama;
        document.getElementById('editTugas').value = this.dataset.tugas;
        document.getElementById('editFungsi').value = this.dataset.fungsi;
        $('#modalEdit').modal('show');
    });
});

// Tombol Hapus — isi modal hapus
document.querySelectorAll('.btn-hapus').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('hapusId').value           = this.dataset.id;
        document.getElementById('hapusNama').textContent   = '"' + this.dataset.nama + '"';
        $('#modalHapus').modal('show');
    });
});
</script>

</body>
</html>
