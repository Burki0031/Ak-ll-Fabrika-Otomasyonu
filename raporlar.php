<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'baglanti.php';
include 'includes/auth.php';

// --- HATA DÜZELTİLDİ ---
// Eski Hatalı Kod: checkRole(['Yönetici']|| ['Admin']);
// Doğru Kod: Array içinde virgülle ayırıyoruz.
checkRole(['Yönetici', 'Admin']); 

$pageTitle = "Raporlar";

// Filtreleme parametreleri
$baslangic_tarihi = $_GET['baslangic'] ?? date('Y-m-d', strtotime('-30 days'));
$bitis_tarihi = $_GET['bitis'] ?? date('Y-m-d');
$rapor_tipi = $_GET['rapor_tipi'] ?? 'uretim';

// Rapor verilerini çek
$raporVerileri = [];

if($rapor_tipi == 'uretim') {
    $sql = "SELECT m.MakineAdi, 
            COUNT(u.UretimID) as UretimSayisi,
            SUM(u.UretilenMiktar) as ToplamUretim,
            SUM(u.HedefMiktar) as ToplamHedef,
            AVG(u.UretilenMiktar * 100.0 / NULLIF(u.HedefMiktar, 0)) as BasariOrani
            FROM Uretimler u
            INNER JOIN Makineler m ON u.MakineID = m.MakineID
            WHERE CAST(u.BaslangicZamani AS DATE) BETWEEN ? AND ?
            GROUP BY m.MakineAdi
            ORDER BY ToplamUretim DESC";
    $params = array($baslangic_tarihi, $bitis_tarihi);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt !== false) {
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $raporVerileri[] = $row;
        }
    }
} elseif($rapor_tipi == 'ariza') {
    $sql = "SELECT m.MakineAdi,
            COUNT(a.ArizaID) as ArizaSayisi,
            SUM(DATEDIFF(HOUR, a.BaslangicZamani, ISNULL(a.BitisZamani, GETDATE()))) as ToplamDurusSuresi,
            AVG(DATEDIFF(HOUR, a.BaslangicZamani, ISNULL(a.BitisZamani, GETDATE()))) as OrtalamaCozulmeSuresi
            FROM Arizalar a
            INNER JOIN Makineler m ON a.MakineID = m.MakineID
            WHERE CAST(a.BaslangicZamani AS DATE) BETWEEN ? AND ?
            GROUP BY m.MakineAdi
            ORDER BY ArizaSayisi DESC";
    $params = array($baslangic_tarihi, $bitis_tarihi);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt !== false) {
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $raporVerileri[] = $row;
        }
    }
} elseif($rapor_tipi == 'makine') {
    $sql = "SELECT m.MakineAdi, m.UretimHatti, m.Model, m.Durum, m.CalismaSaati,
            (SELECT COUNT(*) FROM Uretimler u WHERE u.MakineID = m.MakineID) as ToplamUretim,
            (SELECT SUM(u.UretilenMiktar) FROM Uretimler u WHERE u.MakineID = m.MakineID) as ToplamUretilenMiktar,
            (SELECT COUNT(*) FROM Arizalar a WHERE a.MakineID = m.MakineID) as ToplamAriza
            FROM Makineler m
            ORDER BY m.MakineAdi";
    $stmt = sqlsrv_query($conn, $sql);
    if($stmt !== false) {
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $raporVerileri[] = $row;
        }
    }
}

