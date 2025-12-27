<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';
include 'includes/auth.php';
checkLogin();

$pageTitle = "Makine Kontrol";

// Makine işlemleri
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['islem'])) {
    $makine_id = $_POST['makine_id'] ?? 0;
    $islem = $_POST['islem'];
    $kullanici_id = $_SESSION['kullanici_id'];

    if($makine_id > 0) {
        if($islem == "baslat") {
            // Üretim kaydı oluştur
            $sql1 = "INSERT INTO Uretimler (MakineID, PersonelID, UrunTipi, HedefMiktar, UretilenMiktar, BaslangicZamani, Durum) 
                     VALUES (?, ?, 'Web Siparis', 500, 0, GETDATE(), 'Devam Ediyor')";
            $params1 = array($makine_id, $kullanici_id);
            sqlsrv_query($conn, $sql1, $params1);
            
            // Makine durumunu güncelle
            $sql2 = "UPDATE Makineler SET Durum = 'Calisiyor' WHERE MakineID = ?";
            $params2 = array($makine_id);
            sqlsrv_query($conn, $sql2, $params2);
            
            header("Location: makine-kontrol.php?success=Makine başarıyla başlatıldı");
            exit();
        }
        elseif($islem == "durdur") {
            // Aktif üretimleri durdur (Hem 'Devam Ediyor' hem 'DevamEdiyor' olanları yakala)
            $sql1 = "UPDATE Uretimler SET Durum = 'Durduruldu', BitisZamani = GETDATE() 
                     WHERE MakineID = ? AND Durum IN ('Devam Ediyor', 'DevamEdiyor')";
            $params1 = array($makine_id);
            sqlsrv_query($conn, $sql1, $params1);
            
            // Makine durumunu güncelle
            $sql2 = "UPDATE Makineler SET Durum = 'Hazir' WHERE MakineID = ?";
            $params2 = array($makine_id);
            sqlsrv_query($conn, $sql2, $params2);
            
            header("Location: makine-kontrol.php?success=Makine başarıyla durduruldu");
            exit();
        }
        elseif($islem == "bakim") {
            // Makineyi bakım moduna al
            $sql = "UPDATE Makineler SET Durum = 'Bakimda', SonBakimTarihi = GETDATE() WHERE MakineID = ?";
            $params = array($makine_id);
            sqlsrv_query($conn, $sql, $params);
            
            header("Location: makine-kontrol.php?success=Makine bakım moduna alındı");
            exit();
        }
        elseif($islem == "hazir") {
            // Makineyi hazır duruma getir
            $sql = "UPDATE Makineler SET Durum = 'Hazir' WHERE MakineID = ?";
            $params = array($makine_id);
            sqlsrv_query($conn, $sql, $params);
            
            header("Location: makine-kontrol.php?success=Makine hazır duruma getirildi");
            exit();
        }
    }
}

// --------------------------------------------------------------------------
// TÜM MAKİNELERİ VE DETAYLI VERİLERİ ÇEK
// --------------------------------------------------------------------------
$sql = "SELECT m.*, 
        
        -- 1. AKTİF ÜRETİLEN ADET (Toplam Miktar)
        (SELECT ISNULL(SUM(u.UretilenMiktar), 0) 
         FROM Uretimler u 
         WHERE u.MakineID = m.MakineID 
         AND u.Durum IN ('Devam Ediyor', 'DevamEdiyor')) as AktifUretimAdedi,
         
        -- 2. OPERATÖR ADI
        (SELECT TOP 1 k.AdSoyad 
         FROM Uretimler u 
         INNER JOIN Kullanicilar k ON u.PersonelID = k.KullaniciID 
         WHERE u.MakineID = m.MakineID 
         AND u.Durum IN ('Devam Ediyor', 'DevamEdiyor') 
         ORDER BY u.BaslangicZamani DESC) as OperatorAdi,

        -- 3. TOPLAM ÇALIŞMA SÜRESİ (Dakika cinsinden hesapla)
        -- (Bitiş zamanı varsa onu, yoksa şu anı(GETDATE) baz alır)
        (SELECT ISNULL(SUM(DATEDIFF(MINUTE, u.BaslangicZamani, ISNULL(u.BitisZamani, GETDATE()))), 0)
         FROM Uretimler u
         WHERE u.MakineID = m.MakineID) as ToplamCalismaDakika

        FROM Makineler m
        ORDER BY m.MakineAdi";

