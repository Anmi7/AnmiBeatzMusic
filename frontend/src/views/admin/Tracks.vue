<template>
  <div class="anmi-page min-h-screen text-white pt-24 pb-16">
    <div class="max-w-6xl mx-auto px-4">
      <div v-if="!adminToken" class="admin-glass p-8 rounded-2xl border border-cyan-500/20 shadow-glow text-center">
        <h1 class="text-2xl font-bold mb-2 bg-gradient-to-r from-cyan-400 to-sky-400 bg-clip-text text-transparent">Tracks Storage</h1>
        <p class="text-gray-400 mb-6">Admin login required.</p>
        <router-link
          to="/admin"
          class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition"
        >
          <i class="fas fa-lock"></i> Go to Admin Login
        </router-link>
      </div>

      <div v-else class="space-y-6">
        <div class="admin-glass p-6 rounded-2xl border border-cyan-500/20 shadow-glow">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-cyan-400 to-sky-400 bg-clip-text text-transparent">Tracks Storage</h1>
              <p class="text-gray-400 text-sm">
                {{ isRecoveryMode ? "Review deleted tracks and restore when needed." : "Manage your tracks with search, edit, and delete." }}
              </p>
            </div>
            <div class="flex items-center gap-2">
              <div class="inline-flex items-center rounded-xl border border-cyan-500/30 bg-black/30 p-1">
                <button
                  type="button"
                  @click="setViewMode('active')"
                  :class="[
                    'px-3 py-1.5 rounded-lg text-xs sm:text-sm transition',
                    !isRecoveryMode ? 'bg-cyan-500/25 text-cyan-200 border border-cyan-300/40' : 'text-gray-400 hover:text-cyan-200'
                  ]"
                >
                  Active
                </button>
                <button
                  type="button"
                  @click="setViewMode('deleted')"
                  :class="[
                    'px-3 py-1.5 rounded-lg text-xs sm:text-sm transition',
                    isRecoveryMode ? 'bg-amber-500/25 text-amber-200 border border-amber-300/40' : 'text-gray-400 hover:text-amber-200'
                  ]"
                >
                  Recovery
                </button>
              </div>
              <router-link
                to="/admin"
                class="px-4 py-2 rounded-xl border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition text-sm"
              >
                <i class="fas fa-plus mr-1"></i> Add Song
              </router-link>
              <button
                type="button"
                @click="logout"
                class="px-4 py-2 rounded-xl border border-gray-600 text-gray-300 hover:border-cyan-500/50 hover:text-cyan-300 transition text-sm"
              >
                <i class="fas fa-sign-out-alt mr-1"></i> Exit admin
              </button>
            </div>
          </div>
        </div>

        <div class="admin-glass p-5 rounded-2xl border border-cyan-500/20 shadow-glow">
          <div class="grid gap-3 sm:grid-cols-[1fr_auto]">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by title, artist, genre, or YYYY-MM-DD release date..."
              class="input-neon w-full px-4 py-3 rounded-xl bg-black/40 border border-cyan-500/30 text-white placeholder-gray-500 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20"
            >
            <button
              type="button"
              class="px-4 py-3 rounded-xl border border-cyan-500/40 text-cyan-300 hover:bg-cyan-500/10 transition"
              @click="clearSearch"
            >
              Clear
            </button>
          </div>
        </div>

        <div class="admin-glass p-5 rounded-2xl border border-cyan-500/20 shadow-glow">
          <p v-if="actionMessage" class="text-emerald-400 text-sm pb-3">{{ actionMessage }}</p>
          <p v-if="actionError" class="text-red-400 text-sm pb-3">{{ actionError }}</p>
          <p v-if="loadingTracks" class="text-gray-400 py-6">Loading tracks...</p>
          <p v-else-if="tracks.length === 0" class="text-gray-500 py-6">
            {{ isRecoveryMode ? "No deleted tracks found." : "No tracks found." }}
          </p>
          <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[980px] border-separate border-spacing-y-2">
              <thead>
                <tr class="text-left text-gray-400 text-xs uppercase tracking-wider">
                  <th class="px-3 py-2">Cover</th>
                  <th class="px-3 py-2">Title</th>
                  <th class="px-3 py-2">Artist</th>
                  <th class="px-3 py-2">Genre</th>
                  <th class="px-3 py-2">Release Date</th>
                  <th v-if="isRecoveryMode" class="px-3 py-2">Deleted At</th>
                  <th class="px-3 py-2">Duration</th>
                  <th class="px-3 py-2 text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="track in tracks"
                  :key="track.id"
                  class="bg-black/35 border border-gray-700/40"
                >
                  <td class="px-3 py-2 rounded-l-xl">
                    <img :src="getImageUrl(track.image_url)" :alt="track.title" class="h-12 w-12 rounded-md object-cover bg-gray-800 border border-cyan-500/15">
                  </td>
                  <td class="px-3 py-2">
                    <p class="font-semibold text-white leading-tight">{{ track.title }}</p>
                    <p class="text-xs text-gray-500">#{{ track.id }}</p>
                  </td>
                  <td class="px-3 py-2 text-gray-200">{{ track.artist }}</td>
                  <td class="px-3 py-2 text-gray-300 max-w-[260px] truncate" :title="track.genre">{{ track.genre }}</td>
                  <td class="px-3 py-2 text-gray-300">{{ formatDate(track.release_date) }}</td>
                  <td v-if="isRecoveryMode" class="px-3 py-2 text-amber-200">{{ formatDateTime(track.deleted_at) || "-" }}</td>
                  <td class="px-3 py-2 text-gray-300">{{ track.duration || "--:--" }}</td>
                  <td class="px-3 py-2 rounded-r-xl">
                    <div class="flex justify-end gap-2">
                      <template v-if="!isRecoveryMode">
                        <button
                          type="button"
                          @click="startEdit(track)"
                          class="py-2 px-3 rounded-lg border border-cyan-500/50 text-cyan-300 hover:bg-cyan-500/10 text-sm"
                        >
                          <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button
                          type="button"
                          @click="confirmDelete(track)"
                          class="py-2 px-3 rounded-lg border border-red-500/50 text-red-400 hover:bg-red-500/10 text-sm"
                        >
                          <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                      </template>
                      <button
                        v-else
                        type="button"
                        :disabled="restoringTrackId === track.id"
                        @click="restoreTrack(track)"
                        class="py-2 px-3 rounded-lg border border-emerald-500/50 text-emerald-300 hover:bg-emerald-500/10 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <i v-if="restoringTrackId === track.id" class="fas fa-spinner fa-spin mr-1"></i>
                        <i v-else class="fas fa-undo mr-1"></i>
                        {{ restoringTrackId === track.id ? "Restoring..." : "Restore" }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="pagination.lastPage > 1" class="mt-5 flex flex-wrap items-center justify-center gap-2">
            <button
              type="button"
              class="page-btn"
              :disabled="pagination.currentPage <= 1 || loadingTracks"
              @click="goToPage(pagination.currentPage - 1)"
            >
              Prev
            </button>

            <button
              v-for="item in pageItems"
              :key="`page-${item}`"
              type="button"
              class="page-btn"
              :class="{ active: item === pagination.currentPage, ellipsis: typeof item !== 'number' }"
              :disabled="typeof item !== 'number' || loadingTracks"
              @click="typeof item === 'number' && goToPage(item)"
            >
              {{ typeof item === "number" ? item : "..." }}
            </button>

            <button
              type="button"
              class="page-btn"
              :disabled="pagination.currentPage >= pagination.lastPage || loadingTracks"
              @click="goToPage(pagination.currentPage + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="editingTrack"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
      @click.self="cancelEdit"
    >
      <div class="admin-glass w-full max-w-2xl p-6 rounded-2xl border border-cyan-500/25 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Edit Track</h2>
        <form @submit.prevent="saveEdit" class="grid gap-3 sm:grid-cols-2">
          <input v-model="editForm.title" type="text" required placeholder="Title" class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-cyan-500/30 text-white text-sm sm:col-span-2">
          <input v-model="editForm.artist" type="text" required placeholder="Artist" class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-cyan-500/30 text-white text-sm">
          <input v-model="editForm.release_date" type="date" required class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-cyan-500/30 text-white text-sm">
          <input v-model="editForm.genre" type="text" required placeholder="Genres (comma-separated)" class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-cyan-500/30 text-white text-sm sm:col-span-2">
          <input v-model="editForm.spotify_link" type="url" placeholder="Spotify URL" class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-cyan-500/30 text-white text-sm">
          <input v-model="editForm.youtube_link" type="url" placeholder="YouTube URL" class="input-neon px-3 py-2 rounded-lg bg-black/40 border border-sky-500/30 text-white text-sm">

          <div>
            <label class="text-xs text-gray-500 block mb-1">New cover (optional)</label>
            <input type="file" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" @change="onEditFileSelect" class="text-sm w-full">
            <img v-if="editCoverPreviewUrl" :src="editCoverPreviewUrl" alt="New cover preview" class="mt-2 h-16 w-16 rounded object-cover border border-cyan-500/20">
          </div>

          <div>
            <label class="text-xs text-gray-500 block mb-1">New audio (optional)</label>
            <input type="file" accept=".mp3,.wav,.ogg,.m4a,.aac,.flac,audio/*" @change="onEditAudioSelect" class="text-sm w-full">
            <audio v-if="editAudioPreviewUrl" :src="editAudioPreviewUrl" controls class="mt-2 w-full"></audio>
          </div>

          <p v-if="editError" class="text-red-400 text-sm sm:col-span-2">{{ editError }}</p>
          <div class="sm:col-span-2 flex gap-2">
            <button type="submit" :disabled="savingEdit" class="btn-primary py-2 px-4 rounded-lg text-sm font-bold disabled:opacity-50">
              <i v-if="savingEdit" class="fas fa-spinner fa-spin mr-1"></i>
              {{ savingEdit ? "Saving..." : "Save changes" }}
            </button>
            <button type="button" @click="cancelEdit" class="py-2 px-4 rounded-lg border border-gray-600 text-gray-400 hover:text-white text-sm">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="trackToDelete"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
      @click.self="trackToDelete = null"
    >
      <div class="admin-glass p-6 rounded-2xl border border-cyan-500/20 max-w-md w-full">
        <p class="text-white font-medium mb-2">Delete this song?</p>
        <p class="text-gray-400 text-sm mb-4">"{{ trackToDelete.title }}" will be moved to Recovery. You can restore it later.</p>
        <div class="flex gap-3">
          <button
            @click="doDelete"
            :disabled="deleting"
            class="flex-1 py-2 rounded-xl font-bold bg-red-600 hover:bg-red-500 text-white disabled:opacity-50"
          >
            {{ deleting ? "Moving..." : "Move to Recovery" }}
          </button>
          <button @click="trackToDelete = null" class="flex-1 py-2 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-800">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const API_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api";
const API_BASE = import.meta.env.VITE_API_URL?.replace(/\/api\/?$/, "") || "http://localhost:8000";
const ADMIN_TOKEN_KEY = "anmi_admin_token";
const MAX_IMAGE_SIZE_MB = 20;
const MAX_IMAGE_SIZE_BYTES = MAX_IMAGE_SIZE_MB * 1024 * 1024;

const router = useRouter();
const adminToken = ref("");

const loadingTracks = ref(false);
const tracks = ref([]);
const searchQuery = ref("");
const searchTimer = ref(null);
const viewMode = ref("active");

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  perPage: 10,
  total: 0,
});

