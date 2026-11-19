@echo off
title Google Form Auto-Injector Web Dashboard
cd /d %~dp0
echo ====================================================
echo  Membuka Dashboard Auto-Injector Google Form...
echo  Alamat: http://localhost:3000
echo ====================================================
start http://localhost:3000
node server.js
pause