$makineler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $makineler[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-cpu"></i> Makine Kontrol Paneli</h1>
            <p class="text-muted">Makineleri başlatın, durdurun veya bakım moduna alın</p>
        </div>
    </div>

    <div class="row">
        <?php foreach($makineler as $makine): 
            $durumRenk = 'secondary';
            $durumIcon = 'question-circle';
            if($makine['Durum'] == 'Calisiyor') {
                $durumRenk = 'success';
                $durumIcon = 'check-circle-fill';
            } elseif($makine['Durum'] == 'Arizali') {
                $durumRenk = 'danger';
                $durumIcon = 'exclamation-triangle-fill';
            } elseif($makine['Durum'] == 'Bakimda') {
                $durumRenk = 'warning';
                $durumIcon = 'tools';
            } elseif($makine['Durum'] == 'Hazir') {
                $durumRenk = 'info';
                $durumIcon = 'play-circle';
            }
            
            // Dakikayı Saate Çevir
            $toplamSaat = $makine['ToplamCalismaDakika'] / 60;
        ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-<?php echo $durumRenk; ?> text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="bi bi-<?php echo $durumIcon; ?>"></i> <?php echo htmlspecialchars($makine['MakineAdi']); ?></h5>
                            <small>Durum: <strong><?php echo htmlspecialchars($makine['Durum']); ?></strong></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Marka/Model:</small>
                        <strong><?php echo htmlspecialchars($makine['Marka'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($makine['Model'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Toplam Çalışma Saati:</small>
                        <strong><?php echo number_format($toplamSaat, 1); ?> saat</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Aktif Üretilen Parça:</small>
                        <?php if($makine['AktifUretimAdedi'] > 0 || $makine['Durum'] == 'Calisiyor'): ?>
                            <span class="badge bg-success fs-6"><?php echo number_format($makine['AktifUretimAdedi']); ?> adet</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">0 adet</span>
                        <?php endif; ?>
                    </div>
                    <?php if($makine['OperatorAdi']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Operatör:</small>
                        <strong><?php echo htmlspecialchars($makine['OperatorAdi']); ?></strong>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <?php if($makine['Durum'] == 'Hazir' || $makine['Durum'] == 'Bakimda'): ?>
                        <form method="POST" action="makine-kontrol.php">
                            <input type="hidden" name="makine_id" value="<?php echo $makine['MakineID']; ?>">
                            <input type="hidden" name="islem" value="baslat">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-play-fill"></i> Başlat
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if($makine['Durum'] == 'Calisiyor'): ?>
                        <form method="POST" action="makine-kontrol.php">
                            <input type="hidden" name="makine_id" value="<?php echo $makine['MakineID']; ?>">
                            <input type="hidden" name="islem" value="durdur">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-pause-fill"></i> Durdur
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if($makine['Durum'] != 'Bakimda' && $makine['Durum'] != 'Arizali'): ?>
                        <form method="POST" action="makine-kontrol.php">
                            <input type="hidden" name="makine_id" value="<?php echo $makine['MakineID']; ?>">
                            <input type="hidden" name="islem" value="bakim">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-tools"></i> Bakım Moduna Al
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <?php if($makine['Durum'] == 'Bakimda'): ?>
                        <form method="POST" action="makine-kontrol.php">
                            <input type="hidden" name="makine_id" value="<?php echo $makine['MakineID']; ?>">
                            <input type="hidden" name="islem" value="hazir">
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-check-circle"></i> Hazır Duruma Getir
                            </button>
                        </form>
                        <?php endif; ?>
                        
                        <a href="ariza-yonetimi.php?makine_id=<?php echo $makine['MakineID']; ?>" class="btn btn-danger w-100">
                            <i class="bi bi-exclamation-triangle"></i> Arıza Bildir
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if(empty($makineler)): ?>
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i> Henüz makine kaydı bulunmuyor.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>