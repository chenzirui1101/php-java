import request from '@/utils/request'

/**
 * 失物招领相关API
 * 后端路由参考 routes/api.php：
 * - GET    /api/lostfound          - 获取失物招领列表（无需登录）
 * - GET    /api/lostfound/{id}     - 获取失物招领详情（无需登录）
 */

// 1. 获取失物招领列表
export const getLostFoundList = (params) => {
  return request({
    url: '/api/lostfound',
    method: 'get',
    params: params
  })
}

// 2. 获取失物招领详情
export const getLostFoundById = (id) => {
  return request({
    url: `/api/lostfound/${id}`,
    method: 'get'
  })
}

// 导出API对象，保持兼容性
export const lostFoundApi = {
  getLostFoundList,
  getLostFoundById
}

// 为了兼容性，也可以导出默认对象
export default lostFoundApi