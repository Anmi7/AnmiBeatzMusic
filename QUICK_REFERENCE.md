# Quick Reference Guide - Common Commands

## Starting the Application

### Every Time You Want to Work:
```powershell
# Terminal 1 - Backend
cd backend
php artisan serve
# Runs on http://localhost:8000

# Terminal 2 - Frontend  
cd frontend
npm run dev
# Runs on http://localhost:5173
```

---

## Adding New Music Tracks

### Via Database Seeder:
Edit `backend/database/seeders/TrackSeeder.php` and add:
```php
Track::create([
    'title' => 'Your Track Title',
    'artist' => 'Anmi Beatz',
    'genre' => 'hip-hop',
    'image_url' => 'assets/images/music-covers/your-image.jpg',
    'audio_url' => 'audio/your-track.mp3',
    'youtube_link' => 'https://youtube.com/...',
    'spotify_link' => 'https://open.spotify.com/...',
    'release_date' => '2024-02-13',
    'featured' => true,
    'plays' => 0,
    'duration' => '3:45'
]);
```

Then run:
```bash
php artisan db:seed --class=TrackSeeder
```

### Via API (from frontend):
```javascript
// In Vue component
const newTrack = {
    title: 'New Track',
    artist: 'Anmi Beatz',
    genre: 'trap',
    image_url: 'assets/images/music-covers/new.jpg',
    audio_url: 'audio/new-track.mp3',
    release_date: '2024-02-13',
    featured: false,
    plays: 0,
    duration: '3:50'
};

await axios.post('http://localhost:8000/api/tracks', newTrack);
```

### Via Laravel Tinker:
```bash
php artisan tinker
Track::create([
    'title' => 'Track Name',
    'artist' => 'Anmi Beatz',
    'genre' => 'hip-hop',
    'image_url' => 'assets/images/music-covers/image.jpg',
    'audio_url' => 'audio/track.mp3',
    'release_date' => '2024-02-13',
    'featured' => false,
    'duration' => '3:45'
])
exit
```

---

## Managing Genres

### Add a Genre:
```bash
php artisan tinker
Genre::create(['name' => 'Hip-Hop', 'slug' => 'hip-hop']);
exit
```

### View All Genres:
```bash
php artisan tinker
Genre::all();
exit
```

---

## Database Management

### Backup Database:
```bash
# MySQL
mysqldump -u root -p anmi_beatz > backup.sql

# PostgreSQL
pg_dump anmi_beatz > backup.sql

# SQLite
Copy-Item "backend/database.sqlite" "backup.sqlite"
```

### Restore Database:
```bash
# MySQL
mysql -u root -p anmi_beatz < backup.sql

# PostgreSQL
psql anmi_beatz < backup.sql

# SQLite
Copy-Item "backup.sqlite" "backend/database.sqlite"
```

### Reset Database:
```bash
php artisan migrate:fresh --seed
# WARNING: This deletes all data!
```

### Run Specific Migration:
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh
```

---

## Frontend Development

### Install New Package:
```bash
cd frontend
npm install package-name
```

### Update All Packages:
```bash
cd frontend
npm update
```

### Build for Production:
```bash
cd frontend
npm run build
# Creates optimized files in dist/ folder
```

### Fix Linting Issues:
```bash
cd frontend
npm run lint  # (if configured)
```

---

## Backend Development

### Create New Controller:
```bash
php artisan make:controller YourController -r
# -r flag adds resource methods (index, create, store, etc.)
```

### Create New Model:
```bash
php artisan make:model YourModel -m
# -m flag also creates migration
```

### Create New Migration:
```bash
php artisan make:migration create_your_table_name
```

### List All Routes:
```bash
php artisan route:list
```

### Clear Cache:
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

---

## API Endpoints Available

After setup, you can access these endpoints:

```
GET    /api/tracks              # Get all tracks
GET    /api/tracks/{id}         # Get specific track
POST   /api/tracks              # Create new track
PUT    /api/tracks/{id}         # Update track
DELETE /api/tracks/{id}         # Delete track

GET    /api/tracks/featured     # Get featured tracks only
GET    /api/tracks/genre/{name} # Get tracks by genre
```

### Test API with curl:
```bash
# Get all tracks
curl http://localhost:8000/api/tracks

# Get featured tracks
curl http://localhost:8000/api/tracks/featured

