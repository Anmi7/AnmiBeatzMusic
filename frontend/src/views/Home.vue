<template>
  <div class="anmi-page min-h-screen text-white">
    <main class="pt-20" :class="currentTrack ? 'pb-28 sm:pb-24' : ''">
      <section id="home" class="hero-section text-center scroll-mt-20">
        <div class="hero-content">
          <img
            :src="logoUrl"
            alt="Anmi Beatz Logo"
            class="hero-logo mx-auto mb-6 sm:mb-8 object-contain logo-glow"
            @error="onLogoError"
          >
          <h1 class="hero-title font-bold mb-4 bg-gradient-to-r from-zinc-100 via-neutral-50 to-stone-300 bg-clip-text text-transparent">Anmi Beatz</h1>
          <p class="hero-subtitle text-gray-400 mb-8">Filipino Music Producer</p>
          <button
            @click="scrollToMusic"
            class="btn-primary hero-cta stream-now-btn"
          >
            <span class="stream-now-content">
              <i class="fas fa-play"></i>
              Stream Now
            </span>
          </button>
        </div>
      </section>

      <section id="about" class="py-16 w-full px-4 sm:px-8 xl:px-12 scroll-mt-20">
        <h2 class="section-title">About</h2>
        <p class="text-gray-400 text-lg max-w-4xl">Anmi Beatz is a Filipino music producer. Stream the latest beats and tracks.</p>
      </section>

      <section id="music" class="py-20 w-full px-4 sm:px-8 xl:px-12 scroll-mt-20">
        <!-- Search & Filter -->
        <div class="search-filter-glass mb-12 p-6 rounded-2xl">
          <p v-if="loadError" class="mb-4 text-sm text-red-400">{{ loadError }}</p>
          <div class="flex flex-col md:flex-row gap-4 mb-4">
            <div class="flex-1 relative">
              <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-cyan-400/80"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by title or artist..."
                class="input-neon w-full pl-12 pr-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 transition-all"
              >
            </div>
            <div class="flex flex-wrap gap-2 items-center">
              <div class="filter-select-wrap">
                <select
                  v-model="filterGenre"
                  class="filter-select input-neon rounded-xl bg-black/40 border border-sky-500/30 text-white focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20 transition-all"
                  :class="{ 'with-clear': !!filterGenre }"
                >
                  <option value="">Genre</option>
                  <option v-for="g in genres" :key="g" :value="g">{{ g }}</option>
                </select>
                <button
                  v-if="filterGenre"
                  type="button"
                  @click="filterGenre = ''"
                  class="filter-clear-btn"
                  title="Clear genre"
                  aria-label="Clear genre"
                >
                  <i class="fas fa-times"></i>
                </button>
                <span class="filter-chevron" aria-hidden="true">
                  <i class="fas fa-chevron-down"></i>
                </span>
              </div>
              <div class="filter-select-wrap">
                <select
                  v-model="sortBy"
                  class="filter-select input-neon rounded-xl bg-black/40 border border-sky-500/30 text-white focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20 transition-all"
                  :class="{ 'with-clear': !!sortBy }"
                >
                  <option value="">Sort by</option>
                  <option value="artist">Artist Name</option>
                  <option value="title">Title</option>
                  <option value="genre">Genre</option>
                  <option value="release_date">Date</option>
                </select>
                <button
                  v-if="sortBy"
                  type="button"
                  @click="sortBy = ''"
                  class="filter-clear-btn"
                  title="Clear sort"
                  aria-label="Clear sort"
                >
                  <i class="fas fa-times"></i>
                </button>
                <span class="filter-chevron" aria-hidden="true">
                  <i class="fas fa-chevron-down"></i>
                </span>
              </div>
              <button
                type="button"
                @click="toggleSortDirection"
                class="h-[50px] w-[50px] rounded-xl border border-blue-500/40 bg-black/40 text-cyan-300 hover:text-cyan-200 hover:border-cyan-300/70 transition"
                :title="sortDirection === 'asc' ? 'Ascending' : 'Descending'"
                aria-label="Toggle sort direction"
              >
                <i :class="sortDirection === 'asc' ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
              </button>
            </div>
          </div>
          <p class="text-sm text-gray-500">Showing {{ filteredTracks.length }} track(s)</p>
        </div>

        <div class="mb-16">
          <h2 class="section-title mb-8">Featured</h2>
          <div class="featured-grid">
            <TrackCard
              v-for="track in featuredTracks"
              :key="'f-' + track.id"
              :track="track"
              @play="playTrack"
            />
          </div>
        </div>

        <div>
          <div class="flex items-center gap-3 mb-8">
            <h2 class="section-title mb-0">All Tracks</h2>
            <div class="view-toggle" role="group" aria-label="View mode">
              <button
                type="button"
                class="view-btn"
                :class="{ active: viewMode === 'tiles' }"
                @click="viewMode = 'tiles'"
                title="Tiles view"
                aria-label="Tiles view"
              >
                <span class="tile-icon" aria-hidden="true">
                  <span></span>
                  <span></span>
                  <span></span>
                  <span></span>
                </span>
              </button>
              <button
                type="button"
                class="view-btn"
                :class="{ active: viewMode === 'list' }"
                @click="viewMode = 'list'"
                title="List view"
                aria-label="List view"
              >
                <i class="fas fa-list"></i>
              </button>
            </div>
          </div>

          <TransitionGroup v-if="viewMode === 'tiles'" name="track-list" tag="div" class="all-tracks-grid">
            <TrackCard
              v-for="track in filteredTracks"
              :key="track.id"
              :track="track"
              @play="playTrack"
            />
          </TransitionGroup>
          <TransitionGroup v-else name="track-list" tag="div" class="all-tracks-list">
            <div
              v-for="track in filteredTracks"
              :key="track.id"
              class="all-track-row"
              @click="playTrack(track)"
            >
              <img
                :src="getTrackImageUrl(track?.image_url)"
                :alt="track?.title || 'Track cover'"
                class="h-12 w-12 rounded-md bg-slate-900 object-cover shrink-0"
                @error="onTrackImageError"
              >
              <div class="min-w-0 flex-1">
                <p class="font-bold text-sm text-white truncate">{{ track?.title || 'Unknown Track' }}</p>
                <p class="text-xs text-gray-300 truncate">{{ track?.artist || 'Unknown Artist' }}</p>
              </div>
              <p class="hidden sm:block text-xs text-gray-500 truncate max-w-[28%]">{{ track?.genre || '' }}</p>
              <p class="hidden sm:block text-xs text-gray-500 w-12 text-right">{{ track?.duration || '--:--' }}</p>
              <button
                type="button"
                class="row-play-btn"
                @click.stop="playTrack(track)"
                title="Play track"
                aria-label="Play track"
              >
                <i class="fas fa-play"></i>
              </button>
            </div>
          </TransitionGroup>
          <p v-if="filteredTracks.length === 0" class="text-gray-500 py-12 text-center">No tracks match your search.</p>
        </div>
      </section>

      <section id="contact" class="py-16 w-full px-4 sm:px-8 xl:px-12 scroll-mt-20">
        <h2 class="section-title">Contact</h2>
        <p class="text-gray-400 text-lg max-w-4xl">Get in touch for collaborations and bookings.</p>
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
const viewMode = ref('tiles');
const loadError = ref('');
const searchQuery = ref('');
const filterGenre = ref('');
const sortBy = ref('');
const sortDirection = ref('desc');

