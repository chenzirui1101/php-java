import request from '@/utils/request'

/**
 * 收藏相关API
 * 后端路由参考 routes/api.php：
 * - GET    /api/favorites          - 获取用户收藏列表（需登录）
 * - POST   /api/favorites          - 添加收藏（需登录）
 * - DELETE /api/favorites/{productId} - 取消收藏（需登录）
 * - GET    /api/favorites/check/{productId} - 检查商品是否已收藏（需登录）
 * - POST   /api/favorites/check/batch - 批量检查商品是否已收藏（需登录）
 */
export const favoriteApi = {
  // 1. 获取用户收藏列表
  getFavoriteList: (params) => {
    return request({
      url: '/api/favorites',
      method: 'get',
      params
    })
  },

  // 2. 添加收藏
  addFavorite: (productId) => {
    return request({
      url: '/api/favorites',
      method: 'post',
      data: { product_id: productId }
    })
  },

  // 3. 取消收藏
  cancelFavorite: (productId) => {
    return request({
      url: `/api/favorites/${productId}`,
      method: 'delete'
    })
  },

  // 4. 检查商品是否已收藏
  checkFavorite: (productId) => {
    return request({
      url: `/api/favorites/check/${productId}`,
      method: 'get'
    })
  },
  
  // 5. 批量检查商品是否已收藏
  batchCheckFavorite: (productIds) => {
    return request({
      url: '/api/favorites/check/batch',
      method: 'post',
      data: { product_ids: productIds }
    })
  }
}

// 为了兼容性，也可以导出默认对象
export default favoriteApi