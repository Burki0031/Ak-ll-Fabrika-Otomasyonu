<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';

// Eğer zaten giriş yapmışsa ana sayfaya yönlendir
if(isset($_SESSION['kullanici_id'])) {
    header("Location: dashboard.php");
    exit();
}

$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = $_POST['kullanici_adi'] ?? '';
    $sifre = $_POST['sifre'] ?? '';
    
    if(empty($kullanici_adi) || empty($sifre)) {
        $hata = "Kullanıcı adı ve şifre boş bırakılamaz!";
    } else {
        // Önce tablonun var olup olmadığını kontrol et
        $checkTable = "SELECT COUNT(*) as tablo_var FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Kullanicilar'";
        $checkStmt = sqlsrv_query($conn, $checkTable);
        $tableExists = false;
        
        if($checkStmt !== false) {
            $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            $tableExists = ($checkRow['tablo_var'] > 0);
        }
        
        // Tablo yoksa oluştur
        if(!$tableExists) {
            $createTable = "IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Kullanicilar]') AND type in (N'U'))
            BEGIN
                CREATE TABLE Kullanicilar (
                    KullaniciID INT PRIMARY KEY IDENTITY(1,1),
                    AdSoyad NVARCHAR(100) NOT NULL,
                    KullaniciAdi NVARCHAR(50) UNIQUE NOT NULL,
                    Sifre NVARCHAR(100) NOT NULL,
                    Rol NVARCHAR(20) NOT NULL DEFAULT 'Personel',
                    KayitTarihi DATETIME DEFAULT GETDATE(),
                    Durum NVARCHAR(10) NOT NULL DEFAULT 'Aktif',
                    ProfilResmi NVARCHAR(255) NULL
                );
            END";
            
            sqlsrv_query($conn, $createTable);
            
            // Varsayılan kullanıcıları ekle
            $insertAdmin = "IF NOT EXISTS (SELECT * FROM Kullanicilar WHERE KullaniciAdi = 'admin')
            BEGIN
                INSERT INTO Kullanicilar (AdSoyad, KullaniciAdi, Sifre, Rol, Durum) 
                VALUES ('Admin Kullanıcı', 'admin', 'admin123', 'Yönetici', 'Aktif');
            END";
            
            $insertOperator = "IF NOT EXISTS (SELECT * FROM Kullanicilar WHERE KullaniciAdi = 'operator')
            BEGIN
                INSERT INTO Kullanicilar (AdSoyad, KullaniciAdi, Sifre, Rol, Durum) 
                VALUES ('Operatör 1', 'operator', 'operator123', 'Personel', 'Aktif');
            END";
            
            sqlsrv_query($conn, $insertAdmin);
            sqlsrv_query($conn, $insertOperator);
        }
        
        // Mevcut kullanıcıların Durum alanını güncelle (NULL veya boş ise 'Aktif' yap)
        // Sadece tablo varsa ve Durum NULL/boş ise güncelle
        $updateDurum = "UPDATE Kullanicilar SET Durum = 'Aktif' WHERE Durum IS NULL OR LTRIM(RTRIM(Durum)) = ''";
        sqlsrv_query($conn, $updateDurum);
        
        // Veritabanından kullanıcıyı kontrol et
        $sql = "SELECT KullaniciID, KullaniciAdi, Sifre, AdSoyad, Rol, ISNULL(Durum, 'Aktif') as Durum FROM Kullanicilar WHERE KullaniciAdi = ?";
        $params = array($kullanici_adi);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if($stmt !== false) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            if($row) {
                // Durum kontrolü - sadece tam olarak 'Pasif' ise engelle
                // NULL, boş string, 'Aktif' veya başka değerler geçerli
                $durum = isset($row['Durum']) ? trim($row['Durum']) : 'Aktif';
                $durumLower = strtolower($durum);
                
                // Sadece 'pasif' ise engelle, diğer tüm durumlarda (aktif, null, boş) geç
                if($durumLower === 'pasif') {
                    $hata = "Hesabınız pasif durumda! Lütfen yönetici ile iletişime geçin.";
                }
                // Şifre kontrolü - veritabanından gelen şifre ile karşılaştır
                elseif(isset($row['Sifre']) && $row['Sifre'] === $sifre) {
                    // Giriş başarılı
                    $_SESSION['kullanici_id'] = $row['KullaniciID'];
                    $_SESSION['kullanici_adi'] = $row['KullaniciAdi'];
                    $_SESSION['isim_soyisim'] = $row['AdSoyad'];
                    $_SESSION['rol'] = $row['Rol'];
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $hata = "Kullanıcı adı veya şifre hatalı!";
                }
            } else {
                $hata = "Kullanıcı adı veya şifre hatalı!";
            }
        } else {
            $errors = sqlsrv_errors();
            $hata = "Veritabanı hatası oluştu!";
            if($errors) {
                error_log("SQL Error: " . print_r($errors, true));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Akıllı Fabrika</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .login-body {
            padding: 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }


        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .info-box {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            border-left: 4px solid #17a2b8;
        }

        .info-box h6 {
            color: #0c5460;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: #0c5460;
            font-size: 0.9rem;
            margin: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon-wrapper">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>Giriş Yap</h1>
            <p>Akıllı Fabrika Yönetim Sistemi</p>
        </div>
        
        <div class="login-body">
            <?php if($hata): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($hata); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label class="form-label" for="kullanici_adi">
                        <i class="bi bi-person-fill"></i> Kullanıcı Adı
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="kullanici_adi" 
                           name="kullanici_adi" 
                           placeholder="Kullanıcı adınızı giriniz"
                           required 
                           autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sifre">
                        <i class="bi bi-lock-fill"></i> Şifre
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="sifre" 
                           name="sifre" 
                           placeholder="Şifrenizi giriniz"
                           required>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Giriş Yap
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
