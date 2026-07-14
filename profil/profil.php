<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

include_once __DIR__ . '/../koneksi.php';

// ==== Ambil data profil dari database (singleton row id=1) ====
$profil = [];
$result = mysqli_query($koneksi, "SELECT * FROM profil WHERE id = 1 LIMIT 1");
if ($result && mysqli_num_rows($result) > 0) {
    $profil = mysqli_fetch_assoc($result);
} else {
    // Baris belum ada — insert baris kosong agar UPDATE bisa jalan
    mysqli_query($koneksi, "INSERT INTO profil (id) VALUES (1)");
}

// ==== Proses simpan ====
$notif = $_GET['notif'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'simpan') {

    $selayang  = mysqli_real_escape_string($koneksi, trim($_POST['selayang_pandang'] ?? ''));
    $visi      = mysqli_real_escape_string($koneksi, trim($_POST['visi'] ?? ''));
    $misi      = mysqli_real_escape_string($koneksi, trim($_POST['misi'] ?? ''));
    $alamat    = mysqli_real_escape_string($koneksi, trim($_POST['alamat'] ?? ''));
    $telepon   = mysqli_real_escape_string($koneksi, trim($_POST['telepon'] ?? ''));
    $email     = mysqli_real_escape_string($koneksi, trim($_POST['email'] ?? ''));
    $instagram = mysqli_real_escape_string($koneksi, trim($_POST['instagram'] ?? ''));
    $youtube   = mysqli_real_escape_string($koneksi, trim($_POST['youtube'] ?? ''));
    $facebook  = mysqli_real_escape_string($koneksi, trim($_POST['facebook'] ?? ''));

    $sql = "UPDATE profil SET
                selayang_pandang = '$selayang',
                visi             = '$visi',
                misi             = '$misi',
                alamat           = '$alamat',
                telepon          = '$telepon',
                email            = '$email',
                instagram        = '$instagram',
                youtube          = '$youtube',
                facebook         = '$facebook'
            WHERE id = 1";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: profil.php?notif=sukses");
    } else {
        header("Location: profil.php?notif=gagal");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Profil Instansi - Disdik Sumenep</title>
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
        .btn-primary:hover { background-color: #162F55 !important; }

        /* Tabs */
        .profil-tabs { border-bottom: 2px solid #eee0e3; }
        .profil-tabs .nav-link { color: #7f6267 !important; font-weight: 700; font-size: 0.85rem; border: none; border-bottom: 3px solid transparent; padding: 12px 4px; margin-right: 26px; }
        .profil-tabs .nav-link i { margin-right: 6px; }
        .profil-tabs .nav-link.active { color: #162F55 !important; border-bottom-color: #162F55; background: transparent; }

        .form-group label { font-weight: 700; font-size: 0.82rem; color: #0B1F3A; }
        .form-hint { font-size: 0.75rem; color: #9a8a8e; margin-top: 4px; }

        .sosmed-input-group .input-group-text { background: #fff5f7; border-color: #ced4da; width: 46px; justify-content: center; }
        .sosmed-input-group.instagram .input-group-text { color: #162F55; }
        .sosmed-input-group.youtube .input-group-text { color: #1E3F70; }
        .sosmed-input-group.facebook .input-group-text { color: #1565c0; }

        /* Preview */
        .preview-card { border: 1px solid #eee0e3; border-radius: 0.5rem; padding: 20px; background: #fff5f7; }
        .preview-card h6 { font-weight: 800; color: #162F55; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .preview-logo { width: 56px; height: 56px; border-radius: 50%; background: #162F55; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px; margin-bottom: 10px; }
        .preview-sosmed a { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 1px solid #eee0e3; margin-right: 6px; color: #162F55 !important; }

        /* Notifikasi */
        .alert-notif { border-radius: 8px; border: none; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">

    <!-- ===== SIDEBAR ===== -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
            <div class="sidebar-brand-icon">
                <i><img src="../img/Logo1.png" alt="" style="width:60px;height:60px;object-fit:contain;"></i>
            </div>
            <div class="d-flex flex-column" style="color:#fff !important;">
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

        <!-- Nav Item - Sakip -->
        <li class="nav-item">
            <a class="nav-link" href="../sakip/sakip.php">
                <i class="fas fa-fw fa-file-contract"></i>
                <span>Sakip</span></a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Instansi</div>

        <li class="nav-item active">
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

            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Profil Instansi</h1>
                    <div class="text-muted small">Kelola informasi umum, visi misi, dan media sosial Dinas Pendidikan Kabupaten Sumenep.</div>
                </div>
                <!-- Tombol simpan submit form -->
                <button type="submit" form="formProfil" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm text-white">
                    <i class="fas fa-save fa-sm text-white"></i> Simpan Perubahan
                </button>
            </div>

            <!-- Notifikasi -->
            <?php if ($notif === 'sukses'): ?>
            <div class="alert alert-success alert-dismissible alert-notif shadow-sm mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                Profil instansi berhasil disimpan.
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php elseif ($notif === 'gagal'): ?>
            <div class="alert alert-danger alert-dismissible alert-notif shadow-sm mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Gagal menyimpan profil. Silakan coba lagi.
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            <?php endif; ?>

            <!-- FORM — id="formProfil" untuk trigger submit dari tombol luar -->
            <form id="formProfil" action="profil.php" method="POST">
                <input type="hidden" name="aksi" value="simpan">

                <div class="row">

                    <!-- FORM PROFIL -->
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <ul class="nav profil-tabs" id="profilTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="tab-umum-tab" data-toggle="tab" href="#tab-umum" role="tab">
                                            <i class="fas fa-info-circle"></i>Umum
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab-visimisi-tab" data-toggle="tab" href="#tab-visimisi" role="tab">
                                            <i class="fas fa-bullseye"></i>Visi &amp; Misi
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="tab-sosmed-tab" data-toggle="tab" href="#tab-sosmed" role="tab">
                                            <i class="fas fa-share-alt"></i>Media Sosial
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="profilTabContent">

                                    <!-- TAB UMUM -->
                                    <div class="tab-pane fade show active" id="tab-umum" role="tabpanel">
                                        <div class="form-group">
                                            <label>Selayang Pandang</label>
                                            <textarea class="form-control" rows="5" name="selayang_pandang"><?= htmlspecialchars($profil['selayang_pandang'] ?? '') ?></textarea>
                                            <div class="form-hint">Ringkasan singkat mengenai profil instansi, ditampilkan di halaman "Tentang Kami" website.</div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-8">
                                                <label>Alamat</label>
                                                <input type="text" class="form-control" name="alamat"
                                                    value="<?= htmlspecialchars($profil['alamat'] ?? '') ?>">
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label>Telepon</label>
                                                <input type="text" class="form-control" name="telepon"
                                                    value="<?= htmlspecialchars($profil['telepon'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Email</label>
                                            <input type="email" class="form-control" name="email"
                                                value="<?= htmlspecialchars($profil['email'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <!-- TAB VISI MISI -->
                                    <div class="tab-pane fade" id="tab-visimisi" role="tabpanel">
                                        <div class="form-group">
                                            <label>Visi</label>
                                            <textarea class="form-control" rows="3" name="visi"><?= htmlspecialchars($profil['visi'] ?? '') ?></textarea>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Misi</label>
                                            <textarea class="form-control" rows="6" name="misi"><?= htmlspecialchars($profil['misi'] ?? '') ?></textarea>
                                            <div class="form-hint">Gunakan penomoran (1. 2. 3. dst) untuk setiap poin misi, satu poin per baris.</div>
                                        </div>
                                    </div>

                                    <!-- TAB MEDIA SOSIAL -->
                                    <div class="tab-pane fade" id="tab-sosmed" role="tabpanel">
                                        <div class="form-group">
                                            <label>Instagram</label>
                                            <div class="input-group sosmed-input-group instagram">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fab fa-instagram"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="instagram"
                                                    value="<?= htmlspecialchars($profil['instagram'] ?? '') ?>"
                                                    placeholder="https://instagram.com/...">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>YouTube</label>
                                            <div class="input-group sosmed-input-group youtube">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fab fa-youtube"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="youtube"
                                                    value="<?= htmlspecialchars($profil['youtube'] ?? '') ?>"
                                                    placeholder="https://youtube.com/@...">
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label>Facebook</label>
                                            <div class="input-group sosmed-input-group facebook">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fab fa-facebook-f"></i></span>
                                                </div>
                                                <input type="text" class="form-control" name="facebook"
                                                    value="<?= htmlspecialchars($profil['facebook'] ?? '') ?>"
                                                    placeholder="https://facebook.com/...">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PREVIEW -->
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-dark">Pratinjau Tampilan Publik</h6>
                            </div>
                            <div class="card-body">
                                <div class="preview-card text-center mb-3">
                                    <div class="preview-logo mx-auto"><i class="fas fa-graduation-cap"></i></div>
                                    <div class="font-weight-bold" style="color:#4d1e2a;">Dinas Pendidikan Kabupaten Sumenep</div>
                                    <div class="small text-muted"><?= htmlspecialchars($profil['email'] ?? '-') ?></div>
                                </div>
                                <h6>Alamat</h6>
                                <p class="small mb-3"><?= htmlspecialchars($profil['alamat'] ?? '-') ?></p>
                                <h6>Telepon</h6>
                                <p class="small mb-3"><?= htmlspecialchars($profil['telepon'] ?? '-') ?></p>
                                <h6>Media Sosial</h6>
                                <div class="preview-sosmed mb-2">
                                    <?php if (!empty($profil['instagram'])): ?>
                                    <a href="<?= htmlspecialchars($profil['instagram']) ?>" target="_blank" title="Instagram">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($profil['youtube'])): ?>
                                    <a href="<?= htmlspecialchars($profil['youtube']) ?>" target="_blank" title="YouTube">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($profil['facebook'])): ?>
                                    <a href="<?= htmlspecialchars($profil['facebook']) ?>" target="_blank" title="Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (empty($profil['instagram']) && empty($profil['youtube']) && empty($profil['facebook'])): ?>
                                    <span class="text-muted small">Belum ada media sosial</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Terakhir Diperbarui</div>
                                <div class="small text-muted">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?php if (!empty($profil['updated_at'])): ?>
                                        <?= date('d M Y, H:i', strtotime($profil['updated_at'])) ?>
                                        oleh <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin') ?>
                                    <?php else: ?>
                                        Belum pernah diperbarui
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol simpan di bawah untuk mobile -->
                        <button type="submit" form="formProfil" class="btn btn-primary btn-block d-sm-none">
                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                        </button>
                    </div>

                </div>
            </form>

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

<!-- Modal Logout -->
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
</body>
</html>
