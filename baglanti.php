<?php
// baglanti.php

$serverName = "YIGIT\SQLEXPRESS01"; 

$connectionInfo = array(
    "Database" => "AkilliFabrikaDB",
    "CharacterSet" => "UTF-8",
    "UID" => "webuser",   // SQL'de oluşturduğumuz Kullanıcı Adı
    "PWD" => "12345"      // SQL'de oluşturduğumuz Şifre
);

// Bağlantıyı Kur
$conn = sqlsrv_connect($serverName, $connectionInfo);

if( $conn === false ) {
    echo "Veritabanı Bağlantı Hatası!<br />";
    die( print_r( sqlsrv_errors(), true));
}
?>