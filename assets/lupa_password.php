<?php
session_start();

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error   = '';
$success = '';
$step    = 1; // step 1 = verifikasi username, step 2 = form ganti password

// Tangkap step dari session jika sudah terverifikasi
if (isset($_SESSION['reset_username'])) {
    $step = 2;
}

// Proses POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    include "../config/koneksi.php";

    // --- STEP 1: Verifikasi username ---
    if (isset($_POST['action']) && $_POST['action'] === 'verify_username') {
        $username = trim(mysqli_real_escape_string($conn, $_POST['username']));

        $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");

        if (mysqli_num_rows($query) > 0) {
            $_SESSION['reset_username'] = $username;
            $step = 2;
        } else {
            $error = 'Username tidak ditemukan. Silakan periksa kembali.';
            $step  = 1;
        }
    }

    // --- STEP 2: Ganti password ---
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        if (!isset($_SESSION['reset_username'])) {
            $error = 'Sesi tidak valid. Silakan mulai ulang.';
            $step  = 1;
        } else {
            $new_password     = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($new_password) || empty($confirm_password)) {
                $error = 'Password baru dan konfirmasi password tidak boleh kosong.';
                $step  = 2;
            } elseif ($new_password !== $confirm_password) {
                $error = 'Konfirmasi password tidak cocok.';
                $step  = 2;
            } elseif (strlen($new_password) < 6) {
                $error = 'Password minimal 6 karakter.';
                $step  = 2;
            } else {
                $username     = $_SESSION['reset_username'];
                $new_password = mysqli_real_escape_string($conn, $new_password);

                $update = mysqli_query($conn, "UPDATE admin SET password='$new_password' WHERE username='$username'");

                if ($update) {
                    unset($_SESSION['reset_username']);
                    $success = 'Password berhasil diubah! Silakan login dengan password baru Anda.';
                    $step    = 3;
                } else {
                    $error = 'Gagal mengubah password. Silakan coba lagi.';
                    $step  = 2;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Portal Inventaris Pelindo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* STEP INDICATOR */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .step-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            border: 2px solid #cbd5e1;
            background: #fff;
            color: #94a3b8;
            transition: all 0.3s ease;
        }

        .step-circle.active {
            background: var(--pelindo-primary);
            border-color: var(--pelindo-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 92, 185, 0.3);
        }

        .step-circle.done {
            background: #22c55e;
            border-color: #22c55e;
            color: #fff;
        }

        .step-label {
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 500;
            white-space: nowrap;
        }

        .step-label.active {
            color: var(--pelindo-primary);
            font-weight: 600;
        }

        .step-label.done {
            color: #22c55e;
            font-weight: 600;
        }

        .step-connector {
            width: 56px;
            height: 2px;
            background: #e2e8f0;
            margin: 0 6px;
            margin-bottom: 22px;
            transition: background 0.3s ease;
        }

        .step-connector.done {
            background: #22c55e;
        }

        /* SUCCESS ANIMATION */
        .success-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 24px rgba(34, 197, 94, 0.35);
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            0%   { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* FORM FADE-IN */
        .form-step {
            animation: fadeSlideUp 0.4s ease;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* BACK LINK */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #64748b;
            text-decoration: none;
            margin-bottom: 20px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--pelindo-primary);
        }

        /* PASSWORD STRENGTH INDICATOR */
        .password-strength-bar {
            height: 4px;
            border-radius: 4px;
            background: #e2e8f0;
            margin-top: 8px;
            overflow: hidden;
        }

        .password-strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease, background 0.3s ease;
            width: 0%;
        }

        .strength-weak   { width: 33%; background: #ef4444; }
        .strength-medium { width: 66%; background: #f59e0b; }
        .strength-strong { width: 100%; background: #22c55e; }

        .strength-label {
            font-size: 0.75rem;
            margin-top: 4px;
        }
    </style>
</head>

<body>

<div class="login-layout w-100">
    <!-- LEFT PANEL: Image & Brand Info -->
    <div class="login-left" style="background-image: url('bg-login.jpg');">
        <div class="login-left-content">
            <h1 class="fw-bold mb-3">Reset Password</h1>
            <p class="lead mb-4" style="opacity: 0.9;">
                Masukkan username Anda untuk memverifikasi identitas, kemudian atur password baru dengan aman.
            </p>
            <div class="d-flex align-items-center gap-2 mb-2" style="font-size: 0.85rem; opacity: 0.7;">
                <i class="bi bi-shield-lock"></i> Proses reset bersifat aman dan terlindungi.
            </div>
            <div class="border-top pt-3 mt-4" style="font-size: 0.8rem; opacity: 0.6; border-color: rgba(255,255,255,0.15) !important;">
                &copy; <?= date('Y') ?> PT Pelabuhan Indonesia (Persero). All rights reserved.
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: Reset Password Form -->
    <div class="login-right">
        <div class="login-box">
            <!-- Logo -->
            <div class="login-logo mb-4">
                <img src="logo-pelindo.png" alt="Logo Pelindo" style="height: 52px; object-fit: contain;">
            </div>

            <!-- Back to Login -->
            <a href="login.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Login
            </a>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <!-- Step 1 -->
                <div class="step-item">
                    <div class="step-circle <?= ($step >= 1) ? ($step > 1 ? 'done' : 'active') : '' ?>">
                        <?= $step > 1 ? '<i class="bi bi-check"></i>' : '1' ?>
                    </div>
                    <span class="step-label <?= ($step >= 1) ? ($step > 1 ? 'done' : 'active') : '' ?>">Verifikasi</span>
                </div>
                <div class="step-connector <?= $step > 1 ? 'done' : '' ?>"></div>
                <!-- Step 2 -->
                <div class="step-item">
                    <div class="step-circle <?= ($step >= 2) ? ($step > 2 ? 'done' : 'active') : '' ?>">
                        <?= $step > 2 ? '<i class="bi bi-check"></i>' : '2' ?>
                    </div>
                    <span class="step-label <?= ($step >= 2) ? ($step > 2 ? 'done' : 'active') : '' ?>">Password Baru</span>
                </div>
                <div class="step-connector <?= $step > 2 ? 'done' : '' ?>"></div>
                <!-- Step 3 -->
                <div class="step-item">
                    <div class="step-circle <?= $step === 3 ? 'done' : '' ?>">
                        <?= $step === 3 ? '<i class="bi bi-check"></i>' : '3' ?>
                    </div>
                    <span class="step-label <?= $step === 3 ? 'done' : '' ?>">Selesai</span>
                </div>
            </div>

            <!-- Alert Error -->
            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="font-size: 0.88rem; border-radius: 10px;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- ================================================ -->
            <!-- STEP 1: Verifikasi Username -->
            <!-- ================================================ -->
            <?php if ($step === 1): ?>
            <div class="form-step">
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Verifikasi Akun</h4>
                    <p class="text-muted" style="font-size: 0.9rem;">Masukkan username akun Anda untuk melanjutkan.</p>
                </div>

                <form action="lupa_password.php" method="POST">
                    <input type="hidden" name="action" value="verify_username">

                    <div class="mb-4">
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
                                autocomplete="username"
                                required>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle"></i> Username harus terdaftar di sistem.
                        </small>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-modern-primary w-100 py-2"
                        style="font-size: 1rem; font-weight: 600;">
                        <i class="bi bi-search me-2"></i>Verifikasi Username
                    </button>
                </form>
            </div>

            <!-- ================================================ -->
            <!-- STEP 2: Form Ganti Password -->
            <!-- ================================================ -->
            <?php elseif ($step === 2): ?>
            <div class="form-step">
                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-1">Buat Password Baru</h4>
                    <p class="text-muted" style="font-size: 0.9rem;">
                        Akun: <strong class="text-primary"><?= htmlspecialchars($_SESSION['reset_username']) ?></strong>
                        &mdash; Masukkan password baru Anda.
                    </p>
                </div>

                <form action="lupa_password.php" method="POST" id="formResetPassword">
                    <input type="hidden" name="action" value="reset_password">

                    <div class="mb-3">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control border-start-0 border-end-0 bg-light"
                                placeholder="Minimal 6 karakter"
                                oninput="checkStrength(this.value)"
                                required>
                            <button
                                class="btn btn-light border border-start-0 bg-light"
                                type="button"
                                onclick="togglePassword('new_password', 'eyeNew')"
                                style="border-color: #cbd5e1;">
                                <i class="bi bi-eye text-muted" id="eyeNew"></i>
                            </button>
                        </div>
                        <!-- Password strength bar -->
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-label text-muted" id="strengthLabel"></span>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-lock-fill text-muted"></i>
                            </span>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control border-start-0 border-end-0 bg-light"
                                placeholder="Ulangi password baru"
                                oninput="checkMatch()"
                                required>
                            <button
                                class="btn btn-light border border-start-0 bg-light"
                                type="button"
                                onclick="togglePassword('confirm_password', 'eyeConfirm')"
                                style="border-color: #cbd5e1;">
                                <i class="bi bi-eye text-muted" id="eyeConfirm"></i>
                            </button>
                        </div>
                        <span class="strength-label" id="matchLabel"></span>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-modern-primary w-100 py-2 mb-3"
                        style="font-size: 1rem; font-weight: 600;">
                        <i class="bi bi-key me-2"></i>Simpan Password Baru
                    </button>
                </form>

                <!-- Tombol ganti username — di luar form utama agar tidak nested -->
                <div class="text-center mt-1">
                    <a href="proses_lupa_password.php?clear_session=1" class="text-decoration-none text-muted" style="font-size: 0.83rem;">
                        <i class="bi bi-arrow-left-circle me-1"></i>Ganti username
                    </a>
                </div>
            </div>

            <!-- ================================================ -->
            <!-- STEP 3: Berhasil -->
            <!-- ================================================ -->
            <?php elseif ($step === 3): ?>
            <div class="form-step text-center">
                <div class="success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Password Berhasil Diubah!</h4>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">
                    Password akun Anda telah berhasil diperbarui. Silakan login menggunakan password baru Anda.
                </p>
                <a href="login.php" class="btn btn-modern-primary w-100 py-2" style="font-size: 1rem; font-weight: 600;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Toggle show/hide password
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type  = 'text';
        icon.className = 'bi bi-eye-slash text-muted';
    } else {
        field.type  = 'password';
        icon.className = 'bi bi-eye text-muted';
    }
}

// Password strength checker
function checkStrength(value) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    if (!fill) return;

    if (value.length === 0) {
        fill.className  = 'password-strength-fill';
        label.textContent = '';
        label.className = 'strength-label text-muted';
        return;
    }

    const hasUpper   = /[A-Z]/.test(value);
    const hasLower   = /[a-z]/.test(value);
    const hasNumber  = /[0-9]/.test(value);
    const hasSpecial = /[^A-Za-z0-9]/.test(value);
    const score      = [value.length >= 6, hasUpper, hasLower, hasNumber, hasSpecial].filter(Boolean).length;

    if (score <= 2) {
        fill.className  = 'password-strength-fill strength-weak';
        label.textContent = 'Kekuatan: Lemah';
        label.className = 'strength-label text-danger';
    } else if (score <= 3) {
        fill.className  = 'password-strength-fill strength-medium';
        label.textContent = 'Kekuatan: Sedang';
        label.className = 'strength-label text-warning';
    } else {
        fill.className  = 'password-strength-fill strength-strong';
        label.textContent = 'Kekuatan: Kuat';
        label.className = 'strength-label text-success';
    }

    // Re-check match
    checkMatch();
}

// Confirm password match checker
function checkMatch() {
    const pwd     = document.getElementById('new_password');
    const confirm = document.getElementById('confirm_password');
    const label   = document.getElementById('matchLabel');
    if (!pwd || !confirm || !label) return;

    if (confirm.value.length === 0) {
        label.textContent = '';
        return;
    }

    if (pwd.value === confirm.value) {
        label.textContent = '✓ Password cocok';
        label.className   = 'strength-label text-success';
    } else {
        label.textContent = '✗ Password tidak cocok';
        label.className   = 'strength-label text-danger';
    }
}
</script>

</body>
</html>