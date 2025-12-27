<?php
// Session zaten başlatılmadıysa başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısı
include_once 'baglanti.php';
include_once 'includes/auth.php'; 

// Giriş kontrolü
checkLogin();

$pageTitle = "Ana Sayfa";

// İstatistikleri çek
$stats = [];

// 1. Toplam makine sayısı
$sql = "SELECT COUNT(*) as toplam FROM Makineler";
$stmt = sqlsrv_query($conn, $sql);
$stats['toplam_makine'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['toplam'] : 0;

// 2. Çalışan makine sayısı
$sql = "SELECT COUNT(*) as calisiyor FROM Makineler WHERE Durum = 'Calisiyor'";
$stmt = sqlsrv_query($conn, $sql);
$stats['calisiyor'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['calisiyor'] : 0;

// 3. Arızalı makine sayısı
$sql = "SELECT COUNT(*) as arizali FROM Makineler WHERE Durum = 'Arizali'";
$stmt = sqlsrv_query($conn, $sql);
$stats['arizali'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['arizali'] : 0;

// 4. Bakımda makine sayısı
$sql = "SELECT COUNT(*) as bakimda FROM Makineler WHERE Durum = 'Bakimda'";
$stmt = sqlsrv_query($conn, $sql);
$stats['bakimda'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['bakimda'] : 0;

// 5. Aktif üretim sayısı (DÜZELTİLDİ: Hem 'Devam Ediyor' hem 'DevamEdiyor' sayılır)
$sql = "SELECT COUNT(*) as aktif_uretim FROM Uretimler WHERE Durum IN ('Devam Ediyor', 'DevamEdiyor')";
$stmt = sqlsrv_query($conn, $sql);
$stats['aktif_uretim'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['aktif_uretim'] : 0;

// 6. Bugünkü üretim miktarı
$sql = "SELECT ISNULL(SUM(UretilenMiktar), 0) as bugun_uretim FROM Uretimler WHERE CAST(BaslangicZamani AS DATE) = CAST(GETDATE() AS DATE)";
$stmt = sqlsrv_query($conn, $sql);
$stats['bugun_uretim'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['bugun_uretim'] : 0;

// 7. Aktif arıza sayısı
$sql = "SELECT COUNT(*) as aktif_ariza FROM Arizalar WHERE Durum = 'Açık'";
$stmt = sqlsrv_query($conn, $sql);
$stats['aktif_ariza'] = ($stmt !== false) ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['aktif_ariza'] : 0;

// 8. Aktif makineleri çek (DÜZELTİLDİ: Alt sorguda IN kullanıldı)
$sql = "SELECT TOP 5 m.MakineID, m.MakineAdi, m.Durum, m.CalismaSaati, 
        (SELECT COUNT(*) FROM Uretimler u WHERE u.MakineID = m.MakineID AND u.Durum IN ('Devam Ediyor', 'DevamEdiyor')) as AktifUretim
        FROM Makineler m 
        WHERE m.Durum = 'Calisiyor' 
        ORDER BY m.CalismaSaati DESC";
$aktifMakineler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $aktifMakineler[] = $row;
    }
}

// Kritik uyarılar
$uyarilar = [];
if($stats['arizali'] > 0) {
    $uyarilar[] = [
        'tip' => 'danger',
        'icon' => 'exclamation-triangle-fill',
        'baslik' => 'Arızalı Makineler',
        'mesaj' => $stats['arizali'] . ' makine arızalı durumda!',
        'link' => 'ariza-yonetimi.php'
    ];
}
if($stats['bakimda'] > 3) {
    $uyarilar[] = [
        'tip' => 'warning',
        'icon' => 'tools',
        'baslik' => 'Bakımda Çok Makine',
        'mesaj' => $stats['bakimda'] . ' makine bakımda!',
        'link' => 'makine-kontrol.php'
    ];
}

// Son üretim kayıtları
$sql = "SELECT TOP 5 u.UretimID, u.UrunTipi, u.HedefMiktar, u.UretilenMiktar, u.Durum, 
        m.MakineAdi, u.BaslangicZamani
        FROM Uretimler u
        INNER JOIN Makineler m ON u.MakineID = m.MakineID
        ORDER BY u.BaslangicZamani DESC";
$sonUretimler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $sonUretimler[] = $row;
    }
}

include_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <p class="text-muted">Fabrika anlık durum özeti ve kritik bilgiler</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-icon"><i class="bi bi-cpu-fill"></i></div>
                <div class="stat-number"><?php echo $stats['toplam_makine']; ?></div>
                <div class="stat-label">Toplam Makine</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-number"><?php echo $stats['calisiyor']; ?></div>
                <div class="stat-label">Çalışan Makine</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card danger">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="stat-number"><?php echo $stats['arizali']; ?></div>
                <div class="stat-label">Arızalı Makine</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-icon"><i class="bi bi-tools"></i></div>
                <div class="stat-number"><?php echo $stats['bakimda']; ?></div>
                <div class="stat-label">Bakımda Makine</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="stat-card info">
                <div class="stat-icon"><i class="bi bi-play-circle-fill"></i></div>
                <div class="stat-number"><?php echo $stats['aktif_uretim']; ?></div>
                <div class="stat-label">Aktif Üretim</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                <div class="stat-number"><?php echo number_format($stats['bugun_uretim']); ?></div>
                <div class="stat-label">Bugünkü Üretim (Adet)</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card danger">
                <div class="stat-icon"><i class="bi bi-bug-fill"></i></div>
                <div class="stat-number"><?php echo $stats['aktif_ariza']; ?></div>
                <div class="stat-label">Aktif Arıza</div>
            </div>
        </div>
    </div>

    <?php if(!empty($uyarilar)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-bell-fill"></i> Kritik Uyarılar
                </div>
                <div class="card-body">
                    <?php foreach($uyarilar as $uyari): ?>
                    <div class="alert alert-<?php echo $uyari['tip']; ?> d-flex align-items-center mb-2">
                        <i class="bi bi-<?php echo $uyari['icon']; ?> me-3" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <strong><?php echo $uyari['baslik']; ?>:</strong> <?php echo $uyari['mesaj']; ?>
                        </div>
                        <a href="<?php echo $uyari['link']; ?>" class="btn btn-sm btn-<?php echo $uyari['tip']; ?>">
                            İncele <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-cpu"></i> Aktif Makineler
                </div>
                <div class="card-body">
                    <?php if(empty($aktifMakineler)): ?>
                        <p class="text-muted text-center py-4">Şu anda aktif makine bulunmuyor.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Makine</th>
                                        <th>Çalışma Saati</th>
                                        <th>Aktif Üretim</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($aktifMakineler as $makine): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($makine['MakineAdi'] ?? ''); ?></strong></td>
                                        <td><?php echo number_format($makine['CalismaSaati'], 1); ?> saat</td>
                                        <td>
                                            <?php if($makine['AktifUretim'] > 0): ?>
                                                <span class="badge bg-success">Üretimde</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Boşta</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-success">Çalışıyor</span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="makine-kontrol.php" class="btn btn-primary">Tüm Makineleri Görüntüle</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Son Üretim Kayıtları
                </div>
                <div class="card-body">
                    <?php if(empty($sonUretimler)): ?>
                        <p class="text-muted text-center py-4">Henüz üretim kaydı bulunmuyor.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Makine</th>
                                        <th>Ürün Tipi</th>
                                        <th>İlerleme</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sonUretimler as $uretim): 
                                        $ilerleme = $uretim['HedefMiktar'] > 0 ? ($uretim['UretilenMiktar'] / $uretim['HedefMiktar']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($uretim['MakineAdi'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($uretim['UrunTipi'] ?? ''); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $ilerleme; ?>%">
                                                    <?php echo number_format($ilerleme, 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $durumClass = 'secondary';
                                            // DÜZELTME: Hem boşluklu hem bitişik yazımı kontrol ediyoruz
                                            if($uretim['Durum'] == 'Devam Ediyor' || $uretim['Durum'] == 'DevamEdiyor') $durumClass = 'success';
                                            if($uretim['Durum'] == 'Tamamlandı' || $uretim['Durum'] == 'Tamamlandi') $durumClass = 'primary';
                                            if($uretim['Durum'] == 'Durduruldu') $durumClass = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $durumClass; ?>"><?php echo htmlspecialchars($uretim['Durum'] ?? ''); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="uretim-takip.php" class="btn btn-primary">Tüm Üretimleri Görüntüle</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>