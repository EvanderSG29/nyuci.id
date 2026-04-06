@echo off
setlocal EnableExtensions EnableDelayedExpansion

echo ==============================
echo SETUP HTTPS NYUCI.ID
echo ==============================

:: EDIT jika path kamu berubah
set "PROJECT_PATH=C:\xampp\htdocs\Project 2026\nyuci.id"
set "MKCERT_PATH=C:\mkcert"
set "APACHE_CONF=C:\xampp\apache\conf\httpd.conf"
set "NYUCI_SSL_CONF=C:\xampp\apache\conf\extra\nyuci-ssl.conf"
set "HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts"
set "CERT_FILE=%MKCERT_PATH%\nyuci.id.pem"
set "KEY_FILE=%MKCERT_PATH%\nyuci.id-key.pem"
set "HTTPD_BIN=C:\xampp\apache\bin\httpd.exe"

echo.
echo [0] Validasi path
if not exist "%PROJECT_PATH%\public" (
    echo ERROR: Folder project tidak ditemukan: "%PROJECT_PATH%"
    goto :fail
)
if not exist "%MKCERT_PATH%\mkcert.exe" (
    echo ERROR: mkcert.exe tidak ditemukan di: "%MKCERT_PATH%"
    goto :fail
)
if not exist "%APACHE_CONF%" (
    echo ERROR: Apache config tidak ditemukan: "%APACHE_CONF%"
    goto :fail
)

echo.
echo [1] Install local CA (mkcert)
cd /d "%MKCERT_PATH%"
mkcert -install
if errorlevel 1 goto :fail

echo.
echo [2] Generate SSL certificate: nyuci.id
mkcert -cert-file "%CERT_FILE%" -key-file "%KEY_FILE%" nyuci.id
if errorlevel 1 goto :fail

echo.
echo [3] Tambah hosts (idempotent)
powershell -NoProfile -Command ^
  "$p='%HOSTS_FILE%';" ^
  "$line='127.0.0.1 nyuci.id';" ^
  "$c=Get-Content $p -Raw;" ^
  "if($c -notmatch '(?m)^\s*127\.0\.0\.1\s+nyuci\.id(\s|$)'){ Add-Content -Path $p -Value $line; Write-Output 'hosts updated.' } else { Write-Output 'hosts entry already exists.' }"

echo.
echo [4] Tulis VirtualHost nyuci.id (80/443)
(
echo ^<VirtualHost *:80^>
echo     ServerName nyuci.id
echo     DocumentRoot "%PROJECT_PATH%\public"
echo     Redirect permanent / https://nyuci.id/
echo ^</VirtualHost^>
echo.
echo ^<VirtualHost *:443^>
echo     ServerName nyuci.id
echo     DocumentRoot "%PROJECT_PATH%\public"
echo.
echo     SSLEngine on
echo     SSLCertificateFile "%CERT_FILE%"
echo     SSLCertificateKeyFile "%KEY_FILE%"
echo.
echo     ^<Directory "%PROJECT_PATH%\public"^>
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo ^</VirtualHost^>
) > "%NYUCI_SSL_CONF%"

echo.
echo [5] Include nyuci-ssl.conf ke httpd.conf (jika belum ada)
findstr /C:"Include conf/extra/nyuci-ssl.conf" "%APACHE_CONF%" >nul
if errorlevel 1 (
    echo Include conf/extra/nyuci-ssl.conf>>"%APACHE_CONF%"
    echo include line added.
) else (
    echo include line already exists.
)

echo.
echo [6] Aktifkan module SSL + socache
powershell -NoProfile -Command ^
  "$p='%APACHE_CONF%';" ^
  "$c=Get-Content $p -Raw;" ^
  "$c=$c -replace '(?m)^\s*#\s*LoadModule\s+ssl_module','LoadModule ssl_module';" ^
  "$c=$c -replace '(?m)^\s*#\s*LoadModule\s+socache_shmcb_module','LoadModule socache_shmcb_module';" ^
  "$c=$c -replace '(?m)^\s*Include\s+conf/extra/httpd-ssl\.conf','## Include conf/extra/httpd-ssl.conf (disabled by setup-https.bat)';" ^
  "Set-Content -Path $p -Value $c -Encoding ASCII"

echo.
echo [7] Update APP_URL di .env
if exist "%PROJECT_PATH%\.env" (
    powershell -NoProfile -Command ^
      "$p='%PROJECT_PATH%\.env';" ^
      "$c=Get-Content $p -Raw;" ^
      "if($c -match '(?m)^APP_URL='){ $c=[regex]::Replace($c,'(?m)^APP_URL=.*$','APP_URL=https://nyuci.id') } else { $c=$c.TrimEnd() + [Environment]::NewLine + 'APP_URL=https://nyuci.id' + [Environment]::NewLine };" ^
      "Set-Content -Path $p -Value $c -Encoding UTF8"
    echo APP_URL updated.
) else (
    echo WARNING: .env tidak ditemukan, lewati update APP_URL.
)

echo.
echo [8] Apache config test
if exist "%HTTPD_BIN%" (
    "%HTTPD_BIN%" -t
) else (
    echo WARNING: httpd.exe tidak ditemukan, lewati config test.
)

echo.
echo ==============================
echo SELESAI
echo Restart Apache di XAMPP
echo Buka: https://nyuci.id
echo ==============================
goto :eof

:fail
echo.
echo SETUP GAGAL. Periksa pesan error di atas.
exit /b 1
