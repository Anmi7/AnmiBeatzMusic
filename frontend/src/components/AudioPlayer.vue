<template>
  <div
    v-if="track"
    class="audio-player w-full border-t border-cyan-500/20 text-white relative"
    :style="playerStyle"
  >
    <audio
      v-if="hasAudio"
      ref="audioElement"
      :src="getAudioUrl(track?.audio_url)"
      preload="metadata"
      @timeupdate="updateProgress"
      @ended="handleEnded"
      @loadedmetadata="updateDuration"
      @play="onPlay"
      @pause="onPause"
    ></audio>

    <div class="max-w-7xl mx-auto px-3 sm:px-4 pt-3 sm:pt-4 pb-safe">
      <div class="flex items-center gap-2 sm:gap-4">
        <img
          :src="getImageUrl(track?.image_url)"
          :alt="track?.title || 'Track'"
          class="h-11 w-11 sm:h-16 sm:w-16 rounded bg-gray-800 object-cover"
          @error="onImageError"
        >

        <div class="flex-1 min-w-0">
          <p class="font-bold text-sm sm:text-base truncate">{{ track?.title || "Unknown Track" }}</p>
          <p class="text-gray-400 text-xs sm:text-sm truncate">{{ track?.artist || "Unknown Artist" }}</p>
        </div>

        <div class="flex items-center gap-1 sm:gap-2 shrink-0">
          <a
            v-if="typeof track?.spotify_link === 'string' && track.spotify_link.trim()"
            :href="track.spotify_link"
            target="_blank"
            rel="noopener"
            class="stream-icon spotify-icon"
            title="Open on Spotify"
          >
            <i class="fab fa-spotify"></i>
          </a>
          <a
            v-if="typeof track?.youtube_link === 'string' && track.youtube_link.trim()"
            :href="track.youtube_link"
            target="_blank"
            rel="noopener"
            class="stream-icon youtube-icon"
            title="Open on YouTube"
          >
            <i class="fab fa-youtube"></i>
          </a>
          <button @click="closePlayer" class="h-10 w-10 text-lg sm:text-xl flex items-center justify-center">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div v-if="hasAudio" class="mt-2">
        <input
          type="range"
          min="0"
          max="100"
          step="0.1"
          :value="progress"
          class="seek-slider w-full"
          @input="onSeekInput"
          @change="onSeekInput"
          @pointerdown="onSeekPointer"
          @touchstart.prevent="onSeekPointer"
        >

        <div class="mt-1 grid grid-cols-[1fr_auto_1fr] items-center">
          <button
            type="button"
            class="justify-self-start text-xs text-gray-400 hover:text-cyan-300 transition"
            @click="seekToPercent(0)"
          >
            {{ currentTime }}
          </button>

          <div class="justify-self-center flex items-center gap-1 sm:gap-2">
            <button @click="previousTrack" class="h-10 w-10 text-lg sm:text-xl flex items-center justify-center">
              <i class="fas fa-backward"></i>
            </button>
            <button v-if="hasAudio" @click="togglePlayPause" class="h-10 w-10 text-xl sm:text-2xl text-cyan-400 hover:text-cyan-300 transition flex items-center justify-center">
              <i :class="isPlaying ? 'fas fa-pause' : 'fas fa-play'"></i>
            </button>
            <button @click="nextTrack" class="h-10 w-10 text-lg sm:text-xl flex items-center justify-center">
              <i class="fas fa-forward"></i>
            </button>
          </div>

          <div class="justify-self-end flex items-center gap-2 sm:gap-3">
            <button
              type="button"
              class="text-xs text-gray-400 hover:text-cyan-300 transition"
              @click="seekToPercent(100)"
            >
              {{ duration }}
            </button>
          </div>
        </div>
      </div>

      <p v-else class="mt-2 text-xs sm:text-sm text-gray-500">No audio file</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
  track: Object,
  playToken: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(["previous", "next", "close"]);
const API_BASE = import.meta.env.VITE_API_URL?.replace(/\/api\/?$/, "") || "http://localhost:8000";

const hasAudio = computed(() => {
  const url = props.track?.audio_url;
  return typeof url === "string" && url.trim() !== "";
});

const audioElement = ref(null);
const isPlaying = ref(false);
const progress = ref(0);
const currentTime = ref("0:00");
const duration = ref("0:00");
const playerBottomOffset = ref("0px");
const playerStyle = computed(() => ({
  position: "fixed",
  left: "0",
  right: "0",
  bottom: playerBottomOffset.value,
  zIndex: "2147483646",
}));

const togglePlayPause = async () => {
  if (!audioElement.value || !hasAudio.value) return;
  if (isPlaying.value) {
    audioElement.value.pause();
  } else {
    try {
      await audioElement.value.play();
    } catch {
      isPlaying.value = false;
    }
  }
};

const updateProgress = () => {
  if (!audioElement.value) return;
  if (!audioElement.value.duration) return;
  progress.value = (audioElement.value.currentTime / audioElement.value.duration) * 100;
  currentTime.value = formatTime(audioElement.value.currentTime);
};

const updateDuration = () => {
  if (!audioElement.value) return;
  duration.value = formatTime(audioElement.value.duration);
};

const onSeekInput = (event) => {
  seekToPercent(Number(event.target.value));
};

const onSeekPointer = (event) => {
  if (!audioElement.value || !audioElement.value.duration) return;
  const slider = event.currentTarget;
  const rect = slider.getBoundingClientRect();
  const point = event.touches?.[0] || event;
  const x = point.clientX;
  const percent = ((x - rect.left) / rect.width) * 100;
  seekToPercent(percent);
};

