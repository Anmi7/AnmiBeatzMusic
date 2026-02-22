// === MUSIC DATA MANAGEMENT ===
// Music data is now loaded from music-data.js file

// === AUDIO PLAYER SYSTEM ===
class AudioPlayer {
    constructor() {
        this.audio = document.getElementById('audioElement');
        this.player = document.getElementById('audioPlayer');
        this.currentTrack = null;
        this.currentIndex = 0;
        this.isPlaying = false;
        this.playlist = [];
        
        this.initializeElements();
        this.bindEvents();
    }
    
    initializeElements() {
        this.playPauseBtn = document.getElementById('playPauseButton');
        this.prevBtn = document.getElementById('prevButton');
        this.nextBtn = document.getElementById('nextButton');
        this.closeBtn = document.getElementById('closePlayer');
        this.progressBar = document.getElementById('progressBar');
        this.progressFill = document.getElementById('progressFill');
        this.currentTimeEl = document.getElementById('currentTime');
        this.durationEl = document.getElementById('duration');
        this.playerImage = document.getElementById('playerImage');
        this.playerTitle = document.getElementById('playerTitle');
        this.playerArtist = document.getElementById('playerArtist');
    }
    
    bindEvents() {
        this.playPauseBtn.addEventListener('click', () => this.togglePlayPause());
        this.prevBtn.addEventListener('click', () => this.previousTrack());
        this.nextBtn.addEventListener('click', () => this.nextTrack());
        this.closeBtn.addEventListener('click', () => this.closePlayer());
        
        this.progressBar.addEventListener('click', (e) => this.seekTo(e));
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('ended', () => this.nextTrack());
        this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
    }
    
    loadTrack(track) {
        this.currentTrack = track;
        this.audio.src = track.audioFile;
        this.playerImage.src = track.image;
        this.playerTitle.textContent = track.title;
        this.playerArtist.textContent = track.artist;
        
        // Update all play buttons
        document.querySelectorAll('.play-button').forEach(btn => {
            btn.classList.remove('playing');
            btn.innerHTML = '<i class="fas fa-play"></i>';
        });
        
        // Mark current track's play button as playing
        const currentPlayBtn = document.querySelector(`[data-track-id="${track.id}"]`);
        if (currentPlayBtn) {
            currentPlayBtn.classList.add('playing');
            currentPlayBtn.innerHTML = '<i class="fas fa-pause"></i>';
        }
    }
    
    playTrack(track) {
        this.loadTrack(track);
        this.audio.play().then(() => {
            this.isPlaying = true;
            this.player.classList.add('active');
            this.updatePlayPauseButton();
        }).catch(e => {
            console.log('Audio play failed:', e);
            // Fallback: open YouTube link
            window.open(track.youtubeLink, '_blank');
        });
    }
    
    togglePlayPause() {
        if (!this.currentTrack) return;
        
        if (this.isPlaying) {
            this.audio.pause();
            this.isPlaying = false;
        } else {
            this.audio.play().then(() => {
                this.isPlaying = true;
            }).catch(e => {
                console.log('Audio play failed:', e);
                window.open(this.currentTrack.youtubeLink, '_blank');
            });
        }
        this.updatePlayPauseButton();
    }
    
    updatePlayPauseButton() {
        const icon = this.isPlaying ? 'fa-pause' : 'fa-play';
        this.playPauseBtn.innerHTML = `<i class="fas ${icon}"></i>`;
        
        // Update track play button
        if (this.currentTrack) {
            const trackBtn = document.querySelector(`[data-track-id="${this.currentTrack.id}"]`);
            if (trackBtn) {
                trackBtn.innerHTML = `<i class="fas ${icon}"></i>`;
            }
        }
    }
    
    previousTrack() {
        if (this.playlist.length === 0) return;
        this.currentIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
        this.playTrack(this.playlist[this.currentIndex]);
    }
    
    nextTrack() {
        if (this.playlist.length === 0) return;
        this.currentIndex = (this.currentIndex + 1) % this.playlist.length;
        this.playTrack(this.playlist[this.currentIndex]);
    }
    