const API_BASE = import.meta.env.VITE_API_URL?.replace(/\/api\/?$/, '') || 'http://localhost:8000';
const trackPlaceholderImage =
  'data:image/svg+xml,' +
  encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"><rect fill="%231e293b" width="96" height="96"/><text fill="%2394a3b8" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="10">No cover</text></svg>'
  );
const sortByKey = computed(() => sortBy.value || 'release_date');

function dateToTime(value) {
  if (!value) return 0;
  return new Date(String(value).slice(0, 10)).getTime() || 0;
}

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
      (t.artist && t.artist.toLowerCase().includes(q))
    );
  }
  if (filterGenre.value) {
    const selectedGenre = filterGenre.value.trim().toLowerCase();
    list = list.filter((track) => splitGenres(track.genre).some((genre) => genre.toLowerCase().includes(selectedGenre)));
  }

  const direction = sortDirection.value === 'asc' ? 1 : -1;
  const sorted = [...list].sort((a, b) => {
    if (sortByKey.value === 'artist') {
      return direction * (a.artist || '').localeCompare(b.artist || '', undefined, { sensitivity: 'base' });
    }
    if (sortByKey.value === 'title') {
      return direction * (a.title || '').localeCompare(b.title || '', undefined, { sensitivity: 'base' });
    }
    if (sortByKey.value === 'genre') {
      return direction * (a.genre || '').localeCompare(b.genre || '', undefined, { sensitivity: 'base' });
    }

    const aDate = dateToTime(a.release_date);
    const bDate = dateToTime(b.release_date);
    return direction * (aDate - bDate);
  });

  return sorted;
});