const editingTrack = ref(null);
const savingEdit = ref(false);
const editError = ref("");
const editForm = reactive({
  title: "",
  artist: "",
  genre: "",
  release_date: "",
  spotify_link: "",
  youtube_link: "",
  coverFile: null,
  audioFile: null,
  duration: null,
});
const editCoverPreviewUrl = ref("");
const editAudioPreviewUrl = ref("");

const trackToDelete = ref(null);
const deleting = ref(false);
const restoringTrackId = ref(null);
const actionMessage = ref("");
const actionError = ref("");

const isRecoveryMode = computed(() => viewMode.value === "deleted");

const pageItems = computed(() => {
  const total = pagination.lastPage;
  const current = pagination.currentPage;

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const items = [1];
  const windowStart = Math.max(2, current - 1);
  const windowEnd = Math.min(total - 1, current + 1);

  if (windowStart > 2) items.push("left");
  for (let page = windowStart; page <= windowEnd; page += 1) {
    items.push(page);
  }
  if (windowEnd < total - 1) items.push("right");
  items.push(total);

  return items;
});

onMounted(() => {
  const saved = sessionStorage.getItem(ADMIN_TOKEN_KEY);
  if (!saved) {
    router.replace("/admin");
    return;
  }
  adminToken.value = saved;
  fetchTracks(1);
});