    seekTo(e) {
        if (!this.audio.duration) return;
        const rect = this.progressBar.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        this.audio.currentTime = percent * this.audio.duration;
    }
    
    updateProgress() {
        if (!this.audio.duration) return;
        const percent = (this.audio.currentTime / this.audio.duration) * 100;
        this.progressFill.style.width = `${percent}%`;
        this.currentTimeEl.textContent = this.formatTime(this.audio.currentTime);
    }
    
    updateDuration() {
        this.durationEl.textContent = this.formatTime(this.audio.duration);
    }
    
    formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
    
    closePlayer() {
        this.audio.pause();
        this.isPlaying = false;
        this.player.classList.remove('active');
        this.updatePlayPauseButton();
    }
    
    setPlaylist(tracks) {
        this.playlist = tracks;
        this.currentIndex = 0;
    }
}

// === MUSIC DISPLAY SYSTEM ===
class MusicDisplay {
    constructor(audioPlayer) {
        this.audioPlayer = audioPlayer;
        this.currentSort = 'newest';
        this.currentGenre = 'all';
        this.currentArtist = 'all';
        this.filteredData = [...musicData];
        
        this.initializeElements();
        this.bindEvents();
        this.render();
    }
    
    initializeElements() {
        this.featuredGrid = document.getElementById('featuredGrid');
        this.musicGrid = document.getElementById('musicGrid');
        this.sortSelect = document.getElementById('sortSelect');
        this.genreFilter = document.getElementById('genreFilter');
        this.artistFilter = document.getElementById('artistFilter');
        
        // Add keyboard navigation for artist filter
        this.setupArtistKeyboardNavigation();
    }
    
    setupArtistKeyboardNavigation() {
        let searchString = '';
        let searchTimeout;
        
        this.artistFilter.addEventListener('focus', () => {
            // Clear search string when focusing
            searchString = '';
        });
        
        this.artistFilter.addEventListener('keydown', (e) => {
            // Only handle letter keys
            if (e.key.length === 1 && /[a-zA-Z]/.test(e.key)) {
                e.preventDefault();
                
                // Add to search string
                searchString += e.key.toLowerCase();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Find first option that starts with search string
                const options = Array.from(this.artistFilter.options);
                const matchingOption = options.find(option => 
                    option.value.toLowerCase().startsWith(searchString)
                );
                
                if (matchingOption) {
                    this.artistFilter.value = matchingOption.value;
                    this.currentArtist = matchingOption.value;
                    this.render();
                }
                
                // Clear search string after 1 second of inactivity
                searchTimeout = setTimeout(() => {
                    searchString = '';
                }, 1000);
            }
        });
    }
    
    bindEvents() {
        this.sortSelect.addEventListener('change', (e) => {
            this.currentSort = e.target.value;
            this.render();
        });
        
        this.genreFilter.addEventListener('change', (e) => {
            this.currentGenre = e.target.value;
            this.render();
        });
        
        this.artistFilter.addEventListener('change', (e) => {
            this.currentArtist = e.target.value;
            this.render();
        });
    }
    
    filterAndSort() {
        let filtered = [...musicData];
        
        // Filter by genre (supports multiple genres per track)
        if (this.currentGenre !== 'all') {
            filtered = filtered.filter(track => {
                if (Array.isArray(track.genre)) {
                    return track.genre.includes(this.currentGenre);
                } else {
                    return track.genre === this.currentGenre;
                }
            });
        }
        
        // Filter by artist (searches for artist name anywhere in the artist field)
        if (this.currentArtist !== 'all') {
            filtered = filtered.filter(track => 
                track.artist.toLowerCase().includes(this.currentArtist.toLowerCase())
            );
        }
        
        // Sort
        switch (this.currentSort) {
            case 'newest':
                filtered.sort((a, b) => new Date(b.releaseDate) - new Date(a.releaseDate));
                break;
            case 'oldest':
                filtered.sort((a, b) => new Date(a.releaseDate) - new Date(b.releaseDate));
                break;
            case 'title':
                filtered.sort((a, b) => a.title.localeCompare(b.title));
                break;
            case 'popular':
                filtered.sort((a, b) => b.plays - a.plays);
                break;
        }
        
        this.filteredData = filtered;
        return filtered;
    }
    
