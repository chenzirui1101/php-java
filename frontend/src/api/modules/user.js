import request from '@/utils/request'

/**
 * 用户相关接口 - 适配后端 Laravel 路由
 * 后端路由参考 routes/api.php：
 * - GET    /api/auth/captcha    - 获取验证码
 * - POST   /api/auth/login       - 登录
 * - POST   /api/auth/logout      - 登出
 * - GET    /api/auth/me          - 获取用户信息
 */

// 获取验证码
export const getCaptchaApi = () => {
  return request({
    url: '/api/auth/captcha',
    method: 'get'
  })
}

// 登录
export const loginApi = (data) => {
  return request({
    url: '/api/auth/login',
    method: 'post',
    data
  })
}

// 获取用户信息
export const getUserInfoApi = () => {
  return request({
    url: '/api/auth/me',
    method: 'get'
  })
}

// 退出登录
export const logoutApi = () => {
  return request({
    url: '/api/auth/logout',
    method: 'post'
  })
}

// 检查token有效性
export const checkTokenApi = () => {
  return request({
    url: '/api/auth/check-token',
    method: 'get'
  })
}

// 导出所有API方便使用
export default {
  getCaptcha: getCaptchaApi,
  login: loginApi,
  getUserInfo: getUserInfoApi,
  logout: logoutApi,
  checkToken: checkTokenApi
}