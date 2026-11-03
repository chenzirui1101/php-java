import request from '@/utils/request'

/**
 * DIY纪念册相关API
 * 后端路由（Laravel routes/api.php）：
 * - POST   /api/albums        - 保存纪念册（需登录）
 * - GET    /api/albums        - 我的纪念册列表（需登录）
 * - GET    /api/albums/{id}   - 纪念册详情，用于二次编辑（需登录）
 *
 * 说明：统一携带 Accept: application/json，避免受保护接口返回 302 重定向
 */
const jsonHeaders = {
  Accept: 'application/json',
  'Content-Type': 'application/json'
}

export const albumApi = {
  /**
   * 保存纪念册
   * @param {Object} data
   * @param {string} data.title        纪念册标题
   * @param {string} data.cover_image  合成图片的 Base64（data:image/png;base64,xxx）
   * @param {string|Object} data.canvas_data 画布元素数据（JSON 字符串或对象）
   * @param {number} data.width        画布逻辑宽度
   * @param {number} data.height       画布逻辑高度
   */
  saveAlbum: (data) => {
    return request({
      url: '/api/albums',
      method: 'post',
      data,
      headers: jsonHeaders
    })
  },

  // 我的纪念册列表
  getAlbums: (params) => {
    return request({
      url: '/api/albums',
      method: 'get',
      params,
      headers: jsonHeaders
    })
  },

  // 纪念册详情（二次编辑）
  getAlbum: (id) => {
    return request({
      url: `/api/albums/${id}`,
      method: 'get',
      headers: jsonHeaders
    })
  }
}

export default albumApi
