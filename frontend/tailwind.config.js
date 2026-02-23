/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1e1e1e',
        secondary: '#00ff88',
        accent: '#60a5fa',
      },
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
