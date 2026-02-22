# Anmi Beatz - AnmiBeatzMusic

A modern, responsive music website for Anmi Beatz (Jake Baranda), showcasing music production work and beats. Built for **anmibeatz.com**.

## Features

- Modern, responsive design
- Spotify-like music showcase section
- Social media integration
- Smooth scrolling navigation
- Mobile-friendly layout

## How to Use

1. Clone this repository to your local machine
2. Customize the content in the following files:
   - `index.html`: Update your personal information and content
   - `script.js`: Add your music tracks and social media links
   - `styles.css`: Customize colors and styling if desired

### Adding Your Music

To add your music tracks, edit the `musicData` array in `script.js`. For each track, provide:

```javascript
{
    title: "Your Track Title",
    description: "Track Description",
    image: "URL to track artwork",
    youtubeLink: "YouTube video URL",
    spotifyLink: "Spotify track URL"
}
```

### Updating Social Media Links

Update your social media links in the `socialLinks` object in `script.js`:

```javascript
const socialLinks = {
    spotify: "YOUR_SPOTIFY_PROFILE_LINK",
    youtube: "YOUR_YOUTUBE_CHANNEL_LINK",
    instagram: "YOUR_INSTAGRAM_PROFILE_LINK",
    soundcloud: "YOUR_SOUNDCLOUD_PROFILE_LINK"
};
```

## Deployment

This website is designed to be hosted on GitHub Pages. To deploy:

1. Create a new repository on GitHub
2. Push this code to your repository
3. Go to repository settings
4. Under "GitHub Pages", select the main branch as source
5. Your site will be published at `https://yourusername.github.io/repository-name`

## Customization

### Colors

The website uses a Spotify-inspired color scheme. You can customize the colors by editing the CSS variables in `styles.css`:

```css
:root {
    --primary-color: #1DB954;
    --secondary-color: #191414;
    --text-color: #ffffff;
    --background-color: #121212;
    --accent-color: #1ed760;
}
```

### Images

Replace the placeholder images with your own:
1. Add your images to an `images` folder
2. Update the image paths in the HTML and JavaScript files

## Browser Support

The website is compatible with:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is open source and available under the MIT License. 