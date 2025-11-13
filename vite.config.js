import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  root: './',
  base: '/',
  publicDir: 'static',  // Assets statiques uniquement (manifest, SW, images)
  server: {
    port: 5174,
    middlewareMode: false,
    proxy: {
      '/api': {
        target: process.env.DOCKER_DEV === 'true'
          ? 'http://localhost:8080'
          : 'https://fcchiche.fr',
        changeOrigin: true,
        rewrite: (path) => path,
        secure: false,
      }
    }
  },
  build: {
    outDir: 'dist',      // Build React à la racine
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