    createMusicCard(track) {
        const card = document.createElement('div');
        card.className = 'music-card animate-fadeInUp';
        
        card.innerHTML = `
            <div class="relative">
                <img src="${track.image}" alt="${track.title}" class="music-card-image" 
                     onerror="this.src='assets/images/music-covers/fallback.jpg'">
                ${track.gifPreview ? `
                    <img class="hover-gif" src="${track.gifPreview}" style="display: none;" alt="${track.title} Preview" 
                         onerror="console.log('GIF failed to load: ${track.gifPreview}'); this.style.display='none';">
                ` : ''}
                <div class="music-card-overlay">
                    <div class="preview-controls">
                        <button class="preview-btn youtube-btn" data-track-id="${track.id}" title="Watch on YouTube">
                            <i class="fab fa-youtube"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="music-card-content">
                <h3 class="music-title">${track.title}</h3>
                <p class="music-artist">${track.artist}</p>
                <div class="music-stats">
                    <span class="music-genre">${Array.isArray(track.genre) ? track.genre.join(', ') : track.genre}</span>
                    <span class="music-date">${this.formatDate(track.releaseDate)}</span>
                </div>
            </div>
        `;
        
        // Add GIF hover effect
        const hoverGif = card.querySelector('.hover-gif');
        const cardImage = card.querySelector('.music-card-image');
        
        if (hoverGif) {
            card.addEventListener('mouseenter', () => {
                // Check if GIF loaded successfully
                if (hoverGif.complete && hoverGif.naturalHeight !== 0) {
                    cardImage.style.display = 'none';
                    hoverGif.style.display = 'block';
                } else {
                    // Try to load GIF if not ready
                    hoverGif.onload = () => {
                        cardImage.style.display = 'none';
                        hoverGif.style.display = 'block';
                    };
                }
            });
            
            card.addEventListener('mouseleave', () => {
                hoverGif.style.display = 'none';
                cardImage.style.display = 'block';
            });
        }
        
        // Add click events for YouTube button
        const youtubeBtn = card.querySelector('.youtube-btn');
        
        if (youtubeBtn) {
            youtubeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                window.open(track.youtubeLink, '_blank');
            });
        }
        
    // Click on card to open YouTube
    card.addEventListener('click', (e) => {
        if (!e.target.closest('.preview-btn')) {
            window.open(track.youtubeLink, '_blank');
        }
    });
        
        return card;
    }
    
    playTrack(track) {
        // Set playlist as all filtered tracks starting from current track
        const currentIndex = this.filteredData.findIndex(t => t.id === track.id);
        const playlist = [...this.filteredData.slice(currentIndex), ...this.filteredData.slice(0, currentIndex)];
        this.audioPlayer.setPlaylist(playlist);
        this.audioPlayer.playTrack(track);
    }
    
    
    renderFeatured() {
        const featuredTracks = musicData.filter(track => track.featured);
        this.featuredGrid.innerHTML = '';
        
        featuredTracks.forEach(track => {
            const card = this.createMusicCard(track);
            this.featuredGrid.appendChild(card);
        });
    }
    
    renderAllMusic() {
        const tracks = this.filterAndSort();
        this.musicGrid.innerHTML = '';
        
        // Update results counter
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = tracks.length;
        }
        
        tracks.forEach(track => {
            const card = this.createMusicCard(track);
            this.musicGrid.appendChild(card);
        });
    }
    
    render() {
        this.renderFeatured();
        this.renderAllMusic();
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
    
    // Method to add new track dynamically
    addTrack(trackData) {
        const newTrack = {
            id: Math.max(...musicData.map(t => t.id)) + 1,
            ...trackData,
            releaseDate: trackData.releaseDate || new Date().toISOString().split('T')[0],
            featured: trackData.featured || false,
            plays: trackData.plays || 0
        };
        
        musicData.push(newTrack);
        this.render();
    }
}

