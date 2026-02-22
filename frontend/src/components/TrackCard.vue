<template>
  <div
    class="track-card group rounded-lg sm:rounded-xl overflow-hidden cursor-pointer border border-cyan-500/10 hover:border-cyan-500/30 transition-all duration-300 hover:shadow-track"
    @click="playTrack"
  >
    <div class="relative aspect-square sm:aspect-[4/3] overflow-hidden bg-gray-900">
      <img
        :src="getImageUrl(track?.image_url)"
        :alt="track?.title || 'Track'"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        @error="onImageError"
      >
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>
    <div class="p-2 sm:p-4 bg-gray-900/80 backdrop-blur">
      <p class="font-bold text-white text-[11px] sm:text-base truncate group-hover:text-cyan-200 transition-colors">{{ track?.title || "Unknown Track" }}</p>
      <p class="text-gray-300 text-[10px] sm:text-sm truncate">{{ track?.artist || "Unknown Artist" }}</p>
      <p class="hidden sm:block text-gray-500 text-xs mt-2">{{ track?.genre || "" }} <span v-if="track?.duration">| {{ track.duration }}</span></p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  track: Object,
});

const emit = defineEmits(["play"]);
const API_BASE = import.meta.env.VITE_API_URL?.replace(/\/api\/?$/, "") || "http://localhost:8000";

const playTrack = () => {
  emit("play", props.track);
};

const placeholderImage =
  "data:image/svg+xml," +
  encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400"><rect fill="%23374151" width="400" height="400"/><text fill="%239ca3af" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="18">No cover</text></svg>'
  );

const getImageUrl = (url) => {
  if (!url) return placeholderImage;
  if (typeof url === "string" && url.startsWith("http")) return url;
  return `${API_BASE}${url.startsWith("/") ? url : "/" + url}`;
};

const onImageError = (event) => {
  event.target.src = placeholderImage;
};
</script>

<style scoped>
.track-card {
  background: linear-gradient(180deg, rgba(17, 24, 39, 0.9) 0%, rgba(3, 7, 18, 0.95) 100%);
}
.track-card:hover {
  box-shadow: 0 0 40px rgba(6, 182, 212, 0.15), 0 0 80px rgba(139, 92, 246, 0.08);
}
</style>
