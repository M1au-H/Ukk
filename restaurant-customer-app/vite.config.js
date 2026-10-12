import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// host: true -> bisa dibuka dari HP lewat IP laptop (untuk test scan QR sungguhan)
export default defineConfig({
  plugins: [vue()],
  server: { host: true, port: 5173 },
})
