<?php
$conn = mysqli_connect('localhost','root','','db_pelindo');
if(!$conn){ die('Koneksi gagal: '.mysqli_connect_error()); }
echo 'Koneksi: OK'.PHP_EOL;

// Cek data admin
$r = mysqli_query($conn,'SELECT id_admin, nama_admin, username, password FROM admin');
if(!$r){ echo 'Query error: '.mysqli_error($conn).PHP_EOL; exit; }
echo PHP_EOL.'Data admin:'.PHP_EOL;
while($row = mysqli_fetch_assoc($r)){
    echo '  id='.$row['id_admin'].' | user='.$row['username'].' | pass='.$row['password'].PHP_EOL;
}

// Cek kolom tabel maintenance
$cols = mysqli_query($conn,'SHOW COLUMNS FROM maintenance');
echo PHP_EOL.'Kolom tabel maintenance:'.PHP_EOL;
while($c = mysqli_fetch_assoc($cols)){ echo '  '.$c['Field'].' ('.$c['Type'].')'.PHP_EOL; }
