# Complete Setup Guide: Vue + Tailwind Frontend + Laravel Backend
## For AnmiBeatzMusic (anmibeatz.com)

---

## Prerequisites

Install these on your new laptop:

### Required Software
1. **PHP 8.2+** - https://www.php.net/downloads
   - Add PHP to your system PATH
   - Verify: `php --version`

2. **Composer** - https://getcomposer.org/download
   - PHP dependency manager
   - Verify: `composer --version`

3. **Node.js 18+** - https://nodejs.org/
   - Includes npm
   - Verify: `node --version` and `npm --version`

4. **Git** - https://git-scm.com/download/win
   - Verify: `git --version`

5. **Database** (choose one):
   - **MySQL 8.0+** - https://dev.mysql.com/downloads/mysql/
   - **PostgreSQL 14+** - https://www.postgresql.org/download/windows/
   - Or use SQLite (no installation needed)

6. **Code Editor**: VS Code (recommended)
   - Install Laravel extension pack
   - Install Vue 3 Support extension

---

## Project Structure (Recommended)

```
AnmiBeatzMusic/
├── backend/                 (Laravel API)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── storage/
│   └── .env
│
├── frontend/                (Vue 3 + Tailwind)
│   ├── src/
│   ├── components/
│   ├── pages/
│   ├── assets/
│   └── public/
│
├── audio/                   (Audio files)
├── assets/                  (Images, etc.)
└── README.md
```

---

## Step 1: Backend Setup (Laravel)

### 1.1 Create Laravel Project
```bash
# Open PowerShell in your workspace directory
cd "d:\My Codes\AnmiBeatzMusic"

# Create new Laravel project
composer create-project laravel/laravel backend

# Navigate to backend
cd backend
```

### 1.2 Configure Environment
Edit `backend/.env`:
```env
APP_NAME="Anmi Beatz"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (choose based on your DB)
# For MySQL:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=anmi_beatz
DB_USERNAME=root
DB_PASSWORD=your_password

# For PostgreSQL:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=anmi_beatz
DB_USERNAME=postgres
DB_PASSWORD=your_password

# For SQLite (simpler for development):
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 1.3 Generate Application Key
```bash
php artisan key:generate
```

### 1.4 Create Database
```bash
# For MySQL - Open MySQL command line:
mysql -u root -p
CREATE DATABASE anmi_beatz;
EXIT;

# For PostgreSQL - Open PostgreSQL command line:
psql -U postgres
CREATE DATABASE anmi_beatz;
\q

# For SQLite - will be created automatically when migrations run
```

### 1.5 Install Dependencies & Migrate
```bash
cd backend

# Install PHP dependencies
composer install

# Run migrations (creates tables)
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

---

## Step 2: Create Laravel Models, Migrations & Controllers

### 2.1 Create Music Track Model & Migration
```bash
cd backend

# Creates model + migration + controller
php artisan make:model Track -mcr
php artisan make:model Genre -mcr
php artisan make:model User -mcr
```

### 2.2 Edit Migration Files
Edit `backend/database/migrations/xxxx_xx_xx_create_tracks_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist')->default('Anmi Beatz');
            $table->text('description')->nullable();
            $table->string('genre');
            $table->string('image_url');
            $table->string('audio_url');
            $table->string('youtube_link')->nullable();
            $table->string('spotify_link')->nullable();
            $table->integer('plays')->default(0);
            $table->string('duration'); // e.g., "3:45"
            $table->date('release_date');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
```

Edit `backend/database/migrations/xxxx_xx_xx_create_genres_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
```

### 2.3 Run Migrations
```bash
cd backend
php artisan migrate
```

### 2.4 Create Models
Edit `backend/app/Models/Track.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'description',
        'genre',
        'image_url',
        'audio_url',
        'youtube_link',
        'spotify_link',
        'plays',
        'duration',
        'release_date',
        'featured'
    ];

    protected $casts = [
        'release_date' => 'date',
        'featured' => 'boolean',
    ];
}
```

Edit `backend/app/Models/Genre.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = ['name', 'slug'];
}
```

### 2.5 Create API Controllers
Edit `backend/app/Http/Controllers/TrackController.php`:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        return response()->json(Track::all());
    }

    public function featured()
    {
        return response()->json(Track::where('featured', true)->get());
    }

    public function byGenre($genre)
    {
        return response()->json(Track::where('genre', $genre)->get());
    }

    public function show($id)
    {
        return response()->json(Track::findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'artist' => 'required|string',
            'genre' => 'required|string',
            'image_url' => 'required|url',
            'audio_url' => 'required|url',
            'release_date' => 'required|date',
            'duration' => 'required|string',
        ]);

        return response()->json(Track::create($validated), 201);
    }

    public function update(Request $request, $id)
    {
        $track = Track::findOrFail($id);
        $track->update($request->all());
        return response()->json($track);
    }

    public function destroy($id)
    {
        Track::destroy($id);
        return response()->json(['message' => 'Track deleted']);
    }
}
```

### 2.6 Setup API Routes
Edit `backend/routes/api.php`:
```php
<?php

