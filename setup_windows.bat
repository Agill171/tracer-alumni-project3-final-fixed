@echo off
setlocal
cd /d "%~dp0"

echo ==========================================
echo Setup Sistem Pelacakan Alumni
echo Lokasi project: %CD%
echo ==========================================

where php >nul 2>nul || (echo PHP tidak ditemukan pada PATH.& pause & exit /b 1)
where composer >nul 2>nul || (echo Composer tidak ditemukan pada PATH.& pause & exit /b 1)
where npm >nul 2>nul || (echo Node.js/npm tidak ditemukan pada PATH.& pause & exit /b 1)

if not exist artisan (
    echo File artisan tidak ditemukan di folder project.
    echo Pastikan file batch berada satu folder dengan artisan.
    pause
    exit /b 1
)

if not exist .env copy .env.example .env
if not exist database\database.sqlite type nul > database\database.sqlite

call composer install || (echo Composer install gagal.& pause & exit /b 1)
call npm install || (echo NPM install gagal.& pause & exit /b 1)
php artisan key:generate || (echo Pembuatan application key gagal.& pause & exit /b 1)
php artisan migrate --seed || (echo Migrasi atau seeder gagal.& pause & exit /b 1)
call npm run build || (echo Build frontend gagal.& pause & exit /b 1)

echo.
echo Setup selesai.
echo Login: admin@traceralumni.test / password
echo Jalankan aplikasi dengan run_windows.bat
pause
endlocal
