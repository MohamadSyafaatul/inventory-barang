<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../assets/login.php");
    exit;
}

include "../config/koneksi.php";

$id = intval($_GET['id']);
if ($id > 0) {
    mysqli_query($conn, "DELETE FROM maintenance WHERE id_maintenance = $id");
}
header("Location: index.php");
exit;