use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tracks', TrackController::class);
Route::get('tracks/featured', [TrackController::class, 'featured']);
Route::get('tracks/genre/{genre}', [TrackController::class, 'byGenre']);
```

### 2.7 Enable CORS for Frontend
Edit `backend/config/cors.php`:
```php
'allowed_origins' => ['http://localhost:5173', 'http://localhost:3000'],
'allowed_origins_patterns' => ['localhost*'],
```

### 2.8 Seed Database with Your Music Data
Create seeder:
```bash
php artisan make:seeder TrackSeeder
```

Edit `backend/database/seeders/TrackSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        // Convert your existing music-data.json to database
        $tracks = [
            [
                'title' => 'Dreams & Nightmares',
                'artist' => 'Anmi Beatz',
                'genre' => 'hip-hop',
                'image_url' => 'assets/images/music-covers/Title-1.jpg',
                'audio_url' => 'audio/dreams-and-nightmares.mp3',
                'youtube_link' => 'https://youtube.com/watch?v=YOUR_VIDEO_ID',
                'spotify_link' => 'https://open.spotify.com/track/YOUR_TRACK_ID',
                'release_date' => '2024-01-15',
                'featured' => true,
                'plays' => 125000,
                'duration' => '3:45'
            ],
            // ... add rest of your tracks from music-data.json
        ];

        foreach ($tracks as $track) {
            Track::create($track);
        }
    }
}
```

Run seeder:
```bash
php artisan db:seed --class=TrackSeeder
```

---

## Step 3: Frontend Setup (Vue 3 + Tailwind)

### 3.1 Create Vue Project
```bash
# Go back to project root
cd ..

# Create Vue 3 project with Vite
npm create vite@latest frontend -- --template vue

# Navigate to frontend
cd frontend

# Install dependencies
npm install

# Install Tailwind CSS v4 with PostCSS plugin
npm install -D tailwindcss @tailwindcss/postcss

# Create configuration files (already in Step 3.2)
# tailwind.config.js and postcss.config.js will be configured in Step 3.2

# Install Axios for API calls
npm install axios
```

### 3.2 Configure Tailwind CSS v4

Edit `frontend/postcss.config.js`:
```js
export default {
  plugins: {
    '@tailwindcss/postcss': {},
  },
}
```

Edit `frontend/src/style.css`:
```css
@import "tailwindcss";

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

* {
    font-family: 'Poppins', sans-serif;
}
```

Edit `frontend/tailwind.config.js`:
```js
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1e1e1e',
        secondary: '#00ff88',
        accent: '#ff0080',
      },
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
```

### 3.3 Create Vue Components
Create `frontend/src/components/Navigation.vue`:
```vue
<template>
  <nav class="fixed w-full top-0 z-50 bg-black bg-opacity-90 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <img src="/assets/images/logos/Anmi Beatz Logo 1.png" alt="Logo" class="h-10">
        <span class="text-white font-bold text-xl">Anmi Beatz</span>
      </div>
      <ul class="hidden md:flex gap-8 text-white">
        <li><a href="#home" class="hover:text-green-400 transition">Home</a></li>
        <li><a href="#about" class="hover:text-green-400 transition">About</a></li>
        <li><a href="#music" class="hover:text-green-400 transition">Music</a></li>
        <li><a href="#contact" class="hover:text-green-400 transition">Contact</a></li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
</script>
```

Create `frontend/src/components/TrackCard.vue`:
```vue
<template>
  <div class="bg-gray-900 rounded-lg overflow-hidden hover:shadow-xl transition cursor-pointer">
    <img :src="track.image_url" :alt="track.title" class="w-full h-48 object-cover">
    <div class="p-4">
      <h3 class="font-bold text-white truncate">{{ track.title }}</h3>
      <p class="text-gray-400 text-sm">{{ track.artist }}</p>
      <p class="text-gray-500 text-xs mt-2">{{ track.genre }} • {{ track.duration }}</p>
      <div class="flex gap-2 mt-4">
        <button 
          @click="playTrack"
          class="flex-1 bg-green-500 text-black font-bold py-2 rounded hover:bg-green-400 transition"
        >
          <i class="fas fa-play"></i> Play
        </button>
        <a :href="track.spotify_link" target="_blank" class="flex-1 bg-gray-800 text-white py-2 rounded hover:bg-gray-700 transition text-center">
          <i class="fab fa-spotify"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  track: Object
});

const emit = defineEmits(['play']);

