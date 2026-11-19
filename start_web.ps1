Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " Membuka Dashboard Auto-Injector Google Form..." -ForegroundColor Green
Write-Host " Alamat: http://localhost:3000" -ForegroundColor Yellow
Write-Host "====================================================" -ForegroundColor Cyan

Start-Process "http://localhost:3000"
Set-Location $PSScriptRoot
node server.js