function toggleSortDirection() {
  sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
}

function getTrackImageUrl(url) {
  if (!url) return trackPlaceholderImage;
  if (typeof url === 'string' && url.startsWith('http')) return url;
  return `${API_BASE}${url.startsWith('/') ? url : '/' + url}`;
}

function onTrackImageError(event) {
  event.target.src = trackPlaceholderImage;
}

onMounted(fetchTracks);

async function fetchTracks() {
  loadError.value = '';
  try {
    const response = await axios.get(`${API_URL}/tracks`);
    const data = response.data;
    tracks.value = Array.isArray(data) ? data : (data?.data ?? []);
  } catch (error) {
    console.error('Error fetching tracks:', error);
    tracks.value = [];
    loadError.value = error?.response?.data?.message || 'Failed to load tracks from backend.';
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
  background: linear-gradient(to right, rgba(34, 211, 238, 0.9), rgba(125, 211, 252, 0.9));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.hero-section {
  min-height: calc(100vh - 5rem);
  min-height: calc(100svh - 5rem);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}
.hero-content {
  width: 100%;
  max-width: 72rem;
  margin: 0 auto;
}
.hero-logo {
  height: clamp(6rem, 11vw, 11rem);
}
.hero-title {
  font-size: clamp(2.5rem, 5.4vw, 5.5rem);
  line-height: 1.05;
}
.hero-subtitle {
  font-size: clamp(1.1rem, 2vw, 1.8rem);
}
.hero-cta {
  font-size: clamp(1rem, 1.5vw, 1.35rem);
  padding: clamp(0.8rem, 1.3vw, 1.1rem) clamp(2rem, 3.8vw, 3.3rem);
}
.search-filter-glass {
  background: linear-gradient(135deg, rgba(6, 182, 212, 0.06) 0%, rgba(96, 165, 250, 0.08) 100%);
  border: 1px solid rgba(6, 182, 212, 0.15);
  box-shadow: 0 0 30px rgba(6, 182, 212, 0.08);
}
.filter-select-wrap {
  position: relative;
}
.filter-select {
  min-width: 170px;
  height: 50px;
  padding: 0.7rem 2.1rem 0.7rem 1rem;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}
.filter-select.with-clear {
  padding-right: 4.1rem;
}
.filter-select::-ms-expand {
  display: none;
}
.filter-clear-btn {
  position: absolute;
  top: 50%;
  right: 1.9rem;
  transform: translateY(-50%);
  height: 1.35rem;
  width: 1.35rem;
  border-radius: 9999px;
  border: 1px solid rgba(56, 189, 248, 0.55);
  background: rgba(2, 12, 26, 0.82);
  color: #67e8f9;
  font-size: 0.56rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
}
.filter-clear-btn:hover {
  border-color: rgba(125, 211, 252, 0.8);
  color: #cffafe;
  background: rgba(8, 27, 47, 0.95);
}
.filter-chevron {
  position: absolute;
  top: 50%;
  right: 0.78rem;
  transform: translateY(-50%);
  color: rgba(125, 211, 252, 0.9);
  font-size: 0.74rem;
  pointer-events: none;
}
.input-neon:focus {
  box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
}
.btn-primary {
  padding: 0.75rem 2rem;
  border-radius: 9999px;
  font-weight: 700;
  transition: all 0.3s;
  background: linear-gradient(135deg, #06b6d4 0%, #60a5fa 100%);
  box-shadow: 0 0 25px rgba(6, 182, 212, 0.4);
}
.btn-primary:hover {
  box-shadow: 0 0 35px rgba(96, 165, 250, 0.5);
  transform: translateY(-2px);
}
.stream-now-btn {
  position: relative;
  overflow: hidden;
  isolation: isolate;
  border: 1px solid rgba(125, 211, 252, 0.45);
}
.stream-now-content {
  position: relative;
  z-index: 2;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}
.stream-now-content i {
  transition: transform 0.28s ease;
}
.stream-now-btn::before {
  content: "";
  position: absolute;
  top: -130%;
  left: -30%;
  width: 42%;
  height: 360%;
  background: linear-gradient(
    115deg,
    rgba(255, 255, 255, 0) 25%,
    rgba(186, 230, 253, 0.82) 50%,
    rgba(56, 189, 248, 0) 75%
  );
  transform: translateX(-240%) skewX(-20deg);
  transition: transform 0.62s cubic-bezier(0.22, 0.61, 0.36, 1);
  pointer-events: none;
}
.stream-now-btn::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(140deg, rgba(34, 211, 238, 0.2), rgba(125, 211, 252, 0.22));
  opacity: 0;
  z-index: 1;
  transition: opacity 0.28s ease;
  pointer-events: none;
}
.stream-now-btn:hover::before {
  transform: translateX(420%) skewX(-20deg);
}
.stream-now-btn:hover::after {
  opacity: 1;
}
.stream-now-btn:hover {
  box-shadow: 0 0 18px rgba(34, 211, 238, 0.55), 0 0 40px rgba(56, 189, 248, 0.5);
}
.stream-now-btn:hover .stream-now-content i {
  transform: translateX(2px);
}
.stream-now-btn:active {
  transform: translateY(-1px) scale(0.99);
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
.featured-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}
.all-tracks-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.65rem;
}
.all-tracks-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
}
.all-track-row {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.55rem 0.65rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(56, 189, 248, 0.2);
  background: linear-gradient(180deg, rgba(8, 20, 32, 0.5) 0%, rgba(8, 23, 42, 0.6) 100%);
  transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
  cursor: pointer;
}
.all-track-row:hover {
  border-color: rgba(34, 211, 238, 0.45);
  background: linear-gradient(180deg, rgba(8, 20, 32, 0.62) 0%, rgba(8, 23, 42, 0.72) 100%);
  transform: translateY(-1px);
}
.row-play-btn {
  height: 2rem;
  width: 2rem;
  border-radius: 9999px;
  border: 1px solid rgba(34, 211, 238, 0.35);
  color: #67e8f9;
  background: rgba(2, 6, 23, 0.45);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}
