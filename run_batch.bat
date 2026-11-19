@echo off
setlocal
cd /d %~dp0

if "%1"=="" (
    echo Penggunaan:
    echo   run_batch.bat 10        ^<-- Submit 10 data berikutnya
    echo   run_batch.bat status    ^<-- Cek status progress saat ini
    echo   run_batch.bat all       ^<-- Submit semua sisa data
    exit /b
)

if "%1"=="status" (
    node injector.js --status
    exit /b
)

if "%1"=="all" (
    node injector.js --delay-min 3 --delay-max 6
    exit /b
)

node injector.js --batch %1 --delay-min 3 --delay-max 6
