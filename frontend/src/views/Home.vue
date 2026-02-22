<template>
  <div class="anmi-page min-h-screen text-white">
    <main class="pt-20" :class="currentTrack ? 'pb-28 sm:pb-24' : ''">
      <section id="home" class="py-20 text-center scroll-mt-20">
        <img
          :src="logoUrl"
          alt="Anmi Beatz Logo"
          class="h-20 sm:h-28 md:h-32 mx-auto mb-6 sm:mb-8 object-contain logo-glow"
          @error="onLogoError"
        >
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-cyan-400 via-purple-400 to-blue-500 bg-clip-text text-transparent">Anmi Beatz</h1>
        <p class="text-base sm:text-lg md:text-xl text-gray-400 mb-8">Filipino Music Producer</p>
        <button
          @click="scrollToMusic"
          class="btn-primary"
        >
          <i class="fas fa-play"></i> Stream Now
        </button>
      </section>

      <section id="about" class="py-16 max-w-7xl mx-auto px-4 scroll-mt-20">
        <h2 class="section-title">About</h2>
        <p class="text-gray-400 text-lg max-w-3xl">Anmi Beatz is a Filipino music producer. Stream the latest beats and tracks.</p>
      </section>

      <section id="music" class="py-20 max-w-7xl mx-auto px-4 scroll-mt-20">
        <!-- Search & Filter -->
        <div class="search-filter-glass mb-12 p-6 rounded-2xl">
          <div class="flex flex-col md:flex-row gap-4 mb-4">
            <div class="flex-1 relative">
              <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-cyan-400/80"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by title, artist, or genre..."
                class="input-neon w-full pl-12 pr-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all"
              >
            </div>
            <div class="flex flex-wrap gap-2">
              <input
                v-model="filterGenre"
                list="genre-options"
                placeholder="Filter genre"
                class="input-neon px-4 py-3 rounded-xl bg-black/40 border border-purple-500/30 text-white focus:border-purple-400 focus:ring-2 focus:ring-purple-400/20 transition-all"
              >
              <datalist id="genre-options">
                <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
              </datalist>
              <input
                v-model="filterDateFrom"
                type="date"
                placeholder="From"
                class="input-neon px-4 py-3 rounded-xl bg-black/40 border border-blue-500/30 text-white focus:border-blue-400 transition-all"
              >
              <input
                v-model="filterDateTo"
                type="date"
                placeholder="To"
                class="input-neon px-4 py-3 rounded-xl bg-black/40 border border-blue-500/30 text-white focus:border-blue-400 transition-all"
              >
            </div>
          </div>
          <p class="text-sm text-gray-500">Showing {{ filteredTracks.length }} track(s)</p>
        </div>

        <div class="mb-16">
          <h2 class="section-title mb-8">Featured</h2>
          <div class="grid grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6">
            <TrackCard
              v-for="track in featuredTracks"
              :key="'f-' + track.id"
              :track="track"
              @play="playTrack"
            />
          </div>
        </div>

        <div>
          <h2 class="section-title mb-8">All Tracks</h2>
          <TransitionGroup name="track-list" tag="div" class="grid grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-4 lg:gap-6">
            <TrackCard
              v-for="track in filteredTracks"
              :key="track.id"
              :track="track"
              @play="playTrack"
            />
          </TransitionGroup>
          <p v-if="filteredTracks.length === 0" class="text-gray-500 py-12 text-center">No tracks match your search.</p>
        </div>
      </section>

      <section id="contact" class="py-16 max-w-7xl mx-auto px-4 scroll-mt-20">
        <h2 class="section-title">Contact</h2>
        <p class="text-gray-400 text-lg max-w-3xl">Get in touch for collaborations and bookings.</p>
      </section>
    </main>

    <Teleport to="body">
      <AudioPlayer
        v-if="currentTrack"
        :track="currentTrack"
        :play-token="playToken"
        @previous="previousTrack"
        @next="nextTrack"
        @close="currentTrack = null"
      />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';
import TrackCard from '../components/TrackCard.vue';
import AudioPlayer from '../components/AudioPlayer.vue';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

const logoUrl = ref('http://localhost:8000/assets/images/logos/Anmi%20Beatz%20Logo%201.png');
const fallbackLogo = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128"><rect fill="%2310b981" width="128" height="128" rx="8"/><text fill="white" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="48">AB</text></svg>');

