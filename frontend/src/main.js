import { createApp as createVueApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/router'
import App from './App.vue'

// 全局样式
import '@/assets/styles/common.scss'
import '@/assets/styles/colors.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'font-awesome/css/font-awesome.min.css'

// Bootstrap JS dependencies
import 'jquery'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

export function createApp() {
  const app = createVueApp(App)
  const pinia = createPinia()
  app.use(pinia)
  app.use(router)
  return { app, router }
}

// 客户端挂载
if (!import.meta.env.SSG) {
  const { app } = createApp()
  import('@/stores/modules/user').then(({ useUserStore }) => {
    const userStore = useUserStore()
    userStore.initLoginState()
  })
  app.mount('#app')
}
