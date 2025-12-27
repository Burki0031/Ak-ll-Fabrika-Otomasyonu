<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';
include 'includes/auth.php';
checkLogin();

$pageTitle = "Arıza Yönetimi";

// --------------------------------------------------------------------------
// 0. ARIZA KODLARI LİSTESİ (Formda Seçtirmek İçin)
// --------------------------------------------------------------------------
$arizaKodlari = [
    'ERR-001' => ['baslik' => 'Motor Aşırı Isınma'],
    'ERR-002' => ['baslik' => 'Sensör Hatası'],
    'ERR-003' => ['baslik' => 'Baskı Kafası Tıkanıklığı'],
    'ERR-004' => ['baslik' => 'Malzeme/Hammadde Eksik'],
    'ERR-005' => ['baslik' => 'Güç Kaynağı Hatası'],
    'ERR-006' => ['baslik' => 'Acil Stop Basılı'],
    'ERR-DIGER' => ['baslik' => 'Diğer / Tanımsız Arıza']
];

// --------------------------------------------------------------------------
// 1. ARIZA BİLDİRİM İŞLEMİ
// --------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ariza_bildir'])) {
    $makine_id = $_POST['makine_id'] ?? 0;
    
    // Formdan gelen verileri alıyoruz
    $ariza_kodu = $_POST['ariza_kodu'] ?? 'ERR-DIGER'; 
    $aciklama = $_POST['aciklama'] ?? ''; 

    // Arıza Tanımını diziden bul, yoksa kodun kendisini yaz
    $arizaTanimi = $arizaKodlari[$ariza_kodu]['baslik'] ?? 'Genel Arıza';
    
    // 1. Arizalar Tablosuna Ekle
    $sql1 = "INSERT INTO Arizalar (MakineID, ArizaKodu, ArizaTanimi, Aciklama, Durum, BaslangicZamani, OlusturmaTarihi) 
             VALUES (?, ?, ?, ?, 'Açık', GETDATE(), GETDATE())";
    $params1 = array($makine_id, $ariza_kodu, $arizaTanimi, $aciklama);
    sqlsrv_query($conn, $sql1, $params1);
    
    // 2. Makineler Tablosunu Güncelle (Durum -> 'Arizali')
    $sql2 = "UPDATE Makineler SET Durum = 'Arizali' WHERE MakineID = ?";
    $params2 = array($makine_id);
    sqlsrv_query($conn, $sql2, $params2);

    // 3. Bildirim Gönder
    $makineAdiSql = "SELECT MakineAdi FROM Makineler WHERE MakineID = ?";
    $stmtM = sqlsrv_query($conn, $makineAdiSql, array($makine_id));
    $makineAdi = "Makine";
    if($stmtM && $rowM = sqlsrv_fetch_array($stmtM, SQLSRV_FETCH_ASSOC)){
        $makineAdi = $rowM['MakineAdi'];
    }

    $bildirimBaslik = "Yeni Arıza: $ariza_kodu";
    $bildirimMesaj = "$makineAdi için arıza kaydı açıldı: $aciklama";
    
    $sql3 = "INSERT INTO Tbl_Bildirimler (Baslik, Mesaj, Tarih, Durum) VALUES (?, ?, GETDATE(), 'Okunmadi')";
    $params3 = array($bildirimBaslik, $bildirimMesaj);
    sqlsrv_query($conn, $sql3, $params3);
    
    header("Location: ariza-yonetimi.php?success=Arıza bildirildi ve sisteme işlendi.");
    exit();
}