onBeforeUnmount(() => {
  if (searchTimer.value) clearTimeout(searchTimer.value);
  clearObjectUrl(editCoverPreviewUrl);
  clearObjectUrl(editAudioPreviewUrl);
});

watch(searchQuery, () => {
  if (searchTimer.value) clearTimeout(searchTimer.value);
  searchTimer.value = setTimeout(() => {
    fetchTracks(1);
  }, 260);
});

function clearObjectUrl(urlRef) {
  if (urlRef.value) {
    URL.revokeObjectURL(urlRef.value);
    urlRef.value = "";
  }
}

function logout() {
  sessionStorage.removeItem(ADMIN_TOKEN_KEY);
  adminToken.value = "";
  router.push("/admin");
}

function getImageUrl(url) {
  if (!url) return "";
  if (typeof url === "string" && url.startsWith("http")) return url;
  return `${API_BASE}${url.startsWith("/") ? url : "/" + url}`;
}

function formatDate(value) {
  if (!value) return "";
  const date = typeof value === "string" ? value : value?.date || value;
  return String(date).slice(0, 10);
}

function formatDateTime(value) {
  if (!value) return "";
  const raw = typeof value === "string" ? value : value?.date || value;
  const date = new Date(raw);
  if (Number.isNaN(date.getTime())) {
    return String(raw).replace("T", " ").slice(0, 19);
  }
  return date.toLocaleString();
}