// === INITIALIZATION ===
let audioPlayer, musicDisplay;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize audio player
    audioPlayer = new AudioPlayer();
    
    // Initialize music display
    musicDisplay = new MusicDisplay(audioPlayer);
    
    // Initialize hero slideshow
    startHeroSlideshow();
    
    // Initialize navigation
    initializeNavigation();
    
    // Initialize social links
    initializeSocialLinks();
});

// === HERO SLIDESHOW ===
function startHeroSlideshow() {
const heroSection = document.querySelector('.hero');
const heroImages = [
    'assets/images/backgrounds/Anmi Beatz Wallpaper.png',
    'assets/images/backgrounds/background1.jpg',
    'assets/images/backgrounds/background2.jpg',
    'assets/images/backgrounds/background3.jpg',
];
let currentHeroIndex = 0;

function createHeroSlide(img, className) {
    const slide = document.createElement('div');
    slide.className = `absolute inset-0 w-full h-full bg-cover bg-center transition-all duration-700 ${className}`;
    slide.style.backgroundImage = `url('${img}')`;
    slide.style.zIndex = 0;
    return slide;
}

    if (!heroSection) return;
    heroSection.style.position = 'relative';
    
    // Remove any old slides
    heroSection.querySelectorAll('.hero-bg-slide').forEach(e => e.remove());
    
    // Initial slide
    let activeSlide = createHeroSlide(heroImages[currentHeroIndex], 'hero-bg-slide opacity-100');
    heroSection.appendChild(activeSlide);
    
    setInterval(() => {
        // Prepare next slide
        const nextIndex = (currentHeroIndex + 1) % heroImages.length;
        const nextSlide = createHeroSlide(heroImages[nextIndex], 'hero-bg-slide opacity-0');
        heroSection.appendChild(nextSlide);
        
        // Animate fade/slide
        setTimeout(() => {
            activeSlide.classList.remove('opacity-100');
            activeSlide.classList.add('opacity-0');
            nextSlide.classList.remove('opacity-0');
            nextSlide.classList.add('opacity-100');
        }, 20);
        
        // Cleanup and update references
        setTimeout(() => {
            heroSection.removeChild(activeSlide);
            activeSlide = nextSlide;
            currentHeroIndex = nextIndex;
        }, 700);
    }, 5000);
}

// === NAVIGATION ===
function initializeNavigation() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Hamburger menu toggle for mobile
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
        
        // Close menu on link click (mobile UX)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('open');
            });
        });
    }
}

// === SOCIAL LINKS ===
function initializeSocialLinks() {
const socialLinks = {
    spotify: "YOUR_SPOTIFY_PROFILE_LINK",
    youtube: "YOUR_YOUTUBE_CHANNEL_LINK",
    instagram: "YOUR_INSTAGRAM_PROFILE_LINK",
    soundcloud: "YOUR_SOUNDCLOUD_PROFILE_LINK"
};

// Update social media links
document.querySelectorAll('.social-link').forEach(link => {
    const platform = link.querySelector('i').classList[1].split('-')[1];
    if (socialLinks[platform]) {
        link.href = socialLinks[platform];
    }
});
}

// === UTILITY FUNCTIONS ===
// Function to add new track (for dynamic management)
function addNewTrack(trackData) {
    if (musicDisplay) {
        musicDisplay.addTrack(trackData);
    }
}

// Function to update track data
function updateTrack(trackId, updates) {
    const trackIndex = musicData.findIndex(track => track.id === trackId);
    if (trackIndex !== -1) {
        musicData[trackIndex] = { ...musicData[trackIndex], ...updates };
        musicDisplay.render();
    }
}

// Function to remove track
function removeTrack(trackId) {
    const trackIndex = musicData.findIndex(track => track.id === trackId);
    if (trackIndex !== -1) {
        musicData.splice(trackIndex, 1);
        musicDisplay.render();
    }
}

// Export functions for external use (if needed)
window.MusicManager = {
    addTrack: addNewTrack,
    updateTrack: updateTrack,
    removeTrack: removeTrack,
    getTracks: () => [...musicData]
};