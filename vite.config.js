import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  root: './',
  base: '/',
  publicDir: 'public',
  server: {
    port: 5174,
    middlewareMode: false,
    proxy: {
      '/api': {
        // Déterminer le target selon l'environnement
        // - Si DOCKER_DEV=true : localhost:8080 (serveur PHP local Docker)
        // - Sinon : https://fcchiche.fr (OVH production)
        target: process.env.DOCKER_DEV === 'true'
          ? 'http://localhost:8080'
          : 'https://fcchiche.fr',
        changeOrigin: true,
        rewrite: (path) => path, // Garde le chemin /api intact
        secure: false, // Pour éviter les erreurs SSL en dev
      }
    }
  },
  build: {
    outDir: 'public/dist',
    emptyOutDir: true,
    minify: 'terser',
    target: 'es2020',
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom', 'react-router-dom'],
        }
      }
    },
    chunkSizeWarningLimit: 500,
  },
  define: {
    'process.env.VITE_API_BASE': JSON.stringify(process.env.VITE_API_BASE || '/api'),
    'process.env.VITE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
  }
})
