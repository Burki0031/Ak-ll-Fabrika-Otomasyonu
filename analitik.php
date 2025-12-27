<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';
include 'includes/auth.php';
checkLogin();

$pageTitle = "Analitik Dashboard";

// Makine durum dağılımı
$sql = "SELECT Durum, COUNT(*) as Sayi FROM Makineler GROUP BY Durum";
$durumDagilimi = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $durumDagilimi[$row['Durum']] = $row['Sayi'];
    }
}

// En çok arıza yapan makineler (son 30 gün)
$sql = "SELECT TOP 10 m.MakineID, m.MakineAdi, COUNT(a.ArizaID) as ArizaSayisi,
        SUM(DATEDIFF(HOUR, a.BaslangicZamani, ISNULL(a.BitisZamani, GETDATE()))) as ToplamDurusSuresi
        FROM Makineler m
        LEFT JOIN Arizalar a ON m.MakineID = a.MakineID 
            AND a.BaslangicZamani >= DATEADD(DAY, -30, GETDATE())
        GROUP BY m.MakineID, m.MakineAdi
        HAVING COUNT(a.ArizaID) > 0
        ORDER BY ArizaSayisi DESC";
$enCokAriza = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $enCokAriza[] = $row;
    }
}

// Üretim trendleri (son 12 ay)
$sql = "SELECT YEAR(BaslangicZamani) as Yil,
        MONTH(BaslangicZamani) as Ay,
        SUM(UretilenMiktar) as ToplamUretim,
        COUNT(*) as UretimSayisi,
        AVG(UretilenMiktar) as OrtalamaUretim
        FROM Uretimler
        WHERE BaslangicZamani >= DATEADD(MONTH, -12, GETDATE())
        GROUP BY YEAR(BaslangicZamani), MONTH(BaslangicZamani)
        ORDER BY Yil, Ay";
$uretimTrendleri = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $uretimTrendleri[] = $row;
    }
}

// Makine performansı
$sql = "SELECT m.MakineID, m.MakineAdi, m.CalismaSaati,
        (SELECT COUNT(*) FROM Uretimler u WHERE u.MakineID = m.MakineID) as ToplamUretim,
        (SELECT SUM(u.UretilenMiktar) FROM Uretimler u WHERE u.MakineID = m.MakineID) as ToplamUretilenMiktar,
        (SELECT COUNT(*) FROM Arizalar a WHERE a.MakineID = m.MakineID) as ToplamAriza
        FROM Makineler m
        ORDER BY ToplamUretilenMiktar DESC";
$makinePerformans = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $makinePerformans[] = $row;
    }
}

// Arıza çözüm süreleri
$sql = "SELECT AVG(DATEDIFF(HOUR, BaslangicZamani, BitisZamani)) as OrtalamaCozulmeSuresi,
        MIN(DATEDIFF(HOUR, BaslangicZamani, BitisZamani)) as EnHizliCozulme,
        MAX(DATEDIFF(HOUR, BaslangicZamani, BitisZamani)) as EnYavasCozulme
        FROM Arizalar
        WHERE Durum = 'Giderildi' AND BitisZamani IS NOT NULL";
