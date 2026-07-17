<?php
session_start();

// Clear session reset (dipakai saat user ingin kembali ke step 1 verifikasi username)
if (
    (isset($_GET['clear_session'])) ||
    (isset($_POST['action']) && $_POST['action'] === 'clear_session')
) {
    unset($_SESSION['reset_username']);
    header("Location: lupa_password.php");
    exit;
}

// Redirect default jika tidak ada aksi valid
header("Location: lupa_password.php");
exit;
?>