<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Session cookie'sini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Session'ı yok et
session_destroy();

// Giriş sayfasına yönlendir
header("Location: login.php");
exit();
?>
