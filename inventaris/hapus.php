<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$ip = mysqli_real_escape_string($conn, $_GET['id']);
mysqli_query($conn, "DELETE FROM inventaris WHERE ip_address='$ip'");
header("Location: index.php");
exit;