const playTrack = () => {
  emit('play', props.track);
};
</script>
```

Create `frontend/src/components/AudioPlayer.vue`:
```vue
<template>
  <div v-if="currentTrack" class="fixed bottom-0 w-full bg-black border-t border-gray-800 text-white">
    <audio 
      ref="audioElement" 
      @timeupdate="updateProgress"
      @ended="nextTrack"
      @loadedmetadata="updateDuration"
    ></audio>
    
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-4">
      <img :src="currentTrack.image_url" :alt="currentTrack.title" class="h-16 w-16 rounded">
      
      <div class="flex-1 min-w-0">
        <p class="font-bold truncate">{{ currentTrack.title }}</p>
        <p class="text-gray-400 text-sm truncate">{{ currentTrack.artist }}</p>
      </div>

      <div class="w-64">
        <div 
          @click="seekTo"
          class="h-1 bg-gray-700 rounded cursor-pointer hover:bg-gray-600"
        >
          <div 
            class="h-full bg-green-500 rounded" 
            :style="{ width: progress + '%' }"
          ></div>
        </div>
        <div class="text-xs text-gray-400 flex justify-between mt-1">
          <span>{{ currentTime }}</span>
          <span>{{ duration }}</span>
        </div>
      </div>

      <button @click="togglePlayPause" class="text-2xl">
        <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
      </button>
      
      <button @click="nextTrack" class="text-xl">
        <i class="fas fa-forward"></i>
      </button>
      
      <button @click="closePlayer" class="text-xl">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  track: Object
});

const emit = defineEmits(['next', 'close']);

const audioElement = ref(null);
const isPlaying = ref(false);
const progress = ref(0);
const currentTime = ref('0:00');
const duration = ref('0:00');

const currentTrack = ref(props.track);

const togglePlayPause = () => {
  if (isPlaying.value) {
    audioElement.value.pause();
  } else {
    audioElement.value.play();
  }
  isPlaying.value = !isPlaying.value;
};

const updateProgress = () => {
  if (audioElement.value.duration) {
    progress.value = (audioElement.value.currentTime / audioElement.value.duration) * 100;
    currentTime.value = formatTime(audioElement.value.currentTime);
  }
};

const updateDuration = () => {
  duration.value = formatTime(audioElement.value.duration);
};

const seekTo = (e) => {
  const rect = e.currentTarget.getBoundingClientRect();
  const percent = (e.clientX - rect.left) / rect.width;
  audioElement.value.currentTime = percent * audioElement.value.duration;
};

const nextTrack = () => {
  emit('next');
};

const closePlayer = () => {
  emit('close');
};

const formatTime = (seconds) => {
  if (!seconds) return '0:00';
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};
</script>
```

### 3.4 Create Main App Component
Edit `frontend/src/App.vue`:
```vue
<template>
  <div class="bg-gradient-to-b from-black to-gray-900 min-h-screen text-white">
    <Navigation />
    
    <main class="pt-20">
      <!-- Hero Section -->
      <section class="py-20 text-center">
        <img src="/assets/images/logos/Anmi Beatz Logo 1.png" alt="Logo" class="h-32 mx-auto mb-8">
        <h1 class="text-5xl font-bold mb-4">Anmi Beatz</h1>
        <p class="text-xl text-gray-400 mb-8">Music Producer</p>
        <button 
          @click="scrollToMusic"
          class="bg-green-500 hover:bg-green-400 text-black font-bold py-3 px-8 rounded-full transition"
        >
          <i class="fas fa-play"></i> Stream Now
        </button>
      </section>

      <!-- Music Section -->
      <section id="music" class="py-20 max-w-7xl mx-auto px-4">
        <!-- Featured Tracks -->
        <div class="mb-16">
          <h2 class="text-4xl font-bold mb-8">Featured</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <TrackCard 
              v-for="track in featuredTracks" 
              :key="track.id" 
              :track="track"
              @play="playTrack"
            />
          </div>
        </div>

        <!-- All Tracks with Filtering -->
        <div>
          <h2 class="text-4xl font-bold mb-4">All Tracks</h2>
          <div class="flex gap-2 mb-8 flex-wrap">
            <button 
              v-for="genre in genres" 
              :key="genre"
              @click="selectedGenre = genre"
              :class="[
                'px-4 py-2 rounded-full transition',
                selectedGenre === genre 
                  ? 'bg-green-500 text-black font-bold' 
                  : 'bg-gray-800 text-white hover:bg-gray-700'
              ]"
            >
              {{ genre }}
            </button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <TrackCard 
              v-for="track in filteredTracks" 
              :key="track.id" 
              :track="track"
              @play="playTrack"
            />
          </div>
        </div>
      </section>
    </main>

    <AudioPlayer 
      v-if="currentTrack"
      :track="currentTrack"
      @next="nextTrack"
      @close="currentTrack = null"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Navigation from './components/Navigation.vue';
