import request from '@/utils/request'

/**
 * 聊天相关API
 * 后端路由参考 routes/api.php：
 * - GET    /api/chat/conversations          - 获取会话列表（需登录）
 * - POST   /api/chat/conversations          - 创建或获取会话（需登录）
 * - GET    /api/chat/conversations/{id}/messages - 获取会话消息（需登录）
 * - POST   /api/chat/conversations/{id}/messages - 发送消息（需登录）
 */
export const chatApi = {
  // 1. 获取会话列表
  getConversations: () => {
    return request({
      url: '/api/chat/conversations',
      method: 'get'
    })
  },

  // 2. 创建或获取会话
  createConversation: (params) => {
    console.log('调用createConversation API，参数:', params)
    const data = {
      resource_type: params.resourceType || 'product',
      resource_id: params.resourceId
    }
    
    // 对于商品类型，保持向后兼容
    if (params.resourceType === 'product' || params.productId) {
      data.product_id = params.productId || params.resourceId
    }
    
    return request({
      url: '/api/chat/conversations',
      method: 'post',
      data: data
    }).then(response => {
      console.log('createConversation API响应:', response)
      return response
    }).catch(error => {
      console.error('createConversation API错误:', error)
      throw error
    })
  },

  // 3. 获取会话消息列表
  getMessages: (conversationId) => {
    return request({
      url: `/api/chat/conversations/${conversationId}/messages`,
      method: 'get'
    })
  },

  // 4. 发送消息
  sendMessage: (conversationId, content) => {
    return request({
      url: `/api/chat/conversations/${conversationId}/messages`,
      method: 'post',
      data: {
        content
      }
    })
  }
}

// 为了兼容性，也可以导出默认对象
export default chatApi
