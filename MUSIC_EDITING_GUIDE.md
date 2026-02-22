# 🎵 Music Editing Guide

## 📁 File Structure
- **`music-data.js`** - Contains all your music tracks (EASY TO EDIT!)
- **`script.js`** - Main functionality code
- **`index.html`** - Website structure
- **`styles.css`** - Styling

## ✏️ How to Edit Music Tracks

### 1. Open `music-data.js`
This file contains all your music tracks in an easy-to-read format.

### 2. Edit Track Information
Each track has these properties you can modify:

```javascript
{
    id: 1,                                    // Unique ID (don't change)
    title: "Space Cake",                      // Song title
    artist: "SAJKA",                          // Artist name
    genre: "hip-hop",                         // Genre
    image: "assets/images/music-covers/Space Cake.jpg",  // Album cover image
    gifPreview: "assets/images/previews/Space Cake.gif", // GIF for hover effect
    youtubeLink: "https://www.youtube.com/watch?v=Z4LVd1u30pg", // YouTube link
    spotifyLink: "https://open.spotify.com/track/YOUR_TRACK_ID", // Spotify link
    releaseDate: "2024-01-15",               // Release date (YYYY-MM-DD)
    featured: true,                          // Show in featured section (true/false)
    plays: 125000,                           // Play count
    duration: "3:45"                         // Song duration
}
```

### 3. Add New Tracks
To add a new track, copy an existing track object and:
- Change the `id` to the next number
- Update all the information
- Make sure the image and GIF files exist in the correct folders

### 4. Remove Tracks
Simply delete the entire track object (including the comma after it).

### 5. Change Images/GIFs
- **Album Cover**: Put your image in `assets/images/music-covers/` and update the `image` path
- **GIF Preview**: Put your GIF in `assets/images/previews/` and update the `gifPreview` path

## 🎯 Quick Tips
- **Featured tracks** appear in the "Featured" section at the top
- **Genres** are used for filtering (hip-hop, trap, rap, r&b, instrumental)
- **YouTube links** should be the full watch URL
- **Spotify links** need the actual track ID (replace YOUR_TRACK_ID)
- **Release dates** should be in YYYY-MM-DD format

## 🔄 After Editing
1. Save the `music-data.js` file
2. Refresh your browser
3. Your changes will appear immediately!

## 📂 File Locations
- Album covers: `assets/images/music-covers/`
- GIF previews: `assets/images/previews/`
- Music data: `music-data.js` (this file!)
