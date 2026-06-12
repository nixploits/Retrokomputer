import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  build: {
    chunkSizeWarningLimit: 600,
    rollupOptions: {
      output: {
        manualChunks: {
          apexcharts: ['apexcharts', 'vue3-apexcharts'],
        },
      },
    },
  },
  server: {
    port: 5173,
    host: '0.0.0.0', // Allow access from all IPs (localhost, 127.0.0.1, 192.168.x.x, etc)
    proxy: {
      '/api': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
      '/storage': {
        target: 'http://backend:8000',
        changeOrigin: true,
      },
    },
  },
})
