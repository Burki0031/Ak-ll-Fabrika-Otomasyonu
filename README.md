AKILLI FABRİKA OTOMASYON SİSTEMİ – README
Bu çalışma, Manisa Celal Bayar Üniversitesi Bilgisayar Programcılığı Bölümü bitirme projesi kapsamında geliştirilmiş bir dijital dönüşüm çözümüdür. Proje, fabrika ortamındaki üretim süreçlerini ve makine durumlarını dijitalleştirerek verimliliği artırmayı hedefler.

1. Proje Genel Bakış
Sistem, web tabanlı bir yönetim paneli ve bu panelin yerel ortamda kolayca çalıştırılmasını sağlayan sunucu altyapısından oluşmaktadır.

Geliştiriciler: Yiğit Pınar & Burak Aşır.

Amacı: Kağıt israfını önlemek, veri akışını dijital ortama taşımak ve şeffaf bir yönetim sağlamak.

2. Temel Özellikler
Uygulama, bir fabrikanın ihtiyaç duyabileceği temel modülleri içerir:

Canlı Üretim Takibi: Makinelerin anlık üretim durumlarını, hedef ve gerçekleşen adetleri gerçek zamanlı izleme.

Hızlı Arıza Bildirimi: Sahadan anında arıza kaydı oluşturma ve müdahale süresini kısaltan mobil uyumlu arayüz.

Analitik Dashboard: Makine verimlilikleri, arıza dağılım grafikleri ve 12 aylık üretim trendlerini içeren detaylı raporlama.

Güvenli Giriş Sistemi: Yönetici ve Operatör rolleriyle yetkilendirilmiş kullanıcı erişimi.

3. Kullanılan Teknolojiler
Backend: PHP (Microsoft SQL Server entegrasyonu ile).

Frontend: Bootstrap 5, Font Awesome, Chart.js.

Veritabanı: MS SQL Server.

4. Kurulum ve Çalıştırma Talimatları
Sistemi yerel bilgisayarınızda çalıştırmak için şu adımları izleyin:

Veritabanı Yapılandırması: baglanti.php dosyasındaki sunucu adı (serverName), kullanıcı adı (UID) ve şifre (PWD) bilgilerini kendi SQL Server ayarlarınıza göre güncelleyin.

Sunucuyu Başlatma: Proje klasöründeki baslat.bat dosyasını çalıştırın. Bu script, PHP sunucusunu otomatik olarak localhost:8000 portunda başlatacaktır.

Erişim: Tarayıcınızdan http://localhost:8000 adresine giderek ana sayfaya ulaşabilirsiniz.

5. Varsayılan Giriş Bilgileri
Sistemi test etmek için aşağıdaki hesapları kullanabilirsiniz:

Yönetici: admin / admin123

Operatör: operator / operator123
