<?php

session_start();

include "../config/koneksi.php";

$username = mysqli_real_escape_string($conn,$_POST['username']);

$password = mysqli_real_escape_string($conn,$_POST['password']);

$query = mysqli_query($conn,"SELECT * FROM admin
WHERE username='$username'
AND password='$password'");

if(mysqli_num_rows($query)>0){

    $_SESSION['login']=true;

    header("Location: dashboard.php");

}else{

    echo "<script>

    alert('Username atau Password Salah');

    window.location='login.php';

    </script>";

}

?>