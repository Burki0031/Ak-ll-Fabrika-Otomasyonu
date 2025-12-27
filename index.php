<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akıllı Fabrika Otomasyonu - Anasayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern ve Endüstriyel Renk Paleti */
        :root {
            --primary-color: #0d6efd;
            --dark-bg: #212529;
            --hero-overlay: rgba(0, 0, 0, 0.7);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Ayarları */
        .navbar {
            background-color: rgba(33, 37, 41, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Hero (Giriş) Bölümü */
        .hero-section {
            background: linear-gradient(var(--hero-overlay), var(--hero-overlay)), url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            animation: fadeInDown 1s ease-out;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #ddd;
            animation: fadeInUp 1s ease-out;
        }

        /* Özellik Kartları */
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            background: #fff;
            padding: 20px;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        /* Footer */
        footer {
            background-color: var(--dark-bg);
            color: #bbb;
            padding: 40px 0;
        }

        /* Animasyonlar */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-industry me-2"></i>AKILLI FABRİKA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="#anasayfa">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#ozellikler">Özellikler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#hakkimizda">Hakkımızda</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="login.php" class="btn btn-primary rounded-pill px-4">Giriş Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="anasayfa" class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">Üretimin Geleceği Burada</h1>
                    <p class="hero-subtitle">
                        Endüstri 4.0 standartlarında makine takibi, üretim yönetimi ve arıza bildirim sistemi. 
                        Verilerinizi dijitalleştirin, verimliliğinizi artırın.
                    </p>
                    <a href="login.php" class="btn btn-lg btn-outline-light rounded-pill px-5 py-3 mt-3">
                        Sisteme Giriş Yap <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <section id="ozellikler" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Neler Yapabilirsiniz?</h2>
                <p class="text-muted">Fabrikanızı tek bir panelden yönetmek için ihtiyacınız olan her şey.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-chart-line feature-icon"></i>
                            <h5 class="card-title">Canlı Üretim Takibi</h5>
                            <p class="card-text text-muted">Makinelerin anlık üretim durumlarını, hedef ve gerçekleşen adetleri gerçek zamanlı izleyin.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-triangle-exclamation feature-icon"></i>
                            <h5 class="card-title">Hızlı Arıza Bildirimi</h5>
                            <p class="card-text text-muted">Mobil uyumlu arayüz sayesinde sahadan anında arıza kaydı oluşturun ve müdahale süresini kısaltın.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card text-center">
                        <div class="card-body">
                            <i class="fa-solid fa-file-pdf feature-icon"></i>
                            <h5 class="card-title">Detaylı Raporlama</h5>
                            <p class="card-text text-muted">Geçmişe dönük üretim ve arıza verilerini analiz edin, tek tıkla PDF raporları oluşturun.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="hakkimizda" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1581091226033-d5c48150dbaa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Fabrika Yönetimi" class="img-fluid rounded shadow">
                </div>
                <div class="col-md-6 mt-4 mt-md-0 ps-md-5">
                    <h2 class="fw-bold mb-3">Manisa CBÜ - Akıllı Fabrika Projesi</h2>
                    <p class="lead">Bu proje, Bilgisayar Programcılığı Bölümü öğrencileri Yiğit Pınar Ve Burak Aşır tarafından bitirme projesi olarak geliştirilmiştir.</p>
                    <p class="text-muted">
                        Masaüstü (C#) ve Web (PHP) platformlarının entegre çalıştığı bu sistemde amaç, kağıt israfını önlemek ve fabrikalardaki veri akışını dijital ortama taşıyarak şeffaf bir yönetim sağlamaktır.
                    </p>
                    <ul class="list-unstyled mt-3">
                        <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i> Mobil Uyumlu Arayüz</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i> Güvenli Veritabanı Altyapısı</li>
                        <li class="mb-2"><i class="fa-solid fa-check text-primary me-2"></i> Kullanıcı Dostu Panel</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container text-center">
            <h4 class="text-white mb-3">Akıllı Fabrika Yönetim Sistemi</h4>
            <p class="mb-1">Geliştiriciler: Yiğit Pınar & Burak Aşır</p>
            <p class="small text-muted">Manisa Celal Bayar Üniversitesi - 2025</p>
            <div class="mt-4">
                <a href="#" class="text-white me-3"><i class="fa-brands fa-linkedin fa-lg"></i></a>
                <a href="#" class="text-white"><i class="fa-brands fa-github fa-lg"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>