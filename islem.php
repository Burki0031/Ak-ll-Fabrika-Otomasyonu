<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';

// Giriş kontrolü
if(!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['makine_id'] ?? 0;
    $islem = $_POST['islem'] ?? '';
    $personel_id = $_SESSION['kullanici_id'] ?? 0; // Session'dan kullanıcı ID'sini al
    
    // Güvenlik kontrolü
    if(empty($id) || empty($islem) || empty($personel_id)) {
        header("Location: index.php?error=Geçersiz işlem");
        exit();
    }

    if ($islem == "baslat") {
        // 1. Yeni Üretim Kaydı Ekle (Web Siparişi olarak)
        $sql1 = "INSERT INTO Uretimler (MakineID, PersonelID, UrunTipi, HedefMiktar, UretilenMiktar, BaslangicZamani, Durum) 
                 VALUES (?, ?, 'Web Siparis', 500, 0, GETDATE(), 'Devam Ediyor')";
        
        // 2. Makine Durumunu 'Calisiyor' Yap
        $sql2 = "UPDATE Makineler SET Durum = 'Calisiyor' WHERE MakineID = ?";

        $params1 = array($id, $personel_id);
        $params2 = array($id);
        sqlsrv_query($conn, $sql1, $params1);
        sqlsrv_query($conn, $sql2, $params2);
    }
    
    elseif ($islem == "ariza") {
        // 1. Arıza Kaydı Ekle
        $sql1 = "INSERT INTO Arizalar (MakineID, ArizaKodu, ArizaTanimi, Aciklama, Durum, BaslangicZamani, OlusturmaTarihi) 
                 VALUES (?, 'ERR-WEB', 'Operatör Acil Durum', 'Operatör Acil Durum', 'Açık', GETDATE(), GETDATE())";
        
        // 2. Makine Durumunu 'Arizali' Yap
        $sql2 = "UPDATE Makineler SET Durum = 'Arizali' WHERE MakineID = ?";

        $params1 = array($id);
        $params2 = array($id);
        sqlsrv_query($conn, $sql1, $params1);
        sqlsrv_query($conn, $sql2, $params2);
    }

    elseif ($islem == "sifirla") {
        // Makineyi 'Calisiyor' durumuna döndür
        $sql = "UPDATE Makineler SET Durum = 'Calisiyor' WHERE MakineID = ?";
        $params = array($id);
        sqlsrv_query($conn, $sql, $params);
    }

    // İşlem bitince ana sayfaya geri dön
    header("Location: index.php");
    exit();
}
?>