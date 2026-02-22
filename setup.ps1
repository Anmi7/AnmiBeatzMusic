#!/usr/bin/env powershell
# AnmiBeatzMusic - Automated Setup Script
# This script automates the initial setup on a new laptop
# Usage: powershell -ExecutionPolicy Bypass -File setup.ps1

$ErrorActionPreference = "Stop"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "Anmi Beatz - Full Setup Script" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Check prerequisites
Write-Host "Checking prerequisites..." -ForegroundColor Yellow

function CheckCommand {
    param($command, $name)
    try {
        & $command 2>&1 > $null
        Write-Host "✓ $name found" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "✗ $name NOT found - Please install from SETUP_GUIDE.md" -ForegroundColor Red
        return $false
    }
}

$allGood = $true
$allGood = (CheckCommand "php --version" "PHP") -and $allGood
$allGood = (CheckCommand "composer --version" "Composer") -and $allGood
$allGood = (CheckCommand "node --version" "Node.js") -and $allGood
$allGood = (CheckCommand "npm --version" "npm") -and $allGood
$allGood = (CheckCommand "git --version" "Git") -and $allGood

if (-not $allGood) {
    Write-Host ""
    Write-Host "Please install missing prerequisites first!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "All prerequisites installed!" -ForegroundColor Green
Write-Host ""

# Get current directory
$projectRoot = Get-Location
Write-Host "Project root: $projectRoot" -ForegroundColor Cyan
Write-Host ""

# Step 1: Backend Setup
Write-Host "Step 1: Setting up Laravel Backend..." -ForegroundColor Yellow
Write-Host "Creating backend directory..." -ForegroundColor Gray

if (-not (Test-Path "backend")) {
    composer create-project laravel/laravel backend --prefer-dist
}
else {
    Write-Host "Backend directory already exists, skipping creation" -ForegroundColor Yellow
}

cd backend
Write-Host "Generating application key..." -ForegroundColor Gray
php artisan key:generate

# Copy and setup .env file
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env" -ErrorAction SilentlyContinue
}

# Ask for database choice
Write-Host ""
Write-Host "Which database do you want to use?" -ForegroundColor Cyan
Write-Host "1. SQLite (Recommended for development)"
Write-Host "2. MySQL"
Write-Host "3. PostgreSQL"
Write-Host ""

$dbChoice = Read-Host "Enter choice (1-3)"

switch ($dbChoice) {
    "1" {
        Write-Host "Setting up SQLite..." -ForegroundColor Green
        php artisan migrate --seed 2>&1 | Write-Host -ForegroundColor Green
    }
    "2" {
        Write-Host "Setting up MySQL..." -ForegroundColor Green
        $dbName = Read-Host "Enter database name (default: anmi_beatz)"
        if ([string]::IsNullOrEmpty($dbName)) { $dbName = "anmi_beatz" }
        
        Write-Host "Please create the database manually:" -ForegroundColor Yellow
        Write-Host "mysql -u root -p" -ForegroundColor Gray
        Write-Host "CREATE DATABASE $dbName;" -ForegroundColor Gray
        
        Read-Host "Press Enter when database is created"
        
        # Update .env
        $envContent = Get-Content ".env"
        $envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=mysql"
        $envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$dbName"
        Set-Content ".env" $envContent
        
        php artisan migrate --seed
    }
    "3" {
        Write-Host "Setting up PostgreSQL..." -ForegroundColor Green
        $dbName = Read-Host "Enter database name (default: anmi_beatz)"
        if ([string]::IsNullOrEmpty($dbName)) { $dbName = "anmi_beatz" }
        
        Write-Host "Please create the database manually:" -ForegroundColor Yellow
        Write-Host "psql -U postgres" -ForegroundColor Gray
        Write-Host "CREATE DATABASE $dbName;" -ForegroundColor Gray
        
        Read-Host "Press Enter when database is created"
        
        # Update .env
        $envContent = Get-Content ".env"
        $envContent = $envContent -replace "DB_CONNECTION=.*", "DB_CONNECTION=pgsql"
        $envContent = $envContent -replace "DB_DATABASE=.*", "DB_DATABASE=$dbName"
        Set-Content ".env" $envContent
        
        php artisan migrate --seed
    }
    default {
        Write-Host "Invalid choice, using SQLite" -ForegroundColor Yellow
        php artisan migrate --seed
    }
}

cd ..

Write-Host ""
Write-Host "✓ Backend setup complete!" -ForegroundColor Green
Write-Host ""

# Step 2: Frontend Setup
Write-Host "Step 2: Setting up Vue 3 + Tailwind Frontend..." -ForegroundColor Yellow
Write-Host "Creating frontend directory..." -ForegroundColor Gray

if (-not (Test-Path "frontend")) {
    npm create vite@latest frontend -- --template vue --yes
}

cd frontend

Write-Host "Installing dependencies..." -ForegroundColor Gray
npm install

Write-Host "Installing Tailwind CSS..." -ForegroundColor Gray
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p --yes

Write-Host "Installing Axios..." -ForegroundColor Gray
npm install axios

Write-Host "Installing Font Awesome..." -ForegroundColor Gray
npm install @fortawesome/fontawesome-free

Write-Host ""
Write-Host "✓ Frontend setup complete!" -ForegroundColor Green
Write-Host ""

# Step 3: Copy Assets
Write-Host "Step 3: Copying audio and image files..." -ForegroundColor Yellow

if (Test-Path "..\audio") {
    Copy-Item "..\audio" -Destination "..\backend\public\audio" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "✓ Audio files copied" -ForegroundColor Green
}

if (Test-Path "..\assets") {
    Copy-Item "..\assets" -Destination "..\backend\public\assets" -Recurse -Force -ErrorAction SilentlyContinue
    Write-Host "✓ Asset files copied" -ForegroundColor Green
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "To run the application, open TWO terminals:" -ForegroundColor Cyan
Write-Host ""
Write-Host "Terminal 1 (Backend):" -ForegroundColor Yellow
Write-Host "  cd backend" -ForegroundColor Gray
Write-Host "  php artisan serve" -ForegroundColor Gray
Write-Host ""
Write-Host "Terminal 2 (Frontend):" -ForegroundColor Yellow
Write-Host "  cd frontend" -ForegroundColor Gray
Write-Host "  npm run dev" -ForegroundColor Gray
Write-Host ""
Write-Host "Then open: http://localhost:5173" -ForegroundColor Cyan
Write-Host ""
Write-Host "For more help, see SETUP_GUIDE.md" -ForegroundColor Yellow
