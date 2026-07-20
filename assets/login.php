<?php
session_start();

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Portal Inventaris Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-layout w-100">
    <!-- LEFT PANEL: Image & Brand Info -->
    <div class="login-left" style="background-image: url('bg-login.jpg');">
        <div class="login-left-content">
            <h1 class="fw-bold mb-3">Portal Inventaris & Perawatan</h1>
            <p class="lead mb-4" style="opacity: 0.9;">
                Sistem pengelolaan aset, perangkat teknologi informasi, dan monitoring perawatan rutin secara realtime di lingkungan PT Pelabuhan Indonesia.
            </p>
            <div class="d-flex align-items-center gap-2 mb-2" style="font-size: 0.85rem; opacity: 0.7;">
                <i class="bi bi-shield-check"></i> Secure SSL Connection Active
            </div>
            <div class="border-top pt-3 mt-4" style="font-size: 0.8rem; opacity: 0.6; border-color: rgba(255,255,255,0.15) !important;">
                &copy; <?= date('Y') ?> PT Pelabuhan Indonesia (Persero). All rights reserved.
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Login Form -->
    <div class="login-right">
        <div class="login-box">
            <!-- Pelindo Corporate Branding -->
            <div class="login-logo mb-4">
                <img src="logo-pelindo.png" alt="Logo Pelindo Multi Terminal" style="height: 52px; object-fit: contain;">
            </div>

            <div class="mb-4">
                <h4 class="fw-bold text-dark mb-1">Selamat Datang</h4>
                <p class="text-muted" style="font-size: 0.9rem;">Silakan masuk untuk mengakses sistem inventaris.</p>
            </div>

            <form action="proses_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control border-start-0 bg-light"
                            placeholder="Masukkan username Anda"
                            required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0" for="password">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control border-start-0 border-end-0 bg-light"
                            placeholder="Masukkan password Anda"
                            required>
                        <button
                            class="btn btn-light border border-start-0 bg-light"
                            type="button"
                            onclick="showPassword()"
                            style="border-color: #cbd5e1;">
                            <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-modern-primary w-100 py-2.5 mb-3"
                    style="font-size: 1rem; font-weight: 600;">
                    Masuk ke Sistem
                </button>

                <div class="text-center">
                    <a href="lupa_password.php" class="text-decoration-none text-muted" style="font-size: 0.85rem;">
                        Lupa Password?
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPassword(){
    const x = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");
    if(x.type === "password"){
        x.type = "text";
        icon.className = "bi bi-eye-slash text-muted";
    }else{
        x.type = "password";
        icon.className = "bi bi-eye text-muted";
    }
}
</script>

<?php if (isset($_SESSION['error_login'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '<?= $_SESSION['error_login'] ?>',
        confirmButtonColor: '#005cb9'
    });
</script>
<?php unset($_SESSION['error_login']); ?>
<?php endif; ?>

</body>
</html>