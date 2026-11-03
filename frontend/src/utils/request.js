import axios from 'axios'
import { useUserStore } from '@/stores/modules/user.js'

// 创建Axios实例
const service = axios.create({
  // 开发环境走 Vite 代理：/api 请求由 vite.config.js 转发到 http://127.0.0.1:8080
  baseURL: import.meta.env.VITE_API_BASE_URL || '/',
  timeout: 30000, // 增加超时时间到30秒
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json;charset=utf-8'
  }
})

// 写请求需要的一次性通行证：由后端签发，60 秒内有效，使用一次即失效
const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || '/').replace(/\/$/, '')
const TICKET_URL = `${API_BASE_URL}/api/auth/ticket`
const WRITE_METHODS = ['post', 'put', 'patch', 'delete']
// 这些接口不需要通行证；尤其是 /api/auth/ticket 本身必须跳过，否则会死循环
const TICKET_EXEMPT_URLS = ['/api/auth/login', '/api/auth/register', '/api/auth/captcha', '/api/auth/ticket']

// 使用原生 fetch 获取通行证，绕开 Axios 拦截器，避免递归调用
async function fetchTicket(token) {
  const response = await fetch(TICKET_URL, {
    method: 'GET',
    headers: { Authorization: `Bearer ${token}` },
    credentials: 'include'
  })
  if (!response.ok) {
    throw new Error(`获取通行证失败 (${response.status})`)
  }
  const result = await response.json()
  const ticket = result?.data?.ticket
  if (result?.code !== 200 || !ticket) {
    throw new Error(result?.msg || '获取通行证失败')
  }
  return ticket
}

// 请求拦截器
service.interceptors.request.use(
  async (config) => {
    // 获取token（Pinia使用）
    const userStore = useUserStore()
    const token = userStore.token || localStorage.getItem('token')
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    const method = (config.method || 'get').toLowerCase()
    const url = config.url || ''
    const needsTicket = WRITE_METHODS.includes(method)
      && !TICKET_EXEMPT_URLS.some((path) => url.includes(path))

    // 写请求先换取一次性 Ticket，再携带 X-Ticket 放行目标请求
    if (needsTicket && token) {
      config.headers['X-Ticket'] = await fetchTicket(token)
    }
    
    // 调试用：打印请求信息
    console.log('请求:', config.method.toUpperCase(), config.url, config.data || config.params)
    
    return config
  },
  (error) => {
    if (import.meta.env.MODE === 'development') {
      alert('请求异常，请重试')
    }
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  (response) => {
    // 只在开发环境打印响应信息，减少生产环境的日志
    if (import.meta.env.MODE === 'development') {
      console.log('响应:', response.config.method.toUpperCase(), response.config.url, response.data)
    }
    
    const res = response.data
    
    // 处理后端返回的格式
    // 如果返回的是文件流，直接返回
    if (response.config.responseType === 'blob') {
      return response
    }
    
    // 根据后端实际返回格式处理
    if (res.code === 200) {
      return res
    } else {
      // 业务状态码非200
      // 只在开发环境显示错误信息
      if (import.meta.env.MODE === 'development') {
        alert(res.msg || '请求失败')
      }
      
      // 401未授权，跳转登录
      if (res.code === 401) {
        if (confirm('登录已过期，请重新登录')) {
          const userStore = useUserStore()
          userStore.logout()
          // 使用Vue Router跳转到登录页，避免页面刷新
          const router = window.router
          if (router) {
            router.push('/login')
          } else {
            // 降级方案：如果router不可用，使用window.location
            window.location.href = '/login'
          }
        }
      }
      return Promise.reject(new Error(res.msg || '请求失败'))
    }
  },
  (error) => {
    // 只在开发环境打印详细错误信息
    if (import.meta.env.MODE === 'development') {
      console.error('响应错误:', error)
    }
    
    // 处理 HTTP 状态码
    if (error.response) {
      const status = error.response.status
      const data = error.response.data
      
      switch (status) {
        case 401:
          // 只在开发环境显示，生产环境直接跳转
          if (import.meta.env.MODE === 'development') {
            alert('未授权，请重新登录')
          }
          const userStore = useUserStore()
          userStore.logout()
          // 使用Vue Router跳转到登录页，避免页面刷新
          const router = window.router
          if (router) {
            router.push('/login')
          } else {
            // 降级方案：如果router不可用，使用window.location
            window.location.href = '/login'
          }
          break
        case 403:
          if (import.meta.env.MODE === 'development') {
            alert('没有权限访问')
          }
          break
        case 404:
          if (import.meta.env.MODE === 'development') {
            alert('请求的资源不存在')
          }
          break
        case 422:
          // 验证错误，显示具体信息
          const msg = data?.msg || data?.message || '数据验证失败'
          if (import.meta.env.MODE === 'development') {
            alert(msg)
          }
          break
        case 500:
          if (import.meta.env.MODE === 'development') {
            alert('服务器内部错误')
          }
          break
        default:
          // 只在开发环境显示其他错误
          if (import.meta.env.MODE === 'development') {
            alert(data?.msg || `请求失败 (${status})`)
          }
      }
    } else if (error.request) {
      // 请求发出但没有收到响应
      // 检查是否是登录页面或个人中心页面的请求
      const isAuthRequest = error.config?.url?.includes('/api/auth')
      const isFavoriteRequest = error.config?.url?.includes('/api/favorites')
      const isChatRequest = error.config?.url?.includes('/api/chat')
      
      // 只在开发环境显示连接错误，或者非授权/收藏/聊天相关的请求
      if (import.meta.env.MODE === 'development' && !isAuthRequest && !isFavoriteRequest && !isChatRequest) {
        alert('无法连接到服务器，请检查后端服务是否启动')
      }
    } else {
      // 请求配置出错
      if (import.meta.env.MODE === 'development') {
        alert('请求配置错误: ' + error.message)
      }
    }
    
    return Promise.reject(error)
  }
)

export default service