import { defineStore } from 'pinia'
import { loginApi, getUserInfoApi, checkTokenApi } from '@/api/modules/user.js'
import { setStorage, getStorage, removeStorage } from '@/utils/storage.js'

// 用户状态管理
export const useUserStore = defineStore('user', {
  state: () => ({
    token: getStorage('token') || '',
    userInfo: getStorage('userInfo') || {},
    lastCheckTime: 0, // 记录上次检查token的时间
    checkInterval: 30 * 60 * 1000, // token检查间隔：30分钟
    unreadMessageCount: 0 // 未读消息数量
  }),
  getters: {
    // 计算属性：是否已登录
    isLogin() {
      return !!this.token && !!this.userInfo.id
    },
    // 计算属性：是否是管理员
    isAdmin() {
      return this.userInfo.role === 'admin'
    }
  },
  actions: {
    // 初始化登录状态监听
    initLoginState() {
      // 监听localStorage变化，实现多标签页同步
      window.addEventListener('storage', this.handleStorageChange)
      
      // 定期检查token有效性
      this.startTokenCheck()
    },
    
    // 停止登录状态监听
    stopLoginState() {
      window.removeEventListener('storage', this.handleStorageChange)
      if (this.tokenCheckTimer) {
        clearInterval(this.tokenCheckTimer)
      }
    },
    
    // 处理localStorage变化
    handleStorageChange(event) {
      if (event.key === 'token' || event.key === 'userInfo') {
        if (event.newValue) {
          // 其他标签页登录，同步状态
          if (event.key === 'token') {
            this.token = event.newValue
          } else {
            this.userInfo = JSON.parse(event.newValue)
          }
        } else {
          // 其他标签页退出登录，同步状态
          this.token = ''
          this.userInfo = {}
        }
      }
    },
    
    // 开始定期检查token
    startTokenCheck() {
      this.tokenCheckTimer = setInterval(async () => {
        await this.checkTokenValidity()
      }, this.checkInterval)
    },
    
    // 检查token有效性
    async checkTokenValidity() {
      if (!this.token) return
      
      const now = Date.now()
      // 避免频繁检查
      if (now - this.lastCheckTime < this.checkInterval / 2) return
      
      try {
        this.lastCheckTime = now
        // 调用后端API检查token有效性
        await checkTokenApi()
      } catch (error) {
        // token无效或过期，自动退出登录
        this.logout()
      }
    },
    
    // 登录
    async login(loginForm) {
      const res = await loginApi(loginForm)
      this.token = res.data.token
      // 存储token（Vite 4.5兼容localStorage）
      setStorage('token', res.data.token)
      // 存储用户信息
      this.userInfo = res.data.user
      setStorage('userInfo', res.data.user)
      return res
    },
    
    // 获取用户信息
    async getUserInfo() {
      const res = await getUserInfoApi()
      this.userInfo = res.data
      setStorage('userInfo', res.data)
      return res
    },
    
    // 退出登录
    logout() {
      this.token = ''
      this.userInfo = {}
      this.unreadMessageCount = 0
      removeStorage('token')
      removeStorage('userInfo')
      // 跳转首页
      const router = window.router
      if (router) {
        router.push('/')
      } else {
        window.location.href = '/'
      }
    },
    
    // 更新未读消息数量
    updateUnreadMessageCount(count) {
      this.unreadMessageCount = count
    }
  }
})
