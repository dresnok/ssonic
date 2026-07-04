import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

const buildId = new Date().toISOString()

export default defineConfig({
  plugins: [vue()],

  define: {
    __APP_BUILD_ID__: JSON.stringify(buildId)
  },

  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    },
  },

  server: {
    watch: {
      usePolling: true,
      interval: 500
    },

    proxy: {
      '/src_my_source/': {
        target: 'http://FASJOLA',
        changeOrigin: true,
        secure: false,
        rewrite: (path) => '/vue-ssonic' + path
      },
    }
  }
})