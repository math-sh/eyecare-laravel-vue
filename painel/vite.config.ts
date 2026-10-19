import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue2'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    port: 5173,
    // host + polling para o dev server funcionar tambem dentro do container.
    host: true,
    watch: { usePolling: true },
  },
})
