import request from '@/utils/request'

/**
 * 论坛相关API
 * 后端路由参考：
 * - GET    /api/forum/topics          - 获取话题列表（不需要登录）
 * - POST   /api/forum/topics          - 创建话题（需要登录）
 * - GET    /api/forum/topics/{id}     - 获取话题详情（不需要登录）
 * - POST   /api/forum/topics/{id}/replies - 添加回复（需要登录）
 * - GET    /api/forum/hot-topics      - 获取热门话题（不需要登录）
 * - GET    /api/forum/active-users    - 获取活跃用户（不需要登录）
 */
export const forumApi = {
  // 1. 获取话题列表
  getTopics: (params) => {
    return request({
      url: '/api/forum/topics',
      method: 'get',
      params
    })
  },

  // 2. 创建话题
  createTopic: (data) => {
    return request({
      url: '/api/forum/topics',
      method: 'post',
      data
    })
  },

  // 3. 获取话题详情
  getTopicDetail: (id) => {
    return request({
      url: `/api/forum/topics/${id}`,
      method: 'get'
    })
  },

  // 4. 获取话题评论
  getComments: (id) => {
    return request({
      url: `/api/forum/topics/${id}/replies`,
      method: 'get'
    })
  },

  // 5. 添加评论
  addComment: (topicId, data) => {
    return request({
      url: `/api/forum/topics/${topicId}/replies`,
      method: 'post',
      data: {
        content: data.content
      }
    })
  },

  // 6. 获取热门话题
  getHotTopics: () => {
    return request({
      url: '/api/forum/hot-topics',
      method: 'get'
    })
  },

  // 7. 获取活跃用户
  getActiveUsers: () => {
    return request({
      url: '/api/forum/active-users',
      method: 'get'
    })
  },

  // 8. 点赞话题
  likeTopic: (id) => {
    return request({
      url: `/api/forum/topics/${id}/like`,
      method: 'post'
    })
  },

  // 9. 收藏话题
  favoriteTopic: (id) => {
    return request({
      url: `/api/forum/topics/${id}/favorite`,
      method: 'post'
    })
  },

  // 10. 获取推荐话题（点赞数前五）
  getRecommendations: () => {
    return request({
      url: '/api/forum/recommendations',
      method: 'get'
    })
  },

  // 11. 获取话题导航（上下篇）
  getTopicNavigation: (id) => {
    return request({
      url: `/api/forum/topics/${id}/navigation`,
      method: 'get'
    })
  },

  // 12. 获取用户对话题的状态（点赞、收藏）
  getUserTopicStatus: (id) => {
    return request({
      url: `/api/forum/topics/${id}/user-status`,
      method: 'get'
    })
  },

  // 13. 获取用户收藏的话题列表
  getUserFavorites: () => {
    return request({
      url: '/api/forum/favorites',
      method: 'get'
    })
  }
}

// 为了兼容性，也可以导出默认对象
export default forumApi