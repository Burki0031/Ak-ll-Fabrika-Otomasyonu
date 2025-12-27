<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';
include 'includes/auth.php';
checkLogin();

$pageTitle = "Üretim Takibi";

// ---------------------------------------------------------
// FİLTRELEME PARAMETRELERİ
// ---------------------------------------------------------
$filtre_periyot = $_GET['periyot'] ?? 'gunluk';
$filtre_makine = isset($_GET['makine_id']) ? (int)$_GET['makine_id'] : 0; 

// Sadece Grafikler ve İstatistikler için geçerli filtre eki
$sqlEk = ""; 
if($filtre_makine > 0) {
    $sqlEk = " AND MakineID = " . $filtre_makine;
}

// ---------------------------------------------------------
// 1. GÜNLÜK ÜRETİM VERİLERİ (Filtreli)
// ---------------------------------------------------------
$gunlukVeriler = [];
$sql = "SELECT CAST(BaslangicZamani AS DATE) as Tarih, 
        SUM(UretilenMiktar) as ToplamUretim,
        COUNT(*) as UretimSayisi
        FROM Uretimler
        WHERE BaslangicZamani >= DATEADD(DAY, -30, GETDATE())" . $sqlEk . "
        GROUP BY CAST(BaslangicZamani AS DATE)
        ORDER BY Tarih";
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $gunlukVeriler[] = $row;
    }
}

// ---------------------------------------------------------
// 2. HAFTALIK ÜRETİM VERİLERİ (Filtreli)
// ---------------------------------------------------------
$haftalikVeriler = [];
$sql = "SELECT DATEPART(WEEK, BaslangicZamani) as Hafta,
        YEAR(BaslangicZamani) as Yil,
        SUM(UretilenMiktar) as ToplamUretim,
        COUNT(*) as UretimSayisi
        FROM Uretimler
        WHERE BaslangicZamani >= DATEADD(MONTH, -6, GETDATE())" . $sqlEk . "
        GROUP BY DATEPART(WEEK, BaslangicZamani), YEAR(BaslangicZamani)
        ORDER BY Yil, Hafta";
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $haftalikVeriler[] = $row;
    }
}

// ---------------------------------------------------------
// 3. AYLIK ÜRETİM VERİLERİ (Filtreli)
// ---------------------------------------------------------
$aylikVeriler = [];
$sql = "SELECT YEAR(BaslangicZamani) as Yil,
        MONTH(BaslangicZamani) as Ay,
        SUM(UretilenMiktar) as ToplamUretim,
        COUNT(*) as UretimSayisi
        FROM Uretimler
        WHERE BaslangicZamani >= DATEADD(YEAR, -2, GETDATE())" . $sqlEk . "
        GROUP BY YEAR(BaslangicZamani), MONTH(BaslangicZamani)
        ORDER BY Yil, Ay";
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $aylikVeriler[] = $row;
    }
}

// ---------------------------------------------------------
// 4. AKTİF ÜRETİMLER (FİLTRESİZ - HEPSİNİ GÖSTERİR)
// ---------------------------------------------------------
// Burada $sqlEk KULLANILMIYOR. Her zaman tüm aktifleri çeker.
$sql = "SELECT u.*, m.MakineAdi,
        (u.UretilenMiktar * 100.0 / NULLIF(u.HedefMiktar, 0)) as IlerlemeYuzde
        FROM Uretimler u
        INNER JOIN Makineler m ON u.MakineID = m.MakineID
        WHERE u.Durum = 'DevamEdiyor'
        ORDER BY u.BaslangicZamani DESC";

$aktifUretimler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $aktifUretimler[] = $row;
    }
}

// ---------------------------------------------------------
// 5. MAKİNE LİSTESİ (Dropdown İçin)
// ---------------------------------------------------------
$sql = "SELECT MakineID, MakineAdi FROM Makineler ORDER BY MakineAdi";
$makineler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $makineler[] = $row;
    }
}

// ---------------------------------------------------------
// 6. İSTATİSTİK KARTLARI (Filtreli)
// ---------------------------------------------------------
$sql = "SELECT 
        SUM(CASE WHEN CAST(BaslangicZamani AS DATE) = CAST(GETDATE() AS DATE) THEN UretilenMiktar ELSE 0 END) as Bugun,
        SUM(CASE WHEN BaslangicZamani >= DATEADD(DAY, -7, GETDATE()) THEN UretilenMiktar ELSE 0 END) as BuHafta,
        SUM(CASE WHEN BaslangicZamani >= DATEADD(MONTH, -1, GETDATE()) THEN UretilenMiktar ELSE 0 END) as BuAy,
        SUM(UretilenMiktar) as Toplam
        FROM Uretimler
        WHERE 1=1" . $sqlEk; 