$cozulmeSuresi = [];
$stmt = sqlsrv_query($conn, $sql);
if($stmt !== false) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $cozulmeSuresi = $row;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-bar-chart"></i> Analitik Dashboard</h1>
            <p class="text-muted">Detaylı analiz ve performans metrikleri</p>
        </div>
    </div>

    <!-- Makine Durum Dağılımı -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pie-chart"></i> Makine Durum Dağılımı
                </div>
                <div class="card-body">
                    <canvas id="durumDagilimGrafik" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up-arrow"></i> Üretim Trendleri (Son 12 Ay)
                </div>
                <div class="card-body">
                    <canvas id="uretimTrendGrafik" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- En Çok Arıza Yapan Makineler -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle-fill"></i> En Çok Arıza Yapan Makineler (Son 30 Gün)
                </div>
                <div class="card-body">
                    <?php if(empty($enCokAriza)): ?>
                        <p class="text-muted text-center py-4">Son 30 günde arıza kaydı bulunmuyor.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sıra</th>
                                        <th>Makine</th>
                                        <th>Arıza Sayısı</th>
                                        <th>Toplam Duruş Süresi</th>
                                        <th>Ortalama Duruş</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($enCokAriza as $index => $makine): 
                                        $ortalamaDurus = $makine['ArizaSayisi'] > 0 ? $makine['ToplamDurusSuresi'] / $makine['ArizaSayisi'] : 0;
                                    ?>
                                    <tr>
                                        <td><strong>#<?php echo $index + 1; ?></strong></td>
                                        <td><?php echo htmlspecialchars($makine['MakineAdi']); ?></td>
                                        <td><span class="badge bg-danger"><?php echo $makine['ArizaSayisi']; ?></span></td>
                                        <td><?php echo number_format($makine['ToplamDurusSuresi']); ?> saat</td>
                                        <td><?php echo number_format($ortalamaDurus, 1); ?> saat</td>
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

    <!-- Makine Performansı -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy"></i> Makine Performans Sıralaması
                </div>
                <div class="card-body">
                    <?php if(empty($makinePerformans)): ?>
                        <p class="text-muted text-center py-4">Makine performans verisi bulunamadı.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sıra</th>
                                        <th>Makine</th>
                                        <th>Çalışma Saati</th>
                                        <th>Toplam Üretim</th>
                                        <th>Üretilen Miktar</th>
                                        <th>Toplam Arıza</th>
                                        <th>Verimlilik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($makinePerformans as $index => $makine): 
                                        $verimlilik = $makine['CalismaSaati'] > 0 ? ($makine['ToplamUretilenMiktar'] / $makine['CalismaSaati']) : 0;
                                    ?>
                                    <tr>
                                        <td><strong>#<?php echo $index + 1; ?></strong></td>
                                        <td><?php echo htmlspecialchars($makine['MakineAdi']); ?></td>
                                        <td><?php echo number_format($makine['CalismaSaati'], 1); ?> saat</td>
                                        <td><?php echo $makine['ToplamUretim']; ?> adet</td>
                                        <td><?php echo number_format($makine['ToplamUretilenMiktar'] ?? 0); ?></td>
                                        <td><span class="badge bg-<?php echo $makine['ToplamAriza'] > 5 ? 'danger' : ($makine['ToplamAriza'] > 2 ? 'warning' : 'success'); ?>"><?php echo $makine['ToplamAriza']; ?></span></td>
                                        <td><?php echo number_format($verimlilik, 2); ?> adet/saat</td>
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

    <!-- Arıza Çözüm Süreleri -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="stat-card info">
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                <div class="stat-number"><?php echo number_format($cozulmeSuresi['OrtalamaCozulmeSuresi'] ?? 0, 1); ?></div>
                <div class="stat-label">Ortalama Çözülme Süresi (Saat)</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card success">
                <div class="stat-icon"><i class="bi bi-lightning-fill"></i></div>
                <div class="stat-number"><?php echo number_format($cozulmeSuresi['EnHizliCozulme'] ?? 0, 1); ?></div>
                <div class="stat-label">En Hızlı Çözülme (Saat)</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card warning">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-number"><?php echo number_format($cozulmeSuresi['EnYavasCozulme'] ?? 0, 1); ?></div>
                <div class="stat-label">En Yavaş Çözülme (Saat)</div>
            </div>
        </div>
    </div>
</div>

<script>
// Makine Durum Dağılımı Grafiği
const durumCtx = document.getElementById('durumDagilimGrafik').getContext('2d');
new Chart(durumCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($durumDagilimi)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($durumDagilimi)); ?>,
            backgroundColor: [
                'rgb(16, 185, 129)',  // Çalışıyor - Yeşil
                'rgb(239, 68, 68)',   // Arızalı - Kırmızı
                'rgb(245, 158, 11)',  // Bakımda - Sarı
                'rgb(59, 130, 246)',   // Hazır - Mavi
                'rgb(107, 114, 128)'  // Diğer - Gri
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Üretim Trendleri Grafiği
<?php
$aylar = ['', 'Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
$trendLabels = [];
$trendData = [];
foreach($uretimTrendleri as $trend) {
    $trendLabels[] = $aylar[$trend['Ay']] . ' ' . $trend['Yil'];
    $trendData[] = $trend['ToplamUretim'];
}
?>

const trendCtx = document.getElementById('uretimTrendGrafik').getContext('2d');
new Chart(trendCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($trendLabels); ?>,
        datasets: [{
            label: 'Üretim Miktarı',
            data: <?php echo json_encode($trendData); ?>,
            backgroundColor: 'rgba(37, 99, 235, 0.8)',
            borderColor: 'rgb(37, 99, 235)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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
