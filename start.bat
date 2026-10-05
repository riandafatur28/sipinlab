@echo off
title SipinLab - Server Startup

set PHP=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe
set NODE=C:\laragon\bin\nodejs\node-v22\node.exe
set NPM_CLI=C:\laragon\bin\nodejs\node-v22\node_modules\npm\bin\npm-cli.js
set CLOUDFLARED=%TEMP%\cloudflared.exe
set PATH=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;%PATH%
set APPDIR=%~dp0

cd /d %APPDIR%

echo ============================================
echo   SipinLab - Startup Script
echo ============================================
echo.

:: 1. Pastikan cloudflared ada
if not exist "%CLOUDFLARED%" (
    echo [INFO] Downloading cloudflared...
    powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile '%CLOUDFLARED%' -UseBasicParsing"
)

:: 2. Clear Laravel cache
echo [INFO] Clearing cache...
%PHP% artisan config:clear >nul 2>&1
%PHP% artisan route:clear >nul 2>&1
%PHP% artisan view:clear >nul 2>&1

:: 3. Start Laravel server di window baru
echo [INFO] Starting Laravel server on port 8000...
start "SipinLab - Laravel" cmd /k "%PHP% artisan serve --host=0.0.0.0 --port=8000"

:: 4. Start Cloudflare tunnel di window baru
echo [INFO] Starting Cloudflare tunnel...
start "SipinLab - Tunnel" cmd /k "echo Tunggu URL muncul... && %CLOUDFLARED% tunnel --url http://localhost:8000"

echo.
echo ============================================
echo   Langkah selanjutnya:
echo ============================================
echo.
echo 1. Tunggu ~10 detik hingga URL *.trycloudflare.com muncul
echo    di window "SipinLab - Tunnel"
echo.
echo 2. Salin URL *.trycloudflare.com yang muncul, lalu jalankan:
echo    %PHP% artisan telegram:set-webhook https://xxx.trycloudflare.com
echo.
echo    (APP_URL tetap http://localhost:8000 untuk akses lokal — jangan diubah)
echo.
echo 3. Buka browser: http://localhost:8000
echo.
echo CATATAN: URL Cloudflare berubah setiap restart!
echo          Jalankan ulang perintah telegram:set-webhook setiap kali restart.
echo.
pause