function onLogoError(e) {
  e.target.src = fallbackLogo;
}

const tracks = ref([]);
const currentTrack = ref(null);
const playToken = ref(0);
const searchQuery = ref('');
const filterGenre = ref('');
const filterDateFrom = ref('');
const filterDateTo = ref('');

function splitGenres(rawGenre) {
  if (!rawGenre || typeof rawGenre !== 'string') return [];
  return rawGenre
    .split(',')
    .map((genre) => genre.trim())
    .filter(Boolean);
}

const genres = computed(() => {
  const set = new Set();
  tracks.value.forEach((track) => {
    splitGenres(track.genre).forEach((genre) => set.add(genre));
  });
  return Array.from(set).sort();
});

const featuredTracks = computed(() => tracks.value.filter(t => t.featured));

const filteredTracks = computed(() => {
  let list = tracks.value;
  const q = searchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter(t =>
      (t.title && t.title.toLowerCase().includes(q)) ||
      (t.artist && t.artist.toLowerCase().includes(q)) ||
      splitGenres(t.genre).some((genre) => genre.toLowerCase().includes(q))
    );
  }
  if (filterGenre.value) {
    const selectedGenre = filterGenre.value.trim().toLowerCase();
    list = list.filter((track) => splitGenres(track.genre).some((genre) => genre.toLowerCase().includes(selectedGenre)));
  }
  if (filterDateFrom.value) {
    list = list.filter(t => t.release_date && t.release_date >= filterDateFrom.value);
  }
  if (filterDateTo.value) {
    list = list.filter(t => t.release_date && t.release_date <= filterDateTo.value);
  }
  return list;
});

onMounted(fetchTracks);

async function fetchTracks() {
  try {
    const response = await axios.get(`${API_URL}/tracks`);
    const data = response.data;
    tracks.value = Array.isArray(data) ? data : (data?.data ?? []);
  } catch (error) {
    console.error('Error fetching tracks:', error);
    tracks.value = [];
  }
}

async function playTrack(track) {
  if (!track) return;
  playToken.value += 1;
  if (currentTrack.value?.id === track.id) {
    currentTrack.value = null;
    await nextTick();
  }
  currentTrack.value = { ...track };
}

function nextTrack() {
  const list = filteredTracks.value;
  const idx = list.findIndex(t => t.id === currentTrack.value?.id);
  if (idx >= 0 && idx < list.length - 1) {
    currentTrack.value = { ...list[idx + 1] };
  }
}

function previousTrack() {
  const list = filteredTracks.value;
  const idx = list.findIndex(t => t.id === currentTrack.value?.id);
  if (idx > 0) {
    currentTrack.value = { ...list[idx - 1] };
  }
}

function scrollToMusic() {
  document.getElementById('music')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<style scoped>
.section-title {
  font-size: 2.25rem;
  font-weight: 700;
  background: linear-gradient(to right, rgba(34, 211, 238, 0.9), rgba(192, 132, 252, 0.9));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.search-filter-glass {
  background: linear-gradient(135deg, rgba(6, 182, 212, 0.06) 0%, rgba(168, 85, 247, 0.06) 100%);
  border: 1px solid rgba(6, 182, 212, 0.15);
  box-shadow: 0 0 30px rgba(6, 182, 212, 0.08);
}
.input-neon:focus {
  box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
}
.btn-primary {
  padding: 0.75rem 2rem;
  border-radius: 9999px;
  font-weight: 700;
  transition: all 0.3s;
  background: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 100%);
  box-shadow: 0 0 25px rgba(6, 182, 212, 0.4);
}
.btn-primary:hover {
  box-shadow: 0 0 35px rgba(139, 92, 246, 0.5);
  transform: translateY(-2px);
}
.logo-glow {
  filter: drop-shadow(0 0 20px rgba(6, 182, 212, 0.3));
}
.track-list-move,
.track-list-enter-active,
.track-list-leave-active {
  transition: all 0.35s ease;
}
.track-list-enter-from,
.track-list-leave-to {
  opacity: 0;
  transform: translateY(12px);
}
.track-list-leave-active {
  position: absolute;
}
</style>