// --------------------------------------------------------------------------
// 2. ARIZA ÇÖZME İŞLEMİ
// --------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ariza_coz'])) {
    $ariza_id = $_POST['ariza_id'] ?? 0;
    $makine_id = $_POST['makine_id'] ?? 0;
    
    // 1. Arızayı Kapat
    $sql1 = "UPDATE Arizalar SET Durum = 'Giderildi', BitisZamani = GETDATE() WHERE ArizaID = ?";
    $params1 = array($ariza_id);
    sqlsrv_query($conn, $sql1, $params1);
    
    // 2. Makineyi Aç
    $sql2 = "UPDATE Makineler SET Durum = 'Calisiyor' WHERE MakineID = ?";
    $params2 = array($makine_id);
    sqlsrv_query($conn, $sql2, $params2);
    
    // 3. Bildirim (Opsiyonel)
    $sql3 = "INSERT INTO Tbl_Bildirimler (Baslik, Mesaj, Tarih, Durum) VALUES (?, ?, GETDATE(), 'Okunmadi')";
    sqlsrv_query($conn, $sql3, array("Arıza Giderildi", "Makine ID: $makine_id tekrar devreye alındı."));

    header("Location: ariza-yonetimi.php?success=Arıza çözüldü olarak işaretlendi.");
    exit();
}

// --------------------------------------------------------------------------
// 3. AKTİF ARIZALARI ÇEK
// --------------------------------------------------------------------------
$sql = "SELECT a.ArizaID, a.MakineID, a.ArizaKodu, a.ArizaTanimi, a.BaslangicZamani, a.Aciklama,
        m.MakineAdi, m.Model, m.UretimHatti,
        DATEDIFF(HOUR, a.BaslangicZamani, GETDATE()) as GecenSure
        FROM Arizalar a
        INNER JOIN Makineler m ON a.MakineID = m.MakineID
        WHERE a.Durum NOT IN ('Giderildi', 'Tamamlandi', 'İptal')
        ORDER BY a.BaslangicZamani DESC";

$aktifArizalar = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $aktifArizalar[] = $row;
    }
}

// --------------------------------------------------------------------------
// 4. ARIZA GEÇMİŞİ
// --------------------------------------------------------------------------
$sql = "SELECT a.ArizaID, a.ArizaKodu, a.ArizaTanimi, a.BaslangicZamani, a.BitisZamani,
        m.MakineAdi, m.UretimHatti,
        DATEDIFF(HOUR, a.BaslangicZamani, ISNULL(a.BitisZamani, GETDATE())) as CozulmeSuresi
        FROM Arizalar a
        INNER JOIN Makineler m ON a.MakineID = m.MakineID
        WHERE a.Durum IN ('Giderildi', 'Tamamlandi')
        ORDER BY a.BitisZamani DESC";

$arizaGecmisi = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $arizaGecmisi[] = $row;
    }
}

// --------------------------------------------------------------------------
// 5. MAKİNE LİSTESİ
// --------------------------------------------------------------------------
$sql = "SELECT MakineID, MakineAdi, UretimHatti FROM Makineler ORDER BY MakineAdi";
$makineler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $makineler[] = $row;
    }
}

// --------------------------------------------------------------------------
// 6. İSTATİSTİK
// --------------------------------------------------------------------------
$sql = "SELECT TOP 5 m.MakineID, m.MakineAdi, COUNT(a.ArizaID) as ArizaSayisi
        FROM Makineler m
        LEFT JOIN Arizalar a ON m.MakineID = a.MakineID
        GROUP BY m.MakineID, m.MakineAdi
        ORDER BY ArizaSayisi DESC";
$enCokAriza = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $enCokAriza[] = $row;
    }
}

