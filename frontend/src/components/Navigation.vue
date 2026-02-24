<template>
  <nav class="nav-glass fixed w-full top-0 z-50 border-b border-cyan-500/10">
    <div class="w-full px-4 sm:px-8 xl:px-12 py-3 sm:py-4 flex justify-between items-center">
      <router-link to="/" class="flex items-center gap-2 group" @click="closeMenu">
        <img
          :src="logoUrl"
          alt="Anmi Beatz Logo"
          class="h-8 sm:h-10 object-contain nav-logo"
          @error="onLogoError"
        >
        <span class="text-white font-bold text-base sm:text-xl group-hover:text-cyan-400 transition-colors">Anmi Beatz</span>
      </router-link>

      <ul class="hidden md:flex gap-8 text-white">
        <li><a href="#home" class="nav-link" @click.prevent="scrollTo('#home')">Home</a></li>
        <li><a href="#about" class="nav-link" @click.prevent="scrollTo('#about')">About</a></li>
        <li><a href="#all-tracks" class="nav-link" @click.prevent="scrollTo('#all-tracks')">Music</a></li>
        <li><a href="#contact" class="nav-link" @click.prevent="scrollTo('#contact')">Contact</a></li>
      </ul>

      <button
        type="button"
        class="md:hidden text-white text-lg h-9 w-9 rounded-lg border border-cyan-500/30 hover:border-cyan-400/60 hover:text-cyan-300 transition flex items-center justify-center"
        :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'"
        @click="toggleMenu"
      >
        <i :class="isMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
      </button>
    </div>

    <div v-if="isMenuOpen" class="md:hidden border-t border-cyan-500/10 bg-black/90 backdrop-blur">
      <ul class="px-4 py-3 space-y-1 text-white">
        <li><a href="#home" class="mobile-nav-link" @click.prevent="scrollToAndClose('#home')">Home</a></li>
        <li><a href="#about" class="mobile-nav-link" @click.prevent="scrollToAndClose('#about')">About</a></li>
        <li><a href="#all-tracks" class="mobile-nav-link" @click.prevent="scrollToAndClose('#all-tracks')">Music</a></li>
        <li><a href="#contact" class="mobile-nav-link" @click.prevent="scrollToAndClose('#contact')">Contact</a></li>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const LOGO_PATH = '/assets/images/logos/Anmi%20Beatz%20Logo%201.png';
const logoUrl = ref(LOGO_PATH);
const fallbackLogo = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect fill="%2306b6d4" width="40" height="40" rx="4"/><text fill="white" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="14">AB</text></svg>');
const isMenuOpen = ref(false);

function onLogoError(e) {
  e.target.src = fallbackLogo;
}

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value;
}

function closeMenu() {
  isMenuOpen.value = false;
}

function scrollToAndClose(selector) {
  closeMenu();
  scrollTo(selector);
}

function scrollTo(selector) {
  const go = () => {
    const el = document.querySelector(selector);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };
  if (router.currentRoute.value.path !== '/') {
    router.push('/').then(() => setTimeout(go, 100));
  } else {
    go();
  }
}
</script>

<style scoped>
.nav-glass {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(12px);
  box-shadow: 0 0 30px rgba(6, 182, 212, 0.06);
}
.nav-link {
  transition: color 0.2s;
}
.nav-link:hover {
  color: rgb(34 211 238);
  text-shadow: 0 0 20px rgba(6, 182, 212, 0.4);
}
.nav-logo {
  filter: drop-shadow(0 0 8px rgba(6, 182, 212, 0.2));
}
.mobile-nav-link {
  display: block;
  padding: 0.6rem 0.2rem;
  border-radius: 0.5rem;
  transition: color 0.2s, background-color 0.2s;
}
.mobile-nav-link:hover {
  color: rgb(34 211 238);
  background: rgba(6, 182, 212, 0.1);
}
</style>