$istatistikler = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $istatistikler = $row;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-graph-up"></i> Üretim Takibi</h1>
            <p class="text-muted">Günlük, haftalık ve aylık üretim performans analizi</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card info">
                <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
                <div class="stat-number"><?php echo number_format($istatistikler['Bugun'] ?? 0); ?></div>
                <div class="stat-label">Bugünkü Üretim</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card primary">
                <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
                <div class="stat-number"><?php echo number_format($istatistikler['BuHafta'] ?? 0); ?></div>
                <div class="stat-label">Bu Hafta</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card success">
                <div class="stat-icon"><i class="bi bi-calendar-month"></i></div>
                <div class="stat-number"><?php echo number_format($istatistikler['BuAy'] ?? 0); ?></div>
                <div class="stat-label">Bu Ay</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card warning">
                <div class="stat-icon"><i class="bi bi-bar-chart"></i></div>
                <div class="stat-number"><?php echo number_format($istatistikler['Toplam'] ?? 0); ?></div>
                <div class="stat-label">Toplam Üretim</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Periyot</label>
                            <select name="periyot" class="form-select">
                                <option value="gunluk" <?php echo $filtre_periyot == 'gunluk' ? 'selected' : ''; ?>>Günlük</option>
                                <option value="haftalik" <?php echo $filtre_periyot == 'haftalik' ? 'selected' : ''; ?>>Haftalık</option>
                                <option value="aylik" <?php echo $filtre_periyot == 'aylik' ? 'selected' : ''; ?>>Aylık</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Makine (Grafikler İçin)</label>
                            <select name="makine_id" class="form-select">
                                <option value="0">Tümü</option>
                                <?php foreach($makineler as $makine): ?>
                                <option value="<?php echo $makine['MakineID']; ?>" <?php echo $filtre_makine == $makine['MakineID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($makine['MakineAdi']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrele
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-bar-chart-line"></i> Üretim Performans Grafiği
                    <?php if($filtre_makine > 0): ?>
                        <span class="badge bg-info text-dark ms-2">Seçili Makineye Göre Filtrelendi</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <canvas id="uretimGrafik" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-play-circle"></i> Aktif Üretimler
                    </div>
                    <span class="badge bg-success">Canlı İzleme (Tüm Makineler)</span>
                </div>
                <div class="card-body">
                    <?php if(empty($aktifUretimler)): ?>
                        <p class="text-muted text-center py-4">Şu an fabrikada aktif üretim bulunmuyor.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Makine</th>
                                        <th>Ürün Tipi</th>
                                        <th>Hedef</th>
                                        <th>Üretilen</th>
                                        <th>İlerleme</th>
                                        <th>Başlangıç</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($aktifUretimler as $uretim): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($uretim['MakineAdi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($uretim['UrunTipi']); ?></td>
                                        <td><?php echo number_format($uretim['HedefMiktar']); ?></td>
                                        <td><?php echo number_format($uretim['UretilenMiktar']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar <?php echo ($uretim['IlerlemeYuzde'] >= 100) ? 'bg-success' : ''; ?>" 
                                                     role="progressbar" 
                                                     style="width: <?php echo min(100, $uretim['IlerlemeYuzde']); ?>%">
                                                    <?php echo number_format($uretim['IlerlemeYuzde'], 1); ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php 
                                        if($uretim['BaslangicZamani'] instanceof DateTime) {
                                            echo $uretim['BaslangicZamani']->format('d.m.Y H:i');
                                        } else {
                                            echo date('d.m.Y H:i', strtotime($uretim['BaslangicZamani']));
                                        }
                                        ?></td>
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

<script>
// Grafik verilerini hazırla
<?php
$labels = [];
$data = [];

if($filtre_periyot == 'gunluk') {
    foreach($gunlukVeriler as $veri) {
        if($veri['Tarih'] instanceof DateTime) {
            $labels[] = $veri['Tarih']->format('d.m');
        } else {
            $labels[] = date('d.m', strtotime($veri['Tarih']));
        }
        $data[] = $veri['ToplamUretim'];
    }
} elseif($filtre_periyot == 'haftalik') {
    foreach($haftalikVeriler as $veri) {
        $labels[] = $veri['Yil'] . ' - Hafta ' . $veri['Hafta'];
        $data[] = $veri['ToplamUretim'];
    }
} else {
    $aylar = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    foreach($aylikVeriler as $veri) {
        $labels[] = $aylar[$veri['Ay']] . ' ' . $veri['Yil'];
        $data[] = $veri['ToplamUretim'];
    }
}
?>

const ctx = document.getElementById('uretimGrafik').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Üretim Miktarı',
            data: <?php echo json_encode($data); ?>,
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>