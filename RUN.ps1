# Anmi Beatz - One-click run. Double-click or: Right-click -> Run with PowerShell
$ErrorActionPreference = "Stop"
$root = $PSScriptRoot
$backend = Join-Path $root "backend"
$frontend = Join-Path $root "frontend"

# Optional: override password/token from backend\CREDENTIALS.txt (line1=DB password, line2=admin token)
$credPath = Join-Path $backend "CREDENTIALS.txt"
if (Test-Path $credPath) {
  $lines = Get-Content $credPath
  $dbPass = if ($lines.Count -ge 1) { $lines[0].Trim() } else { "postgres" }
  $adminToken = if ($lines.Count -ge 2) { $lines[1].Trim() } else { "testtoken" }
  $envPath = Join-Path $backend ".env"
  (Get-Content $envPath -Raw) -replace "DB_PASSWORD=.*", "DB_PASSWORD=$dbPass" -replace "APP_ADMIN_TOKEN=.*", "APP_ADMIN_TOKEN=$adminToken" | Set-Content $envPath -NoNewline
}

# Migrate + storage link
Set-Location $backend
if (-not (Get-Command php -ErrorAction SilentlyContinue)) { Write-Host "PHP not in PATH. Install PHP and try again."; pause; exit 1 }
Write-Host "Running migrations..."
php artisan migrate --force 2>&1
if ($LASTEXITCODE -ne 0) {
  Write-Host ""
  Write-Host "Migration failed."
  Write-Host "Check PostgreSQL credentials in backend\.env."
  Write-Host "Tip: set your Postgres password on line 1 of backend\CREDENTIALS.txt, then run RUN.ps1 again."
  pause
  exit $LASTEXITCODE
}
php artisan storage:link 2>$null
Write-Host "Starting backend and frontend..."

# Start backend in new window
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$backend'; php artisan serve"

# Start frontend in new window (npm install if needed)
$frontendNodeModules = Join-Path $frontend "node_modules"
$frontendCmd = "cd '$frontend'; if (-not (Test-Path '$frontendNodeModules')) { npm install }; npm run dev"
Start-Process powershell -ArgumentList "-NoExit", "-Command", $frontendCmd

Start-Sleep -Seconds 6
Start-Process "http://localhost:5173"
Write-Host "Done. Site: http://localhost:5173  Admin: http://localhost:5173/admin"
Write-Host "Close the two new PowerShell windows to stop servers."
pause