function setViewMode(mode) {
  if (mode !== "active" && mode !== "deleted") return;
  if (viewMode.value === mode) return;
  viewMode.value = mode;
  trackToDelete.value = null;
  cancelEdit();
  actionMessage.value = "";
  actionError.value = "";
  fetchTracks(1);
}

async function fetchTracks(page = 1) {
  if (!adminToken.value) return;
  loadingTracks.value = true;

  try {
    const endpoint = isRecoveryMode.value ? `${API_URL}/admin/tracks/deleted` : `${API_URL}/tracks`;
    const params = {
      paginate: 1,
      page,
      per_page: pagination.perPage,
      sort_by: isRecoveryMode.value ? "deleted_at" : "release_date",
      sort_dir: "desc",
    };
    const search = searchQuery.value.trim();
    if (search) params.search = search;

    const requestConfig = { params };
    if (isRecoveryMode.value) {
      requestConfig.headers = { "X-Admin-Token": adminToken.value };
    }

    const res = await axios.get(endpoint, requestConfig);
    const payload = res.data || {};

    tracks.value = Array.isArray(payload.data) ? payload.data : [];
    pagination.currentPage = Number(payload.current_page || page);
    pagination.lastPage = Number(payload.last_page || 1);
    pagination.perPage = Number(payload.per_page || 10);
    pagination.total = Number(payload.total || tracks.value.length);
  } catch (error) {
    if (error.response?.status === 401) {
      logout();
      return;
    }
    tracks.value = [];
    pagination.currentPage = 1;
    pagination.lastPage = 1;
    pagination.total = 0;
  } finally {
    loadingTracks.value = false;
  }
}

