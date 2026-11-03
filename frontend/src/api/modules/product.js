import request from '@/utils/request'

/**
 * 商品相关API
 * 后端路由参考 routes/api.php：
 * - GET    /api/products          - 商品列表（公开）
 * - GET    /api/products/{id}     - 商品详情（公开）
 * - POST   /api/products/publish   - 发布商品（需登录）
 * - DELETE /api/products/{id}      - 删除商品（需登录）
 * - GET    /api/products/my        - 我的商品（需登录）
 */
export const productApi = {
  // 1. 获取商品列表（支持分页/分类筛选）- 公开接口
  getProductList: (params) => {
    return request({
      url: '/api/products',
      method: 'get',
      params
    })
  },

  // 2. 获取商品详情 - 公开接口
  getProductDetail: (id) => {
    return request({
      url: `/api/products/${id}`,
      method: 'get'
    })
  },

  // 3. 发布商品 - 需要登录
  publishProduct: (data) => {
    return request({
      url: '/api/products',
      method: 'post',
      data,
      // 需要认证，会自动添加 Authorization 头
      headers: {
        'Content-Type': 'application/json'
      }
    })
  },

  // 4. 删除我的商品 - 需要登录
  deleteProduct: (id) => {
    return request({
      url: `/api/products/${id}`,
      method: 'delete'
    })
  },

  // 5. 获取我的商品列表 - 需要登录
  getMyProducts: () => {
    return request({
      url: '/api/products/my/list',
      method: 'get'
    })
  },

  // 6. 更新商品（如果需要）- 根据你的后端路由，可能需要添加
  updateProduct: (id, data) => {
    return request({
      url: `/api/products/${id}`,
      method: 'put',  // 或者 'post'，取决于后端实现
      data
    })
  }
}

// 为了兼容性，也可以导出默认对象
export default productApi