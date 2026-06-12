@echo off
REM ================================================
REM  Retro Komputer POS - Dev Runner
REM  Jalankan backend (Laravel) + frontend (Vite)
REM  di dua jendela terpisah.
REM ================================================

setlocal
set ROOT=%~dp0

echo.
echo  ===============================================
echo   Retro Komputer POS - Starting Dev Servers
echo  ===============================================
echo.
echo  Backend  : http://127.0.0.1:8000
echo  Frontend : http://localhost:5173
echo.
echo  Tip: tutup kedua jendela untuk menghentikan.
echo.

start "Retrokomputer Backend"  cmd /k "cd /d %ROOT%backend  && php artisan serve"
start "Retrokomputer Frontend" cmd /k "cd /d %ROOT%frontend && npm run dev"

endlocal
