<?php
session_start();
include_once("proses_login.php");

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    checkLogin($_POST, $errors);
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

    <title>Login Admin - Dinas Pendidikan Kabupaten Sumenep</title>

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

        .btn-primary,
        .bg-gradient-primary {
            background-color: #162F55 !important;
            border-color: #162F55 !important;
            background-image: none !important;
        }

        .btn-primary:hover {
            background-color: #0B1F3A !important;
            border-color: #0B1F3A !important;
        }

        .login-card {
            margin-top: 90px;
            border-radius: 0.9rem;
            overflow: hidden;
        }

        .login-side {
            background: linear-gradient(160deg, #0B1F3A 0%, #162F55 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 40px 30px;
            text-align: center;
            height: 100%;
        }

        .login-side img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 18px;
            background: #fff;
            border-radius: 50%;
            padding: 10px;
        }

        .login-side h5 {
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .login-side p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 8px;
        }

        .form-control-user:focus {
            border-color: #162F55;
            box-shadow: 0 0 0 0.2rem rgba(170, 28, 65, 0.2);
        }

        .btn-signup-outline {
            border: 1.5px solid #162F55;
            color: #162F55;
            background: #fff;
            font-weight: 700;
            border-radius: 10rem;
            padding: 0.75rem 1rem;
        }

        .btn-signup-outline:hover {
            background: #162F55;
            color: #fff;
        }

        .divider-or {
            display: flex;
            align-items: center;
            text-align: center;
            color: #b48a94;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 18px 0;
        }

        .divider-or::before,
        .divider-or::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #eee0e3;
        }

        .divider-or:not(:empty)::before {
            margin-right: 0.75em;
        }

        .divider-or:not(:empty)::after {
            margin-left: 0.75em;
        }
    </style>

</head>

<body>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-9 col-lg-11 col-md-11">

                <div class="card o-hidden border-0 shadow-lg login-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row no-gutters">
                            <div class="col-lg-5 d-none d-lg-flex">
                                <div class="login-side w-100">
                                    <img src="../uploads/Logo1.png" alt="Logo Dinas Pendidikan">
                                    <img src="../uploads /Logo1.png" alt="Logo Dinas Pendidikan">
                                    <h5>DINAS PENDIDIKAN<br>KABUPATEN SUMENEP</h5>
                                    <p>Sistem Informasi &amp; Kehumasan â€” Panel Admin</p>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-1">Selamat Datang Kembali</h1>
                                        <p class="text-muted small mb-4">Masuk untuk mengelola konten website dinas.</p>
                                    </div>

                                    <?php if (!empty($errors)) : ?>
                                        <div class="alert alert-danger py-2 small">
                                            <?php foreach ($errors as $err) : ?>
                                                <div><i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($err) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="user">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-gray-600">Email</label>
                                            <input type="email" class="form-control form-control-user"
                                                placeholder="admin@disdiksumenep.go.id" name="email"
                                                value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                                required autofocus>
                                        </div>
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-gray-600">Password</label>
                                            <input type="password" class="form-control form-control-user"
                                                placeholder="Masukkan password" name="password" required>
                                        </div>
                                        <div class="form-group d-flex justify-content-between align-items-center">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Ingat saya</label>
                                            </div>
                                            <a class="small" href="forgot-password.php">Lupa password?</a>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                                        </button>
                                    </form>

                                    <div class="divider-or">Belum punya akun</div>

                                    <a href="../register/register.php" class="btn btn-signup-outline btn-block">
                                        <i class="fas fa-user-plus mr-1"></i> Daftar Akun Admin Baru
                                    </a>
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
