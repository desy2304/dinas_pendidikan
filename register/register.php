<?php
session_start();
include_once("proses_register.php");

$errors = [];
$old = ['name' => '', 'email' => ''];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old['name'] = $_POST['name'] ?? '';
    $old['email'] = $_POST['email'] ?? '';
    checkRegister($_POST, $errors);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Daftar Akun Admin - Dinas Pendidikan Kabupaten Sumenep</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
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

        body {
            background: linear-gradient(135deg, #1E3F70 100%, #162F55 60%, #0B1F3A 0%);
            min-height: 100vh;
        }

        .btn-primary {
            background-color: #162F55 !important;
            border-color: #162F55 !important;
        }

        .btn-primary:hover {
            background-color: #1E3F70 !important;
            border-color: #1E3F70 !important;
        }

        .register-card {
            margin: 60px 0;
            border-radius: 0.9rem;
            overflow: hidden;
        }

        .register-side {
            background: linear-gradient(135deg, #1E3F70 100%, #162F55 60%, #0B1F3A 0%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 40px 30px;
            text-align: center;
            height: 100%;
        }

        .register-side img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 18px;
            background: #fff;
            border-radius: 50%;
            padding: 10px;
        }

        .register-side h5 {
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .register-side p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 8px;
        }

        .form-control-user:focus {
            border-color: #0B1F3A;
            box-shadow: 0 0 0 0.2rem rgba(11, 31, 58, 0.2);
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-11 col-md-11">

                <div class="card o-hidden border-0 shadow-lg register-card">
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <div class="col-lg-5 d-none d-lg-flex">
                                <div class="register-side w-100">
                                    <img src="../img/Logo1.png" alt="Logo Dinas Pendidikan">
                                    <h5>DINAS PENDIDIKAN<br>KABUPATEN SUMENEP</h5>
                                    <p>Buat akun admin baru untuk mengelola konten website dinas.</p>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-1">Daftar Akun Admin</h1>
                                        <p class="text-muted small mb-4">Lengkapi data di bawah untuk membuat akun.</p>
                                    </div>

                                    <?php if (!empty($errors)) : ?>
                                        <div class="alert alert-danger py-2 small">
                                            <?php foreach ($errors as $err) : ?>
                                                <div><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($err) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($_GET['sukses'])) : ?>
                                        <div class="alert alert-success py-2 small">
                                            <i class="fas fa-check-circle mr-1"></i> Akun berhasil dibuat. Silakan login.
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="user">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-gray-600">Nama Lengkap</label>
                                            <input type="text" class="form-control form-control-user" name="name"
                                                placeholder="Nama lengkap admin" value="<?= htmlspecialchars($old['name']) ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-gray-600">Email</label>
                                            <input type="email" class="form-control form-control-user" name="email"
                                                placeholder="admin@disdiksumenep.go.id" value="<?= htmlspecialchars($old['email']) ?>" required>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-6 mb-3 mb-sm-0">
                                                <label class="small font-weight-bold text-gray-600">Password</label>
                                                <input type="password" class="form-control form-control-user"
                                                    name="password" placeholder="Minimal 6 karakter" required>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="small font-weight-bold text-gray-600">Ulangi Password</label>
                                                <input type="password" class="form-control form-control-user"
                                                    name="password_confirm" placeholder="Ulangi password" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-user-plus mr-1"></i> Daftar Akun
                                        </button>
                                    </form>

                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="../login/login.php">Sudah punya akun? Login di sini</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

</body>

</html>
