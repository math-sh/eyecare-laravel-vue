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
    // No bind mount do Docker no Windows os eventos nativos de arquivo sao
    // instaveis (stat retorna EIO), entao usamos polling. O intervalo de 1s
    // reduz a quantidade de stats no filesystem compartilhado — o padrao de
    // 100ms gera eventos fantasma (ex.: ".env changed") que reiniciavam o
    // servidor e derrubavam o watcher com "EIO: i/o error, stat '/app'".
    watch: {
      usePolling: true,
      interval: 1000,
      ignored: ['**/node_modules/**', '**/.git/**', '**/dist/**'],
    },
  },
})
