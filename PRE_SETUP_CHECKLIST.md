# Pre-Setup Checklist for New Laptop

## Prerequisites Installation Checklist

Before running the setup script, ensure you have installed:

### 1. PHP 8.2+
- [ ] Download from https://www.php.net/downloads
- [ ] Run installer (Windows: use the ZIP or MSI)
- [ ] Verify: Open PowerShell and run `php --version`
  - Expected output: `PHP 8.2.0` or higher
- [ ] Add PHP to PATH if not automatic
  - Right-click "This PC" → Properties → Advanced → Environment Variables → Path → add PHP folder

### 2. Composer
- [ ] Download from https://getcomposer.org/download
- [ ] Run installer (Windows: use .exe)
- [ ] Verify: `composer --version`
  - Expected output: `Composer 2.x.x`
  
### 3. Node.js & npm
- [ ] Download from https://nodejs.org/ (LTS version)
- [ ] Run installer (Windows: use .msi)
- [ ] Verify: `node --version` and `npm --version`
  - Expected output: `v18.x.x` or higher and `9.x.x` or higher

### 4. Git
- [ ] Download from https://git-scm.com/download/win
- [ ] Run installer, use default settings
- [ ] Verify: `git --version`
  - Expected output: `git version 2.x.x`

### 5. Database (Choose ONE)

#### Option A: SQLite (Easiest - No Installation)
- [ ] Already included with PHP
- [ ] No configuration needed

#### Option B: MySQL
- [ ] Download from https://dev.mysql.com/downloads/mysql/
- [ ] Run installer
- [ ] Set root password and remember it
- [ ] Verify by opening MySQL Command Line Client
- [ ] Run: `SELECT VERSION();`

#### Option C: PostgreSQL
- [ ] Download from https://www.postgresql.org/download/windows/
- [ ] Run installer
- [ ] Set postgres password and remember it
- [ ] Verify: `psql --version`

---

## Initial Setup Checklist

After installing prerequisites:

### 1. Prepare Project Directory
- [ ] Navigate to `d:\My Codes\AnmiBeatzMusic`
- [ ] Verify you see:
  - [ ] index.html
  - [ ] script.js
  - [ ] styles.css
  - [ ] music-data.json
  - [ ] audio/ folder
  - [ ] assets/ folder

### 2. Run Setup Script
- [ ] Open PowerShell as Administrator
- [ ] Navigate to project directory: `cd "d:\My Codes\AnmiBeatzMusic"`
- [ ] Run: `powershell -ExecutionPolicy Bypass -File setup.ps1`
- [ ] Follow the prompts and select your database choice

### 3. Verify Backend Setup
- [ ] Check `backend` folder was created
- [ ] Check `backend/.env` exists
- [ ] Open PowerShell and run:
  ```bash
  cd backend
  php artisan migrate
  ```
  - Expected: Shows "Migrating:" messages
- [ ] Run: `php artisan tinker`
  - Expected: Shows `Psy Shell` prompt
  - Type: `Track::count()` and press Enter
  - Type: `exit` to quit

### 4. Verify Frontend Setup
- [ ] Check `frontend` folder was created
- [ ] Check `frontend/node_modules` exists
- [ ] Open PowerShell and run:
  ```bash
  cd frontend
  npm list vue
  ```
  - Expected: Shows `vue@3.x.x`

### 5. Copy Data Files
- [ ] Check `backend/public/audio/` has audio files
- [ ] Check `backend/public/assets/` has image files

---

## Running the Application Checklist

### Prerequisites Check
- [ ] Both PHP and Node.js are installed and in PATH
- [ ] You have a database created (MySQL/PostgreSQL) or using SQLite
- [ ] All files were copied in previous step

### Start Backend (Terminal 1)
- [ ] Open PowerShell Terminal 1
- [ ] Navigate: `cd "d:\My Codes\AnmiBeatzMusic\backend"`
- [ ] Run: `php artisan serve`
- [ ] Expected output:
  ```
  INFO  Server running on [http://127.0.0.1:8000]
  ```
- [ ] [ ] Keep this terminal open

### Start Frontend (Terminal 2)
- [ ] Open PowerShell Terminal 2
- [ ] Navigate: `cd "d:\My Codes\AnmiBeatzMusic\frontend"`
- [ ] Run: `npm run dev`
- [ ] Expected output:
  ```
  VITE v4.x.x  ready in 123 ms
  ➜  Local:   http://localhost:5173/
  ```
- [ ] [ ] Keep this terminal open

