@echo off
chcp 65001 >nul
title Akilli Fabrika Web Sunucusu
color 0A

echo.
echo ========================================
echo   AKILLI FABRIKA WEB SUNUCUSU
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] PHP kontrol ediliyor...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo [HATA] PHP bulunamadi!
    echo.
    echo Lutfen PHP'yi yukleyin veya PATH'e ekleyin.
    echo XAMPP kullaniyorsaniz: C:\xampp\php\php.exe
    echo.
    pause
    exit /b 1
)

echo [OK] PHP bulundu!
php -v
echo.

echo [2/3] Port kontrol ediliyor...
netstat -an | findstr ":8000" >nul
if %errorlevel% equ 0 (
    echo [UYARI] Port 8000 kullanimda!
    echo.
    echo Alternatif port kullanmak icin bu dosyayi duzenleyin.
    echo.
    set /p port="Port numarasi girin (varsayilan: 8001): "
    if "!port!"=="" set port=8001
) else (
    set port=8000
)

echo [OK] Port %port% kullanilacak
echo.

echo [3/3] Web sunucusu baslatiliyor...
echo.
echo ========================================
echo   SUNUCU BASLATILDI!
echo ========================================
echo.
echo Tarayicinizda su adresi acin:
echo.
echo   http://localhost:%port%
echo.
echo Test sayfasi icin:
echo   http://localhost:%port%/test.php
echo.
echo Giris sayfasi icin:
echo   http://localhost:%port%/login.php
echo.
echo Durdurmak icin CTRL+C basin
echo.
echo ========================================
echo.

php -S localhost:%port%

pause
