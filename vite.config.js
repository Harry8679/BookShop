import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    rollupOptions: {
      input: './assets/app.js', // Point d’entrée
    },
  },
})
