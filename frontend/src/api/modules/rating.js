import request from '@/utils/request'

/**
 * 评价相关API
 * 后端路由参考 routes/api.php：
 * - GET    /api/products/{productId}/ratings          - 获取商品评价列表（公开）
 * - POST   /api/ratings                              - 创建评价（需登录）
 * - GET    /api/ratings/user                         - 获取用户评价列表（需登录）
 * - PUT    /api/ratings/{id}                         - 更新评价（需登录）
 * - DELETE /api/ratings/{id}                         - 删除评价（需登录）
 */
export const ratingApi = {
  // 1. 获取商品评价列表
  getProductRatings: (productId, params) => {
    return request({
      url: `/api/products/${productId}/ratings`,
      method: 'get',
      params
    })
  },

  // 2. 创建评价
  createRating: (data) => {
    return request({
      url: '/api/ratings',
      method: 'post',
      data
    })
  },

  // 3. 获取用户评价列表
  getUserRatings: (params) => {
    return request({
      url: '/api/ratings/user',
      method: 'get',
      params
    })
  },

  // 4. 更新评价
  updateRating: (id, data) => {
    return request({
      url: `/api/ratings/${id}`,
      method: 'put',
      data
    })
  },

  // 5. 删除评价
  deleteRating: (id) => {
    return request({
      url: `/api/ratings/${id}`,
      method: 'delete'
    })
  }
}

// 为了兼容性，也可以导出默认对象
export default ratingApi