$selectedMakine = $_GET['makine_id'] ?? 0;

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-exclamation-triangle"></i> Arıza Yönetimi</h1>
            <p class="text-muted">Arıza bildirimi, analiz ve çözüm takibi</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-lightning-fill"></i> Arıza Bildirimi Oluştur
                </div>
                <div class="card-body">
                    <form method="POST" action="ariza-yonetimi.php">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Arızalı Makine</label>
                                <select name="makine_id" class="form-select" required>
                                    <option value="">-- Makine Seçiniz --</option>
                                    <?php foreach($makineler as $makine): ?>
                                    <option value="<?php echo $makine['MakineID']; ?>" <?php echo ($selectedMakine == $makine['MakineID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($makine['MakineAdi']); ?> 
                                        (Hat: <?php echo htmlspecialchars($makine['UretimHatti'] ?? '-'); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Arıza Tipi</label>
                                <select name="ariza_kodu" class="form-select" required>
                                    <?php foreach($arizaKodlari as $kod => $detay): ?>
                                        <option value="<?php echo $kod; ?>"><?php echo $detay['baslik']; ?> (<?php echo $kod; ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Açıklama (Opsiyonel)</label>
                                <input type="text" name="aciklama" class="form-control" placeholder="Örn: Ses geliyor, parça sıkıştı...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 text-end">
                                <button type="submit" name="ariza_bildir" class="btn btn-danger px-5">
                                    <i class="bi bi-exclamation-triangle-fill"></i> BİLDİRİM GÖNDER
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-exclamation-circle-fill"></i> Aktif Arızalar (<?php echo count($aktifArizalar); ?>)</span>
                    <?php if(count($aktifArizalar) > 0): ?>
                        <span class="badge bg-white text-danger">Müdahale Bekliyor</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if(empty($aktifArizalar)): ?>
                        <div class="alert alert-success text-center py-4">
                            <h1><i class="bi bi-check-circle"></i></h1>
                            <p class="mb-0">Harika! Şu an aktif bir arıza bulunmuyor.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($aktifArizalar as $ariza): ?>
                        <div class="card mb-3 border-danger shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1 text-danger">
                                            <strong><?php echo htmlspecialchars($ariza['MakineAdi'] ?? ''); ?></strong>
                                            <span class="badge bg-secondary ms-2 text-white" style="font-size: 0.7em;">
                                                <?php echo htmlspecialchars($ariza['Model'] ?? ''); ?>
                                            </span>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <i class="bi bi-geo-alt"></i> Hat: <?php echo htmlspecialchars($ariza['UretimHatti'] ?? 'Belirtilmemiş'); ?>
                                            <br>
                                            <i class="bi bi-clock-history"></i> <?php echo $ariza['GecenSure']; ?> saattir arızalı
                                            <br>
                                            <small class="text-danger fw-bold">
                                                <?php echo htmlspecialchars($ariza['ArizaTanimi'] ?? 'Tanımsız Arıza'); ?>
                                            </small>
                                        </p>
                                        <?php if(!empty($ariza['Aciklama'])): ?>
                                            <div class="alert alert-light p-2 mb-0 border">
                                                <small><strong>Not:</strong> <?php echo htmlspecialchars($ariza['Aciklama'] ?? ''); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form method="POST" action="ariza-yonetimi.php">
                                        <input type="hidden" name="ariza_id" value="<?php echo $ariza['ArizaID']; ?>">
                                        <input type="hidden" name="makine_id" value="<?php echo $ariza['MakineID']; ?>">
                                        <button type="submit" name="ariza_coz" class="btn btn-success" onclick="return confirm('Bu arızanın giderildiğini ve makinenin çalıştığını onaylıyor musunuz?');">
                                            <i class="bi bi-check-lg"></i> Çözüldü Olarak İşaretle
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-bar-chart"></i> En Çok Arıza Yapanlar
                </div>
                <div class="card-body">
                    <?php if(empty($enCokAriza)): ?>
                        <p class="text-muted">Veri bulunamadı.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($enCokAriza as $makine): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($makine['MakineAdi'] ?? ''); ?>
                                <span class="badge bg-danger rounded-pill"><?php echo $makine['ArizaSayisi']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Son Çözülen Arızalar
                </div>
                <div class="card-body">
                    <?php if(empty($arizaGecmisi)): ?>
                        <p class="text-muted text-center py-4">Arıza geçmişi bulunmuyor.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Makine / Hat</th>
                                        <th>Arıza Tanımı</th>
                                        <th>Çözülme Süresi</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($arizaGecmisi as $ariza): ?>
                                    <tr>
                                        <td><?php 
                                        if($ariza['BaslangicZamani'] instanceof DateTime) {
                                            echo $ariza['BaslangicZamani']->format('d.m.Y H:i');
                                        } else {
                                            echo date('d.m.Y H:i', strtotime($ariza['BaslangicZamani']));
                                        }
                                        ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($ariza['MakineAdi'] ?? ''); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($ariza['UretimHatti'] ?? '-'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($ariza['ArizaTanimi'] ?? ''); ?></td>
                                        <td><?php echo $ariza['CozulmeSuresi']; ?> saat</td>
                                        <td><span class="badge bg-success">Giderildi</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>