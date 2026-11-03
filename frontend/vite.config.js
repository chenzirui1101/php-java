import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// Vite 4.5.14 核心配置
export default defineConfig({
  // 插件配置（适配4.5版本）
  plugins: [
    vue()
  ],
  // 路径解析（4.5版本更严格，需完整路径）
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '~': path.resolve(__dirname, './node_modules')
    },
    extensions: ['.vue', '.js', '.jsx', '.json'] // 省略后缀
  },
  // 开发服务器（4.5版本默认配置优化）
  server: {
    host: true, // 等价于 0.0.0.0，允许外部访问
    port: 3000,
    open: true, // 启动后自动打开浏览器
    cors: true,
    // 接口代理（解决跨域）
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8080',
        changeOrigin: true
      }
    }
  },
  // 构建配置（4.5版本编译优化）
  build: {
    outDir: 'dist',
    assetsDir: 'static',
    chunkSizeWarningLimit: 1500,
    minify: 'esbuild', // 4.5版本默认esbuild，更快
    rollupOptions: {
      output: {
        // 分包策略（4.5版本更合理）
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return id.toString().split('node_modules/')[1].split('/')[0].toString()
          }
        },
        chunkFileNames: 'static/js/[name]-[hash].js',
        entryFileNames: 'static/js/[name]-[hash].js',
        assetFileNames: 'static/[ext]/[name]-[hash].[ext]'
      }
    },
    // 4.5版本新增：关闭生产环境sourcemap
    sourcemap: false
  },
  // CSS配置（适配Sass 1.97.3）
  css: {
    preprocessorOptions: {
      scss: {
        // 使用现代Sass API，避免legacy JS API警告
        api: "modern"
      }
    }
  },
  // 优化依赖（4.5版本自动优化，可省略）
  optimizeDeps: {
    include: ['vue', 'vue-router', 'pinia']
  },
  // vite-ssg 配置
  ssgOptions: {
    script: 'async',
    formatting: 'minify',
    mock: true
  }
})