### Test Application
- [ ] Open browser to http://localhost:5173
- [ ] [ ] See Anmi Beatz logo and title
- [ ] [ ] See "Stream Now" button
- [ ] [ ] Scroll down and see music tracks
- [ ] [ ] Click on a track's Play button
- [ ] [ ] Audio player appears at bottom
- [ ] [ ] Click different genre filters
- [ ] [ ] Tracks change based on filter
- [ ] Open browser console (F12 → Console)
- [ ] [ ] No red error messages

### Test API
- [ ] Open new browser tab
- [ ] Navigate to: http://localhost:8000/api/tracks
- [ ] [ ] See JSON data with all tracks
- [ ] Navigate to: http://localhost:8000/api/tracks/featured
- [ ] [ ] See only featured tracks

---

## Troubleshooting Checklist

### Issue: "PHP command not found"
- [ ] Verify PHP is installed
- [ ] Add to PATH: Right-click This PC → Properties → Advanced System Settings
- [ ] Environment Variables → Path → Edit → Add PHP folder
- [ ] Restart PowerShell

### Issue: "Composer command not found"
- [ ] Reinstall Composer
- [ ] Ensure it's in PATH: `composer --version`

### Issue: "npm command not found"
- [ ] Reinstall Node.js
- [ ] Make sure you selected to add to PATH during installation
- [ ] Restart PowerShell

### Issue: CORS Error in Frontend Console
- [ ] Check `backend/config/cors.php` has `http://localhost:5173`
- [ ] Restart Laravel: Stop backend terminal and run `php artisan serve` again

### Issue: Database Connection Failed
- [ ] SQLite: Check `DATABASE_URL` in .env points to valid path
- [ ] MySQL/PostgreSQL: Verify database exists and credentials are correct
- [ ] Run: `php artisan tinker`
- [ ] Type: `DB::connection()->getPDO();`
- [ ] Should connect without error

### Issue: Tracks Not Showing in Frontend
- [ ] [ ] Verify backend API has data: http://localhost:8000/api/tracks
- [ ] [ ] Check browser console for errors (F12)
- [ ] [ ] Verify API URL in Vue is http://localhost:8000/api
- [ ] [ ] Restart both servers

### Issue: Images/Audio Not Loading
- [ ] Verify files in `backend/public/audio/` and `backend/public/assets/`
- [ ] Check database URLs match file locations
- [ ] Try accessing: http://localhost:8000/assets/images/logos/Anmi%20Beatz%20Logo%201.png
- [ ] Should show the image

### Issue: Port Already in Use
- [ ] Change backend port: `php artisan serve --port=8001`
- [ ] Update frontend `.env.local` to point to new port
- [ ] Change frontend port: `npm run dev -- --port 5174`

---

## Daily Use Checklist

Every time you want to use the application:

1. [ ] Open Terminal 1: 
   ```bash
   cd "d:\My Codes\AnmiBeatzMusic\backend"
   php artisan serve
   ```

2. [ ] Open Terminal 2:
   ```bash
   cd "d:\My Codes\AnmiBeatzMusic\frontend"
   npm run dev
   ```

3. [ ] Open browser: http://localhost:5173

4. [ ] To stop:
   - Press `Ctrl+C` in each terminal

---

## Backup Checklist

Weekly/Before Major Changes:

### Database Backup
```bash
# MySQL
mysqldump -u root -p anmi_beatz > backup-$(Get-Date -Format "yyyy-MM-dd").sql

# PostgreSQL
pg_dump anmi_beatz > backup-$(Get-Date -Format "yyyy-MM-dd").sql

# SQLite
Copy-Item database.sqlite "database-$(Get-Date -Format "yyyy-MM-dd").sqlite"
```

### Files Backup
- [ ] Backup `music-data.json`
- [ ] Backup `.env` file (password)
- [ ] Store in cloud (OneDrive, Google Drive, etc.)

---

## Next Steps After Setup

1. [ ] Read SETUP_GUIDE.md for detailed information
2. [ ] Read DATA_MIGRATION_GUIDE.md to understand data structure
3. [ ] Customize Vue components in `frontend/src/components/`
4. [ ] Customize Tailwind colors in `frontend/tailwind.config.js`
5. [ ] Update social media links
6. [ ] Add more tracks to database
7. [ ] Configure domain and SSL for production

---

## Performance Optimization Checklist

After initial setup works:

- [ ] Enable Laravel caching: `php artisan config:cache`
- [ ] Enable route caching: `php artisan route:cache`
- [ ] Optimize autoloader: `composer install --optimize-autoloader`
- [ ] In frontend: `npm run build` to create optimized version
- [ ] Use database indexing for frequently queried columns

---

## Support Resources

- Laravel Documentation: https://laravel.com/docs
- Vue 3 Documentation: https://vuejs.org/
- Tailwind CSS Docs: https://tailwindcss.com/
- Stack Overflow: Tag questions with `laravel` and `vue3`

**Last Updated**: February 13, 2026
