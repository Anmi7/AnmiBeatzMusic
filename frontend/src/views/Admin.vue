<template>
  <div class="anmi-page min-h-screen text-white pt-24 pb-16">
    <div class="max-w-4xl mx-auto px-4">
      <div v-if="!adminToken" class="admin-glass p-8 rounded-2xl border border-cyan-500/20 shadow-glow">
        <h1 class="text-2xl sm:text-3xl font-bold mb-2 bg-gradient-to-r from-cyan-400 to-sky-400 bg-clip-text text-transparent">Admin</h1>
        <p class="text-gray-400 mb-6">Enter your admin token to continue.</p>
        <form @submit.prevent="submitToken" class="space-y-4">
          <input
            v-model="tokenInput"
            type="password"
            placeholder="Admin token"
            class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
            autocomplete="current-password"
          >
          <button type="submit" class="btn-primary w-full py-3 rounded-xl font-bold flex items-center justify-center gap-2">
            <i class="fas fa-lock-open"></i> Continue
          </button>
        </form>
        <p v-if="tokenError" class="mt-4 text-red-400 text-sm">{{ tokenError }}</p>
      </div>

      <div v-else class="space-y-10">
        <div class="admin-glass p-8 rounded-2xl border border-cyan-500/20 shadow-glow">
          <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-cyan-400 to-sky-400 bg-clip-text text-transparent">Add Song</h1>
            <div class="flex items-center gap-3">
              <router-link
                to="/admin/tracks"
                class="text-cyan-300 hover:text-cyan-200 transition text-sm flex items-center gap-1"
              >
                <i class="fas fa-table"></i> Manage tracks
              </router-link>
              <button
                type="button"
                @click="logout"
                class="text-gray-400 hover:text-cyan-400 transition text-sm flex items-center gap-1"
              >
                <i class="fas fa-sign-out-alt"></i> Exit admin
              </button>
            </div>
          </div>

          <form @submit.prevent="submitTrack" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Song Title *</label>
              <input
                v-model="form.title"
                type="text"
                required
                maxlength="255"
                placeholder="Track title"
                class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
              >
              <p v-if="errors.title" class="mt-1 text-red-400 text-sm">{{ errors.title }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Artist Name *</label>
              <input
                v-model="form.artist"
                type="text"
                required
                maxlength="255"
                placeholder="Artist"
                class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                autocomplete="off"
                @focus="showArtistMenu = true"
                @blur="hideArtistMenuSoon"
                @input="onArtistInput"
                @keydown="onArtistInputKeydown"
              >
              <div
                v-if="showArtistMenu && artistSuggestions.length > 0"
                class="mt-2 max-h-44 overflow-auto rounded-lg border border-cyan-500/20 bg-black/70"
              >
                <button
                  v-for="artist in artistSuggestions"
                  :key="artist"
                  type="button"
                  class="w-full text-left px-3 py-2 text-sm hover:bg-cyan-500/15"
                  @mousedown.prevent="selectArtist(artist)"
                >
                  {{ artist }}
                </button>
              </div>
              <p class="mt-2 text-xs text-gray-500">Artist names auto-reuse existing casing (case-insensitive).</p>
              <p v-if="errors.artist" class="mt-1 text-red-400 text-sm">{{ errors.artist }}</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Spotify URL (optional)</label>
                <input
                  v-model="form.spotify_link"
                  type="url"
                  placeholder="https://open.spotify.com/..."
                  class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
                >
                <p v-if="errors.spotify_link" class="mt-1 text-red-400 text-sm">{{ errors.spotify_link }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">YouTube URL (optional)</label>
                <input
                  v-model="form.youtube_link"
                  type="url"
                  placeholder="https://youtube.com/watch?v=..."
                  class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-sky-500/30 text-white placeholder-gray-500 focus:border-sky-400 focus:ring-2 focus:ring-sky-400/20"
                >
                <p v-if="errors.youtube_link" class="mt-1 text-red-400 text-sm">{{ errors.youtube_link }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Genres *</label>
              <div class="rounded-xl bg-black/40 border border-cyan-500/30 p-3">
                <div class="flex flex-wrap gap-2 mb-2">
                  <span
                    v-for="genre in form.genres"
                    :key="genre"
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm bg-cyan-500/20 border border-cyan-400/40"
                  >
                    {{ genre }}
                    <button type="button" @click="removeGenre(genre)" class="text-cyan-200 hover:text-white">
                      <i class="fas fa-times"></i>
                    </button>
                  </span>
                </div>
                <input
                  v-model="genreQuery"
                  type="text"
                  placeholder="Search genre, then press Enter. Use comma for multiple."
                  class="input-neon w-full px-3 py-2 rounded-lg bg-black/30 border border-cyan-500/20 text-white placeholder-gray-500"
                  @focus="showGenreMenu = true"
                  @blur="hideGenreMenuSoon"
                  @keydown="onGenreInputKeydown"
                >
                <div
                  v-if="showGenreMenu && filteredGenreOptions.length > 0"
                  class="mt-2 max-h-44 overflow-auto rounded-lg border border-cyan-500/20 bg-black/70"
                >
                  <button
                    v-for="genre in filteredGenreOptions"
                    :key="genre"
                    type="button"
                    class="w-full text-left px-3 py-2 text-sm hover:bg-cyan-500/15"
                    @mousedown.prevent="selectGenre(genre)"
                  >
                    {{ genre }}
                  </button>
                </div>
                <p class="mt-2 text-xs text-gray-500">Preset genres are alphabetized, but custom genres are also allowed.</p>
              </div>
              <p v-if="errors.genre" class="mt-1 text-red-400 text-sm">{{ errors.genre }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Cover Image *</label>
              <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                class="hidden"
                @change="onFileSelect"
              >
              <button
                type="button"
                @click="fileInput?.click()"
                class="w-full px-4 py-6 rounded-xl border-2 border-dashed border-cyan-500/40 hover:border-cyan-400/60 hover:bg-cyan-500/5 transition text-gray-400 hover:text-cyan-300 flex flex-col items-center gap-2"
              >
                <i class="fas fa-cloud-upload-alt text-xl sm:text-2xl"></i>
                <span>{{ form.coverFile ? form.coverFile.name : `Choose image (JPEG, PNG, GIF, WebP, max ${MAX_IMAGE_SIZE_MB}MB)` }}</span>
              </button>
              <img v-if="coverPreviewUrl" :src="coverPreviewUrl" alt="Cover preview" class="mt-3 h-40 w-40 sm:h-44 sm:w-44 rounded-xl object-cover border border-cyan-500/20">
              <p v-if="errors.cover" class="mt-1 text-red-400 text-sm">{{ errors.cover }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Audio File *</label>
              <input
                ref="audioInput"
                type="file"
                accept=".mp3,.wav,.ogg,.m4a,.aac,.flac,audio/*"
                class="hidden"
                @change="onAudioSelect"
              >
              <button
                type="button"
                @click="audioInput?.click()"
                class="w-full px-4 py-6 rounded-xl border-2 border-dashed border-sky-500/40 hover:border-sky-400/60 hover:bg-sky-500/5 transition text-gray-400 hover:text-sky-300 flex flex-col items-center gap-2"
              >
                <i class="fas fa-music text-xl sm:text-2xl"></i>
                <span>{{ form.audioFile ? form.audioFile.name : "Choose audio (MP3, WAV, OGG, M4A, AAC, FLAC, max 25MB)" }}</span>
              </button>
              <audio v-if="audioPreviewUrl" :src="audioPreviewUrl" class="mt-3 w-full" controls preload="metadata"></audio>
              <p v-if="form.duration" class="mt-1 text-xs text-gray-400">Detected duration: {{ form.duration }}</p>
              <p v-if="errors.audio" class="mt-1 text-red-400 text-sm">{{ errors.audio }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-2">Release Date *</label>
              <input
                v-model="form.release_date"
                type="date"
                required
                class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
              >
              <p v-if="errors.release_date" class="mt-1 text-red-400 text-sm">{{ errors.release_date }}</p>
            </div>

            <div class="flex gap-4 pt-4">
              <button
                type="submit"
                :disabled="submitting"
                class="btn-primary flex-1 py-3 rounded-xl font-bold flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-plus"></i>
                {{ submitting ? "Uploading..." : "Add Song" }}
              </button>
              <router-link
                to="/"
                class="px-6 py-3 rounded-xl border border-gray-600 text-gray-300 hover:border-cyan-500/50 hover:text-cyan-300 transition text-center"
              >
                Cancel
              </router-link>
            </div>
          </form>

          <p v-if="submitError" class="mt-6 text-red-400 text-sm">{{ submitError }}</p>
          <p v-if="submitSuccess" class="mt-6 text-cyan-400 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            Song added.
            <router-link to="/" class="underline hover:text-cyan-300">View on site</router-link>
            <span class="text-cyan-500/60">|</span>
            <router-link to="/admin/tracks" class="underline hover:text-cyan-300">Manage tracks</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";
const ADMIN_TOKEN_KEY = "anmi_admin_token";
const MAX_IMAGE_SIZE_MB = 20;
const MAX_IMAGE_SIZE_BYTES = MAX_IMAGE_SIZE_MB * 1024 * 1024;

const PRESET_GENRES = [
  "Boom Bap",
  "Brazilian Funk",
  "Drill",
  "G-Funk",
  "Hip-hop",
  "Indie",
  "Jersey Club",
  "Lo-fi",
  "Melodic",
  "Phonk",
  "Pop",
  "Punk",
  "Reggae",
  "RNB",
  "Rock",
  "Trap",
  "Trapsoul",
  "West Coast",
].sort((a, b) => a.localeCompare(b));

const router = useRouter();
const route = useRoute();
const adminToken = ref("");
const tokenInput = ref("");
const tokenError = ref("");

const fileInput = ref(null);
const audioInput = ref(null);
const coverPreviewUrl = ref("");
const audioPreviewUrl = ref("");

const submitting = ref(false);
const submitError = ref("");
const submitSuccess = ref(false);
const tracks = ref([]);

const genreQuery = ref("");
const showGenreMenu = ref(false);
const showArtistMenu = ref(false);

const form = reactive({
  title: "",
  artist: "Anmi Beatz",
  genres: [],
  spotify_link: "",
  youtube_link: "",
  coverFile: null,
  audioFile: null,
  duration: null,
  release_date: new Date().toISOString().slice(0, 10),
});

const errors = reactive({
  title: "",
  artist: "",
  genre: "",
  spotify_link: "",
  youtube_link: "",
  cover: "",
  audio: "",
  release_date: "",
});

const filteredGenreOptions = computed(() => {
  const query = genreQuery.value.trim().toLowerCase();
  return PRESET_GENRES.filter((genre) => {
    const alreadySelected = form.genres.some((selected) => selected.toLowerCase() === genre.toLowerCase());
    if (alreadySelected) return false;
    return query ? genre.toLowerCase().includes(query) : true;
  });
});

const artistOptions = computed(() => {
  const canonicalByKey = new Map();
  const orderedTracks = [...tracks.value].sort((a, b) => (a?.id ?? 0) - (b?.id ?? 0));

  orderedTracks.forEach((track) => {
    const artist = typeof track?.artist === "string" ? track.artist.trim() : "";
    if (!artist) return;
    const key = artist.toLowerCase();
    if (!canonicalByKey.has(key)) {
      canonicalByKey.set(key, artist);
    }
  });

  return [...canonicalByKey.values()].sort((a, b) =>
    a.localeCompare(b, undefined, { sensitivity: "base" })
  );
});

const artistLookup = computed(() => {
  const map = new Map();
  artistOptions.value.forEach((artist) => map.set(artist.toLowerCase(), artist));
  return map;
});

const artistSuggestions = computed(() => {
  const query = form.artist.trim().toLowerCase();
  if (!query) return artistOptions.value.slice(0, 10);

  const startsWith = [];
  const contains = [];

  artistOptions.value.forEach((artist) => {
    const lower = artist.toLowerCase();
    if (lower.startsWith(query)) {
      startsWith.push(artist);
    } else if (lower.includes(query)) {
      contains.push(artist);
    }
  });

  return [...startsWith, ...contains].slice(0, 10);
});

onMounted(() => {
  const saved = sessionStorage.getItem(ADMIN_TOKEN_KEY);
  if (saved) {
    adminToken.value = saved;
    const redirect = typeof route.query.redirect === "string" ? route.query.redirect : "";
    if (redirect) {
      router.replace(redirect);
    }
  }
});

onBeforeUnmount(() => {
  clearObjectUrl(coverPreviewUrl);
  clearObjectUrl(audioPreviewUrl);
});

watch(adminToken, (val) => {
  if (val) fetchTracks();
});

function clearObjectUrl(urlRef) {
  if (urlRef.value) {
    URL.revokeObjectURL(urlRef.value);
    urlRef.value = "";
  }
}

async function fetchTracks() {
  if (!adminToken.value) return;
  try {
    const res = await axios.get(`${API_URL}/tracks`);
    tracks.value = Array.isArray(res.data) ? res.data : res.data?.data ?? [];
  } catch (error) {
    if (error.response?.status === 401) logout();
    else tracks.value = [];
  }
}

function submitToken() {
  tokenError.value = "";
  if (!tokenInput.value.trim()) {
    tokenError.value = "Enter your admin token.";
    return;
  }
  const token = tokenInput.value.trim();
  sessionStorage.setItem(ADMIN_TOKEN_KEY, token);
  adminToken.value = token;
  tokenInput.value = "";

  const redirect = typeof route.query.redirect === "string" ? route.query.redirect : "";
  if (redirect) {
    router.push(redirect);
  }
}

function logout() {
  sessionStorage.removeItem(ADMIN_TOKEN_KEY);
  adminToken.value = "";
  router.push("/");
}

function normalizeGenre(raw) {
  const cleaned = raw.trim().replace(/\s+/g, " ");
  if (!cleaned) return "";
  const preset = PRESET_GENRES.find((genre) => genre.toLowerCase() === cleaned.toLowerCase());
  return preset || cleaned;
}

function normalizeArtist(raw) {
  const cleaned = (raw || "").trim().replace(/\s+/g, " ");
  if (!cleaned) return "";
  return artistLookup.value.get(cleaned.toLowerCase()) || cleaned;
}

function onArtistInput() {
  errors.artist = "";
  showArtistMenu.value = true;
}

function selectArtist(artist) {
  form.artist = artist;
  errors.artist = "";
  showArtistMenu.value = false;
}

function hideArtistMenuSoon() {
  setTimeout(() => {
    showArtistMenu.value = false;
    form.artist = normalizeArtist(form.artist);
  }, 120);
}

function onArtistInputKeydown(event) {
  if (event.key === "Escape") {
    showArtistMenu.value = false;
    return;
  }

  if ((event.key === "Enter" || event.key === "Tab") && artistSuggestions.value.length > 0) {
    selectArtist(artistSuggestions.value[0]);
    if (event.key === "Enter") {
      event.preventDefault();
    }
    return;
  }

  if (event.key === "Enter") {
    form.artist = normalizeArtist(form.artist);
  }
}

function normalizeExternalUrl(raw) {
  const value = (raw || "").trim();
  if (!value) return "";
  if (/^https?:\/\//i.test(value)) return value;
  return `https://${value}`;
}

function isValidHttpUrl(raw) {
  if (!raw) return true;
  try {
    const parsed = new URL(raw);
    return parsed.protocol === "http:" || parsed.protocol === "https:";
  } catch {
    return false;
  }
}

function addGenreTag(raw) {
  const genre = normalizeGenre(raw);
  if (!genre) return;
  const exists = form.genres.some((selected) => selected.toLowerCase() === genre.toLowerCase());
  if (!exists) form.genres.push(genre);
}

function addGenresFromQuery() {
  const raw = genreQuery.value.trim();
  if (!raw) return;
  raw.split(",").map((genre) => genre.trim()).filter(Boolean).forEach(addGenreTag);
  genreQuery.value = "";
  errors.genre = "";
}

function selectGenre(genre) {
  addGenreTag(genre);
  genreQuery.value = "";
  errors.genre = "";
  showGenreMenu.value = true;
}

function removeGenre(genreToRemove) {
  form.genres = form.genres.filter((genre) => genre !== genreToRemove);
}

function hideGenreMenuSoon() {
  setTimeout(() => {
    showGenreMenu.value = false;
  }, 120);
}

function onGenreInputKeydown(event) {
  if (event.key === "Enter" || event.key === ",") {
    event.preventDefault();
    addGenresFromQuery();
    return;
  }
  if (event.key === "Backspace" && !genreQuery.value && form.genres.length > 0) {
    form.genres.pop();
  }
}

function isAllowedAudioFile(file) {
  const allowedMime = [
    "audio/mpeg",
    "audio/wav",
    "audio/x-wav",
    "audio/wave",
    "audio/ogg",
    "audio/mp4",
    "audio/x-m4a",
    "audio/aac",
    "audio/flac",
  ];
  const allowedExt = [".mp3", ".wav", ".ogg", ".m4a", ".aac", ".flac"];
  const name = file.name.toLowerCase();
  return allowedMime.includes(file.type) || allowedExt.some((ext) => name.endsWith(ext));
}

async function onFileSelect(event) {
  const file = event.target.files?.[0];
  if (!file) return;
  const allowed = ["image/jpeg", "image/png", "image/jpg", "image/gif", "image/webp"];
  if (file.size > MAX_IMAGE_SIZE_BYTES) {
    errors.cover = `Image must be under ${MAX_IMAGE_SIZE_MB}MB.`;
    return;
  }
  if (!allowed.includes(file.type)) {
    errors.cover = "Use JPEG, PNG, GIF or WebP.";
    return;
  }
  clearObjectUrl(coverPreviewUrl);
  errors.cover = "";
  form.coverFile = file;
  coverPreviewUrl.value = URL.createObjectURL(file);
}

async function onAudioSelect(event) {
  const file = event.target.files?.[0];
  if (!file) return;
  const max = 25 * 1024 * 1024;
  if (file.size > max) {
    errors.audio = "Audio must be under 25MB.";
    return;
  }
  if (!isAllowedAudioFile(file)) {
    errors.audio = "Use MP3, WAV, OGG, M4A, AAC, or FLAC.";
    return;
  }
  clearObjectUrl(audioPreviewUrl);
  errors.audio = "";
  form.audioFile = file;
  audioPreviewUrl.value = URL.createObjectURL(file);
  form.duration = await detectAudioDuration(file);
}

function formatDuration(seconds) {
  if (!Number.isFinite(seconds) || seconds <= 0) return null;
  const total = Math.floor(seconds);
  const mins = Math.floor(total / 60);
  const secs = total % 60;
  return `${mins}:${secs.toString().padStart(2, "0")}`;
}

function detectAudioDuration(file) {
  return new Promise((resolve) => {
    const objectUrl = URL.createObjectURL(file);
    const audio = new Audio();
    const done = (value) => {
      audio.removeAttribute("src");
      audio.load();
      URL.revokeObjectURL(objectUrl);
      resolve(value);
    };

    audio.preload = "metadata";
    audio.onloadedmetadata = () => done(formatDuration(audio.duration));
    audio.onerror = () => done(null);
    audio.src = objectUrl;
  });
}

function clearErrors() {
  Object.keys(errors).forEach((key) => {
    errors[key] = "";
  });
  submitError.value = "";
}

async function submitTrack() {
  clearErrors();
  form.artist = normalizeArtist(form.artist);
  const spotifyLink = normalizeExternalUrl(form.spotify_link);
  const youtubeLink = normalizeExternalUrl(form.youtube_link);

  if (!form.title.trim()) errors.title = "Title is required.";
  if (!form.artist.trim()) errors.artist = "Artist is required.";
  if (form.genres.length === 0) errors.genre = "Add at least one genre.";
  if (!isValidHttpUrl(spotifyLink)) errors.spotify_link = "Enter a valid Spotify URL.";
  if (!isValidHttpUrl(youtubeLink)) errors.youtube_link = "Enter a valid YouTube URL.";
  if (!form.coverFile) errors.cover = "Choose a cover image.";
  if (!form.audioFile) errors.audio = "Choose an audio file.";
  if (!form.release_date) errors.release_date = "Release date is required.";
  if (Object.values(errors).some(Boolean)) return;

  submitting.value = true;
  submitError.value = "";
  submitSuccess.value = false;

  const headers = { "X-Admin-Token": adminToken.value };

  try {
    const coverFormData = new FormData();
    coverFormData.append("image", form.coverFile);
    const coverRes = await axios.post(`${API_URL}/upload/cover`, coverFormData, { headers });
    const imageUrl = coverRes.data?.url;
    if (!imageUrl) throw new Error("Cover upload did not return an image URL.");

    const audioFormData = new FormData();
    audioFormData.append("audio", form.audioFile);
    const audioRes = await axios.post(`${API_URL}/upload/audio`, audioFormData, { headers });
    const audioUrl = audioRes.data?.url;
    if (!audioUrl) throw new Error("Audio upload did not return an audio URL.");

    await axios.post(
      `${API_URL}/tracks`,
      {
        title: form.title.trim(),
        artist: form.artist.trim(),
        genre: form.genres.join(", "),
        image_url: imageUrl,
        audio_url: audioUrl,
        spotify_link: spotifyLink || null,
        youtube_link: youtubeLink || null,
        duration: form.duration || null,
        release_date: form.release_date,
      },
      { headers }
    );

    submitSuccess.value = true;
    form.title = "";
    form.artist = "Anmi Beatz";
    form.genres = [];
    form.spotify_link = "";
    form.youtube_link = "";
    form.coverFile = null;
    form.audioFile = null;
    form.duration = null;
    form.release_date = new Date().toISOString().slice(0, 10);
    genreQuery.value = "";

    clearObjectUrl(coverPreviewUrl);
    clearObjectUrl(audioPreviewUrl);
    if (fileInput.value) fileInput.value.value = "";
    if (audioInput.value) audioInput.value.value = "";

    await fetchTracks();
  } catch (error) {
    const msg = error.response?.data?.message || error.response?.data?.errors || error.message;
    submitError.value = typeof msg === "object" ? JSON.stringify(msg) : msg;
    if (error.response?.status === 401) {
      submitError.value = "Invalid admin token.";
      logout();
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<style scoped>
.admin-glass {
  background: linear-gradient(135deg, rgba(6, 182, 212, 0.06) 0%, rgba(96, 165, 250, 0.08) 100%);
  box-shadow: 0 0 40px rgba(6, 182, 212, 0.1);
}
.input-neon:focus {
  box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
}
.btn-primary {
  background: linear-gradient(135deg, #06b6d4 0%, #60a5fa 100%);
  box-shadow: 0 0 25px rgba(6, 182, 212, 0.4);
}
.btn-primary:hover:not(:disabled) {
  box-shadow: 0 0 35px rgba(96, 165, 250, 0.5);
  transform: translateY(-2px);
}
</style>
