import request from '@/utils/request'

/**
 * 内容审核管理API
 */
export default {
  /**
   * 获取待审核内容列表
   * @param {Object} params - 查询参数
   * @param {string} params.type - 内容类型: all, product, topic, comment, lostfound
   * @param {number} params.page - 页码
   * @param {number} params.pageSize - 每页数量
   */
  getPendingList(params = {}) {
    return request({
      url: '/api/admin/moderation/pending',
      method: 'get',
      params
    })
  },

  /**
   * 审核通过
   * @param {string} type - 内容类型: product, topic, comment, lostfound
   * @param {number} id - 内容ID
   */
  approve(type, id) {
    return request({
      url: `/api/admin/moderation/approve/${type}/${id}`,
      method: 'post'
    })
  },

  /**
   * 审核拒绝
   * @param {string} type - 内容类型: product, topic, comment, lostfound
   * @param {number} id - 内容ID
   * @param {string} reason - 拒绝原因
   */
  reject(type, id, reason = '内容违规') {
    return request({
      url: `/api/admin/moderation/reject/${type}/${id}`,
      method: 'post',
      data: { reason }
    })
  },

  /**
   * 获取审核日志
   */
  getLogs() {
    return request({
      url: '/api/admin/moderation/logs',
      method: 'get'
    })
  }
}
