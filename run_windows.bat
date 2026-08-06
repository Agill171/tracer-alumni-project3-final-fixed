@echo off
setlocal
cd /d "%~dp0"

if not exist artisan (
    echo File artisan tidak ditemukan di folder project.
    echo Pastikan run_windows.bat berada satu folder dengan artisan.
    pause
    exit /b 1
)

if not exist vendor\autoload.php (
    echo Dependency Composer belum terpasang.
    echo Jalankan setup_windows.bat terlebih dahulu.
    pause
    exit /b 1
)

start "Tracer Queue" cmd /k "cd /d ""%~dp0"" && php artisan queue:work"
start "Tracer Scheduler" cmd /k "cd /d ""%~dp0"" && php artisan schedule:work"

echo Menjalankan aplikasi pada http://127.0.0.1:8000
php artisan serve
pause
endlocal