function goToPage(page) {
  if (loadingTracks.value) return;
  if (page < 1 || page > pagination.lastPage || page === pagination.currentPage) return;
  fetchTracks(page);
}

function clearSearch() {
  if (!searchQuery.value) return;
  searchQuery.value = "";
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

function startEdit(track) {
  editError.value = "";
  clearObjectUrl(editCoverPreviewUrl);
  clearObjectUrl(editAudioPreviewUrl);

  editingTrack.value = track;
  editForm.title = track.title || "";
  editForm.artist = track.artist || "";
  editForm.genre = track.genre || "";
  editForm.release_date = formatDate(track.release_date) || new Date().toISOString().slice(0, 10);
  editForm.spotify_link = track.spotify_link || "";
  editForm.youtube_link = track.youtube_link || "";
  editForm.coverFile = null;
  editForm.audioFile = null;
  editForm.duration = track.duration || null;
}

function cancelEdit() {
  editingTrack.value = null;
  editError.value = "";
  clearObjectUrl(editCoverPreviewUrl);
  clearObjectUrl(editAudioPreviewUrl);
}

function onEditFileSelect(event) {
  const file = event.target.files?.[0];
  if (!file) return;

  const allowed = ["image/jpeg", "image/png", "image/jpg", "image/gif", "image/webp"];
  if (file.size > MAX_IMAGE_SIZE_BYTES) {
    editError.value = `Image must be under ${MAX_IMAGE_SIZE_MB}MB.`;
    return;
  }
  if (!allowed.includes(file.type)) {
    editError.value = "Use JPEG, PNG, GIF or WebP for cover.";
    return;
  }

  editError.value = "";
  clearObjectUrl(editCoverPreviewUrl);
  editForm.coverFile = file;
  editCoverPreviewUrl.value = URL.createObjectURL(file);
}

async function onEditAudioSelect(event) {
  const file = event.target.files?.[0];
  if (!file) return;
  const max = 25 * 1024 * 1024;
  if (file.size > max) {
    editError.value = "Audio must be under 25MB.";
    return;
  }
  if (!isAllowedAudioFile(file)) {
    editError.value = "Use MP3, WAV, OGG, M4A, AAC, or FLAC.";
    return;
  }

  editError.value = "";
  clearObjectUrl(editAudioPreviewUrl);
  editForm.audioFile = file;
  editAudioPreviewUrl.value = URL.createObjectURL(file);
  editForm.duration = await detectAudioDuration(file);
}

async function saveEdit() {
  if (!editingTrack.value) return;
  editError.value = "";
  savingEdit.value = true;

  const headers = { "X-Admin-Token": adminToken.value };

  try {
    const spotifyLink = normalizeExternalUrl(editForm.spotify_link);
    const youtubeLink = normalizeExternalUrl(editForm.youtube_link);

    if (!isValidHttpUrl(spotifyLink)) throw new Error("Enter a valid Spotify URL.");
    if (!isValidHttpUrl(youtubeLink)) throw new Error("Enter a valid YouTube URL.");

    let imageUrl = editingTrack.value.image_url;
    let audioUrl = editingTrack.value.audio_url;
    let duration = editingTrack.value.duration;

    if (editForm.coverFile) {
      const coverFormData = new FormData();
      coverFormData.append("image", editForm.coverFile);
      const coverRes = await axios.post(`${API_URL}/upload/cover`, coverFormData, { headers });
      imageUrl = coverRes.data?.url || imageUrl;
    }

    if (editForm.audioFile) {
      const audioFormData = new FormData();
      audioFormData.append("audio", editForm.audioFile);
      const audioRes = await axios.post(`${API_URL}/upload/audio`, audioFormData, { headers });
      audioUrl = audioRes.data?.url || audioUrl;
      duration = editForm.duration || duration;
    }

    await axios.put(
      `${API_URL}/tracks/${editingTrack.value.id}`,
      {
        title: editForm.title.trim(),
        artist: editForm.artist.trim(),
        genre: editForm.genre.trim(),
        release_date: editForm.release_date,
        image_url: imageUrl,
        audio_url: audioUrl || null,
        spotify_link: spotifyLink || null,
        youtube_link: youtubeLink || null,
        duration: duration || null,
      },
      { headers }
    );

    cancelEdit();
    await fetchTracks(pagination.currentPage);
  } catch (error) {
    if (error.response?.status === 401) {
      logout();
      return;
    }
    editError.value = error.response?.data?.message || error.message || "Update failed.";
  } finally {
    savingEdit.value = false;
  }
}

function confirmDelete(track) {
  actionMessage.value = "";
  actionError.value = "";
  trackToDelete.value = track;
}

async function doDelete() {
  if (!trackToDelete.value) return;
  actionMessage.value = "";
  actionError.value = "";
  deleting.value = true;
  const headers = { "X-Admin-Token": adminToken.value };
  try {
    const deletedTitle = trackToDelete.value.title;
    await axios.delete(`${API_URL}/tracks/${trackToDelete.value.id}`, { headers });
    trackToDelete.value = null;
    actionMessage.value = `"${deletedTitle}" moved to recovery.`;

    const shouldGoBackOnePage = tracks.value.length === 1 && pagination.currentPage > 1;
    await fetchTracks(shouldGoBackOnePage ? pagination.currentPage - 1 : pagination.currentPage);
  } catch (error) {
    if (error.response?.status === 401) {
      logout();
      return;
    }
    actionError.value = error.response?.data?.message || "Failed to delete track.";
  } finally {
    deleting.value = false;
  }
}

async function restoreTrack(track) {
  if (!track?.id || restoringTrackId.value) return;
  actionMessage.value = "";
  actionError.value = "";
  restoringTrackId.value = track.id;

  try {
    const headers = { "X-Admin-Token": adminToken.value };
    await axios.post(`${API_URL}/tracks/${track.id}/restore`, {}, { headers });
    actionMessage.value = `"${track.title}" restored.`;

    const shouldGoBackOnePage = tracks.value.length === 1 && pagination.currentPage > 1;
    await fetchTracks(shouldGoBackOnePage ? pagination.currentPage - 1 : pagination.currentPage);
  } catch (error) {
    if (error.response?.status === 401) {
      logout();
      return;
    }
    actionError.value = error.response?.data?.message || "Failed to restore track.";
  } finally {
    restoringTrackId.value = null;
  }
}
</script>

<style scoped>
.admin-glass {
  background: linear-gradient(135deg, rgba(6, 182, 212, 0.06) 0%, rgba(96, 165, 250, 0.08) 100%);
  box-shadow: 0 0 40px rgba(6, 182, 212, 0.1);
}
.shadow-glow {
  box-shadow: 0 0 30px rgba(6, 182, 212, 0.12);
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
  transform: translateY(-1px);
}
.page-btn {
  min-width: 2.3rem;
  height: 2.1rem;
  padding: 0 0.55rem;
  border-radius: 0.6rem;
  border: 1px solid rgba(56, 189, 248, 0.35);
  color: #a5f3fc;
  background: rgba(2, 6, 23, 0.45);
  font-size: 0.85rem;
  transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}
.page-btn:hover:not(:disabled) {
  border-color: rgba(125, 211, 252, 0.75);
  background: rgba(14, 116, 144, 0.25);
}
.page-btn.active {
  color: #082f49;
  background: linear-gradient(135deg, #67e8f9 0%, #7dd3fc 100%);
  border-color: rgba(186, 230, 253, 0.8);
}
.page-btn.ellipsis {
  color: #64748b;
}
.page-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
</style>