# Get specific track
curl http://localhost:8000/api/tracks/1

# Create new track
curl -X POST http://localhost:8000/api/tracks \
  -H "Content-Type: application/json" \
  -d '{"title":"New Song","artist":"Anmi Beatz","genre":"hip-hop",...}'
```

---

## Vue Component Patterns

### Fetch Data from API:
```vue
<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const tracks = ref([]);

onMounted(async () => {
    const response = await axios.get('http://localhost:8000/api/tracks');
    tracks.value = response.data;
});
</script>
```

### Emit Events to Parent:
```vue
<script setup>
const emit = defineEmits(['play', 'delete']);

const playTrack = (track) => {
    emit('play', track);
};
</script>
```

### Accept Props:
```vue
<script setup>
const props = defineProps({
    track: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <h3>{{ props.track.title }}</h3>
</template>
```

---

## Troubleshooting Quick Fixes

### Blank Page in Frontend:
```bash
# Clear browser cache (Ctrl+Shift+Delete)
# Check browser console for errors (F12)
# Restart frontend: npm run dev
```

### API Returns 404:
```bash
# Check Laravel routes: php artisan route:list
# Verify endpoint URL spelling
# Restart backend: php artisan serve
```

### Database Connection Error:
```bash
# Check .env credentials are correct
# Verify database exists
# Check database is running (MySQL/PostgreSQL)
```

### Port Already in Use:
```bash
# Find process using port 8000
netstat -ano | findstr :8000

# Kill it or use different port
php artisan serve --port=8001
```

### npm install issues:
```bash
# Clear npm cache
npm cache clean --force

# Remove node_modules
Remove-Item node_modules -Recurse

# Reinstall
npm install
```

---

## Development Tips

### Hot Reload During Development:
- Frontend: Already enabled with Vite
- Backend: No hot reload, restart with `php artisan serve`

### Debug Laravel:
```bash
php artisan tinker  # Interactive shell
dd($variable);      # Dump and die
\Log::info('message'); # Write to log
```

### Debug Vue:
```javascript
// In console
$0.__vue__           // Vue component instance
console.log(data)    // Standard logging
debugger             // Set breakpoint
```

### Monitor Database Changes:
```bash
php artisan tinker
Track::latest()->first() # Most recent track
Track::where('featured',true)->count() # Count featured
```

---

## Performance Monitoring

### Check Database Query Performance:
```bash
php artisan tinker
DB::enableQueryLog();
Track::all();
DB::getQueryLog();
```

### Monitor Laravel Logs:
```bash
# In new terminal
Get-Content -Path "backend/storage/logs/laravel.log" -Tail 50 -Wait
```

### Check Frontend Performance:
- Open DevTools (F12) → Performance tab
- Record and analyze

---

## Environment Variables

### Backend (.env):
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=anmi_beatz
```

### Frontend (.env.local):
```env
VITE_API_URL=http://localhost:8000/api
```

To use in Vue:
```javascript
import.meta.env.VITE_API_URL  // Access env variables
```

---

## File Locations Reference

```
📁 project-root/
├── 📁 backend/                  (Laravel API)
│   ├── app/
│   │   ├── Http/Controllers/   (API logic)
│   │   ├── Models/             (Database models)
│   ├── database/
│   │   ├── migrations/         (Database schema)
│   │   └── seeders/            (Sample data)
│   ├── routes/
│   │   └── api.php             (API routes)
│   ├── .env                    (Environment config)
│   └── public/                 (Served files)
│       ├── assets/             (Images)
│       └── audio/              (Audio files)
│
├── 📁 frontend/                 (Vue 3 + Tailwind)
│   ├── src/
│   │   ├── components/         (Vue components)
│   │   ├── pages/              (Page components)
│   │   ├── assets/             (Images/styles)
│   │   ├── App.vue             (Main app)
│   │   └── main.js             (Entry point)
│   ├── tailwind.config.js       (Tailwind config)
│   └── package.json            (npm dependencies)
│
└── 📄 SETUP_GUIDE.md           (This guide)
```

---

## Deployment

### Quick Production Build:
```bash
# Backend
cd backend
php artisan config:cache
php artisan route:cache
composer install --optimize-autoloader --no-dev

# Frontend
cd frontend
npm run build
# Upload dist/ folder to web server
```

---

**Last Updated**: February 13, 2026
**For detailed information, see SETUP_GUIDE.md**
