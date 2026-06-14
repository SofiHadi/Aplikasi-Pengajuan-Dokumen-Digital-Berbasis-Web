<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username; 
        
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: index.php?pesan=Username dan Password tidak boleh kosong!");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>