.row-play-btn:hover {
  border-color: rgba(125, 211, 252, 0.75);
  color: #a5f3fc;
  transform: scale(1.04);
}
.view-toggle {
  display: inline-flex;
  border: 1px solid rgba(125, 211, 252, 0.5);
  border-radius: 0.9rem;
  overflow: hidden;
  background: rgba(2, 6, 23, 0.45);
}
.view-btn {
  height: 2.35rem;
  width: 2.6rem;
  color: #cbd5e1;
  background: transparent;
  border: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease, background-color 0.2s ease;
}
.tile-icon {
  display: grid;
  grid-template-columns: repeat(2, 0.36rem);
  grid-template-rows: repeat(2, 0.36rem);
  gap: 0.12rem;
}
.tile-icon > span {
  width: 0.36rem;
  height: 0.36rem;
  border: 1.4px solid currentColor;
  border-radius: 0.06rem;
}
.view-btn + .view-btn {
  border-left: 1px solid rgba(125, 211, 252, 0.3);
}
.view-btn:hover {
  color: #e2e8f0;
  background: rgba(30, 58, 138, 0.22);
}
.view-btn.active {
  color: #082f49;
  background: linear-gradient(135deg, #67e8f9 0%, #7dd3fc 100%);
}
@media (max-width: 639px) {
  .featured-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
  }
  .view-btn {
    height: 2.2rem;
    width: 2.4rem;
  }
}
@media (min-width: 640px) {
  .all-tracks-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
  }
}
@media (min-width: 1024px) {
  .all-tracks-grid {
    grid-template-columns: repeat(10, minmax(0, 1fr));
    gap: 0.5rem;
  }
  .all-tracks-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.65rem;
  }
}
</style>