include 'includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2"><i class="bi bi-file-earmark-text"></i> Raporlar</h1>
            <p class="text-muted">Filtrelenebilir ve indirilebilir raporlar</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-funnel"></i> Rapor Filtreleri
                </div>
                <div class="card-body">
                    <form method="GET" action="raporlar.php" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Rapor Tipi</label>
                            <select name="rapor_tipi" class="form-select">
                                <option value="uretim" <?php echo $rapor_tipi == 'uretim' ? 'selected' : ''; ?>>Üretim Raporu</option>
                                <option value="ariza" <?php echo $rapor_tipi == 'ariza' ? 'selected' : ''; ?>>Arıza Raporu</option>
                                <option value="makine" <?php echo $rapor_tipi == 'makine' ? 'selected' : ''; ?>>Makine Raporu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Başlangıç Tarihi</label>
                            <input type="date" name="baslangic" class="form-control" value="<?php echo $baslangic_tarihi; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bitiş Tarihi</label>
                            <input type="date" name="bitis" class="form-control" value="<?php echo $bitis_tarihi; ?>" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-50">
                                <i class="bi bi-search"></i> Filtrele
                            </button>
                            <button type="button" class="btn btn-danger w-50" onclick="exportToPDF()">
                                <i class="bi bi-file-pdf"></i> PDF İndir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-table"></i> 
                    <span id="raporBasligi">
                    <?php 
                    if($rapor_tipi == 'uretim') echo 'Üretim Performans Raporu';
                    elseif($rapor_tipi == 'ariza') echo 'Arıza Analiz Raporu';
                    else echo 'Makine Durum Raporu';
                    ?>
                    </span>
                    <span class="badge bg-primary ms-2"><?php echo count($raporVerileri); ?> kayıt</span>
                </div>
                <div class="card-body">
                    <?php if(empty($raporVerileri)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle"></i> Seçilen kriterlere uygun veri bulunamadı.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered" id="raporTablosu">
                                <thead class="table-dark">
                                    <tr>
                                        <?php if($rapor_tipi == 'uretim'): ?>
                                            <th>Makine</th>
                                            <th>Üretim Sayısı</th>
                                            <th>Toplam Üretim</th>
                                            <th>Toplam Hedef</th>
                                            <th>Başarı Oranı</th>
                                        <?php elseif($rapor_tipi == 'ariza'): ?>
                                            <th>Makine</th>
                                            <th>Arıza Sayısı</th>
                                            <th>Toplam Duruş</th>
                                            <th>Ort. Çözülme</th>
                                        <?php else: ?>
                                            <th>Makine</th>
                                            <th>Hat / Model</th>
                                            <th>Durum</th>
                                            <th>Çalışma Saati</th>
                                            <th>Top. Üretim</th>
                                            <th>Top. Arıza</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($raporVerileri as $veri): ?>
                                    <tr>
                                        <?php if($rapor_tipi == 'uretim'): ?>
                                            <td><strong><?php echo htmlspecialchars($veri['MakineAdi'] ?? ''); ?></strong></td>
                                            <td><?php echo $veri['UretimSayisi']; ?></td>
                                            <td><?php echo number_format($veri['ToplamUretim']); ?></td>
                                            <td><?php echo number_format($veri['ToplamHedef']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $veri['BasariOrani'] >= 90 ? 'success' : ($veri['BasariOrani'] >= 70 ? 'warning' : 'danger'); ?>">
                                                    %<?php echo number_format($veri['BasariOrani'], 1); ?>
                                                </span>
                                            </td>
                                        <?php elseif($rapor_tipi == 'ariza'): ?>
                                            <td><strong><?php echo htmlspecialchars($veri['MakineAdi'] ?? ''); ?></strong></td>
                                            <td><span class="badge bg-danger"><?php echo $veri['ArizaSayisi']; ?></span></td>
                                            <td><?php echo number_format($veri['ToplamDurusSuresi']); ?> saat</td>
                                            <td><?php echo number_format($veri['OrtalamaCozulmeSuresi'], 1); ?> saat</td>
                                        <?php else: ?>
                                            <td><strong><?php echo htmlspecialchars($veri['MakineAdi'] ?? ''); ?></strong></td>
                                            <td><?php echo htmlspecialchars($veri['UretimHatti'] ?? '-'); ?> / <?php echo htmlspecialchars($veri['Model'] ?? '-'); ?></td>
                                            <td>
                                                <?php 
                                                $durumClass = 'secondary';
                                                if($veri['Durum'] == 'Calisiyor') $durumClass = 'success';
                                                if($veri['Durum'] == 'Arizali') $durumClass = 'danger';
                                                if($veri['Durum'] == 'Bakimda') $durumClass = 'warning';
                                                ?>
                                                <span class="badge bg-<?php echo $durumClass; ?>"><?php echo htmlspecialchars($veri['Durum'] ?? ''); ?></span>
                                            </td>
                                            <td><?php echo number_format($veri['CalismaSaati'], 1); ?> saat</td>
                                            <td><?php echo number_format($veri['ToplamUretilenMiktar'] ?? 0); ?></td>
                                            <td><span class="badge bg-<?php echo $veri['ToplamAriza'] > 5 ? 'danger' : 'warning'; ?>"><?php echo $veri['ToplamAriza']; ?></span></td>
                                        <?php endif; ?>
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
// --- PDF İNDİRME FONKSİYONU ---
function exportToPDF() {
    const { jsPDF } = window.jspdf;
    
    // PDF nesnesi oluştur
    const doc = new jsPDF();
    
    // Font ayarları (Türkçe karakter desteği için varsayılan fontlar bazen sorun çıkarabilir, 
    // ancak autoTable genellikle iyi iş çıkarır)
    
    // Başlık
    const baslik = document.getElementById('raporBasligi').innerText;
    doc.setFontSize(18);
    doc.text(baslik, 14, 20);
    
    doc.setFontSize(11);
    doc.text("Tarih: <?php echo date('d.m.Y'); ?>", 14, 28);
    
    // Tabloyu PDF'e Çevir
    doc.autoTable({ 
        html: '#raporTablosu',
        startY: 35,
        theme: 'grid',
        headStyles: { fillColor: [41, 128, 185] }, // Tablo başlık rengi (Mavi)
        styles: { 
            fontSize: 10,
            cellPadding: 3
        },
        // Türkçe karakterlerde bozulma olursa font ayarı gerekebilir
        didParseCell: function(data) {
            // Badge içindeki metinleri temizle (Sadece metni al)
            // Bu kısım opsiyoneldir, tablo düzgün görünüyorsa gerek yok
        }
    });
    
    // Dosyayı İndir
    doc.save('rapor_cikti.pdf');
}
</script>

<?php include 'includes/footer.php'; ?>