const seekToPercent = (percent) => {
  if (!audioElement.value || !audioElement.value.duration) return;
  const clamped = Math.max(0, Math.min(100, Number(percent) || 0));
  audioElement.value.currentTime = (clamped / 100) * audioElement.value.duration;
  progress.value = clamped;
  currentTime.value = formatTime(audioElement.value.currentTime);
};

const nextTrack = () => {
  emit("next");
};

const previousTrack = () => {
  emit("previous");
};

const handleEnded = () => {
  isPlaying.value = false;
  nextTrack();
};

const onPlay = () => {
  isPlaying.value = true;
};

const onPause = () => {
  isPlaying.value = false;
};

const closePlayer = () => {
  emit("close");
};

const formatTime = (seconds) => {
  if (!seconds) return "0:00";
  const mins = Math.floor(seconds / 60);
  const secs = Math.floor(seconds % 60);
  return `${mins}:${secs.toString().padStart(2, "0")}`;
};

const getAudioUrl = (url) => {
  if (!url) return "";
  if (typeof url === "string" && url.startsWith("http")) return url;
  return `${API_BASE}${url.startsWith("/") ? url : "/" + url}`;
};

const placeholderImage =
  "data:image/svg+xml," +
  encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect fill="%23374151" width="64" height="64"/><text fill="%239ca3af" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="10">No cover</text></svg>'
  );

const getImageUrl = (url) => {
  if (!url) return placeholderImage;
  if (typeof url === "string" && url.startsWith("http")) return url;
  return `${API_BASE}${url.startsWith("/") ? url : "/" + url}`;
};

const onImageError = (event) => {
  event.target.src = placeholderImage;
};

const autoPlayCurrentTrack = async (restart = false) => {
  if (!hasAudio.value) {
    isPlaying.value = false;
    return;
  }
  await nextTick();
  if (!audioElement.value) return;
  if (restart) {
    audioElement.value.currentTime = 0;
    progress.value = 0;
    currentTime.value = "0:00";
  }
  try {
    await audioElement.value.play();
  } catch {
    isPlaying.value = false;
  }
};

const updateViewportOffset = () => {
  let offset = 0;
  const vv = window.visualViewport;
  if (vv) {
    offset = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
  }

  playerBottomOffset.value = `calc(${Math.round(offset)}px + env(safe-area-inset-bottom))`;
};

onMounted(() => {
  updateViewportOffset();
  window.addEventListener("resize", updateViewportOffset);
  if (window.visualViewport) {
    window.visualViewport.addEventListener("resize", updateViewportOffset);
    window.visualViewport.addEventListener("scroll", updateViewportOffset);
  }
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateViewportOffset);
  if (window.visualViewport) {
    window.visualViewport.removeEventListener("resize", updateViewportOffset);
    window.visualViewport.removeEventListener("scroll", updateViewportOffset);
  }
});

watch(
  [() => props.track?.id, () => props.playToken],
  async ([id, token], oldPair = []) => {
    const [prevId, prevToken] = oldPair;
    if (!id) return;
    progress.value = 0;
    currentTime.value = "0:00";
    duration.value = "0:00";
    isPlaying.value = false;
    const restart = token !== prevToken || id !== prevId;
    await autoPlayCurrentTrack(restart);
  }
);
</script>

<style scoped>
.audio-player {
  position: fixed !important;
  left: 0 !important;
  right: 0 !important;
  background: linear-gradient(180deg, rgba(3, 7, 18, 0.98) 0%, rgba(0, 0, 0, 1) 100%);
  backdrop-filter: blur(12px);
  box-shadow: 0 -4px 30px rgba(6, 182, 212, 0.08);
}
.pb-safe {
  padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
}
.seek-slider {
  cursor: pointer;
  accent-color: #22d3ee;
  -webkit-appearance: none;
  appearance: none;
  height: 1.8rem;
  background: transparent;
  touch-action: pan-y;
}
.seek-slider::-webkit-slider-runnable-track {
  height: 0.45rem;
  border-radius: 9999px;
  background: linear-gradient(90deg, rgba(34, 211, 238, 0.35) 0%, rgba(192, 132, 252, 0.35) 100%);
}
.seek-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 1.2rem;
  height: 1.2rem;
  border-radius: 9999px;
  margin-top: -0.38rem;
  background: #22d3ee;
  border: 2px solid #0b1220;
  box-shadow: 0 0 0 2px rgba(34, 211, 238, 0.35);
}
.seek-slider::-moz-range-track {
  height: 0.45rem;
  border-radius: 9999px;
  background: linear-gradient(90deg, rgba(34, 211, 238, 0.35) 0%, rgba(192, 132, 252, 0.35) 100%);
}
.seek-slider::-moz-range-thumb {
  width: 1.2rem;
  height: 1.2rem;
  border-radius: 9999px;
  background: #22d3ee;
  border: 2px solid #0b1220;
  box-shadow: 0 0 0 2px rgba(34, 211, 238, 0.35);
}
.stream-icon {
  font-size: 1.55rem;
  line-height: 1;
  padding: 0.25rem 0.3rem;
  transition: transform 0.2s, filter 0.2s, color 0.2s;
}
.stream-icon:hover {
  transform: translateY(-1px);
  filter: brightness(1.15);
}
.spotify-icon {
  color: #22c55e;
}
.youtube-icon {
  color: #ef4444;
}
@media (max-width: 640px) {
  .stream-icon {
    font-size: 1.9rem;
    padding: 0.25rem 0.3rem;
  }
}
</style>