import TrackCard from './components/TrackCard.vue';
import AudioPlayer from './components/AudioPlayer.vue';

const API_URL = 'http://localhost:8000/api';

const tracks = ref([]);
const currentTrack = ref(null);
const selectedGenre = ref('all');

const genres = computed(() => {
  const genreSet = new Set(['all']);
  tracks.value.forEach(track => genreSet.add(track.genre));
  return Array.from(genreSet);
});

const featuredTracks = computed(() => {
  return tracks.value.filter(t => t.featured);
});

const filteredTracks = computed(() => {
  if (selectedGenre.value === 'all') {
    return tracks.value;
  }
  return tracks.value.filter(t => t.genre === selectedGenre.value);
});

onMounted(async () => {
  try {
    const response = await axios.get(`${API_URL}/tracks`);
    tracks.value = response.data;
  } catch (error) {
    console.error('Error fetching tracks:', error);
  }
});

const playTrack = (track) => {
  currentTrack.value = track;
};

const nextTrack = () => {
  const filteredList = filteredTracks.value;
  const currentIndex = filteredList.findIndex(t => t.id === currentTrack.value.id);
  if (currentIndex < filteredList.length - 1) {
    currentTrack.value = filteredList[currentIndex + 1];
  }
};

const scrollToMusic = () => {
  const musicSection = document.getElementById('music');
  if (musicSection) {
    musicSection.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>
```

### 3.5 Install Font Awesome Icons
```bash
cd frontend
npm install @fortawesome/fontawesome-free
```

Edit `frontend/src/main.js`:
```js
import { createApp } from 'vue'
import App from './App.vue'
import '@fortawesome/fontawesome-free/css/all.min.css'
import './style.css'

createApp(App).mount('#app')
```

---

## Step 4: Running Both Servers

### Terminal 1: Start Laravel Backend
```bash
cd backend
php artisan serve
# Runs on http://localhost:8000
```

### Terminal 2: Start Vue Frontend
```bash
cd frontend
npm run dev
# Runs on http://localhost:5173
```

---

## Step 5: Migrate Your Audio & Image Files

```bash
# Copy your audio files to public directory
cp -Path "audio\*" -Destination "backend\public\audio" -Recurse

# Copy your images
cp -Path "assets\*" -Destination "backend\public\assets" -Recurse
```

Update image/audio URLs in database to point to public paths.

---

## Step 6: Build for Production

### Build Frontend
```bash
cd frontend
npm run build
# Creates dist/ folder
```

### Prepare Laravel for Production
```bash
cd backend

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set correct env
APP_ENV=production
APP_DEBUG=false
```

---

## Database Backup & Restore

### Export Database
```bash
# MySQL
mysqldump -u root -p anmi_beatz > backup.sql

# PostgreSQL
pg_dump anmi_beatz > backup.sql
```

### Import Database
```bash
# MySQL
mysql -u root -p anmi_beatz < backup.sql

# PostgreSQL
psql anmi_beatz < backup.sql
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `npx tailwindcss init -p` fails on Windows | This is a known npm issue. Config files are already created. If not, manually create `tailwind.config.js` and `postcss.config.js` (see Step 3.2) |
| CORS errors | Check `backend/config/cors.php` has frontend URL |
| Images not loading | Ensure files in `public/` folder and URLs match |
| Audio not playing | Check file paths in database and public folder |
| Port already in use | Change port: `php artisan serve --port=8001` |
| npm modules missing | Run `npm install` in frontend directory |
| Database connection error | Check `.env` credentials match DB settings |

---

## Deployment Options

1. **Shared Hosting**: Use Laravel's built-in server or Nginx
2. **VPS (DigitalOcean, Linode)**: Standard Laravel/Vue deployment
3. **Vercel**: Deploy frontend only
4. **Heroku**: Deploy both frontend and backend
5. **Local Network**: Use `ipconfig` to get your IP, access from other devices

---

## Additional Resources

- Laravel Docs: https://laravel.com/docs
- Vue 3 Docs: https://vuejs.org/
- Tailwind CSS: https://tailwindcss.com/
- Axios: https://axios-http.com/

---

## Quick Start Script (Windows PowerShell)

```powershell
# Save as setup.ps1 and run:
# Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
# .\setup.ps1

# Create backend
composer create-project laravel/laravel backend
cd backend
php artisan key:generate
php artisan migrate
cd ..

# Create frontend
npm create vite@latest frontend -- --template vue
cd frontend
npm install
npm install -D tailwindcss @tailwindcss/postcss axios @fortawesome/fontawesome-free
npm run dev
```

---

**Last Updated**: February 13, 2026
