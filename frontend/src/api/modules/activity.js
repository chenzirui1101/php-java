import request from '@/utils/request'

// 活动相关API
export const activityApi = {
  // 发布活动
  publishActivity: (data) => {
    return request({
      url: '/api/activities',
      method: 'post',
      data
    })
  },

  // 更新活动
  updateActivity: (id, data) => {
    return request({
      url: `/api/activities/${id}`,
      method: 'put',
      data
    })
  },

  // 删除活动
  deleteActivity: (id) => {
    return request({
      url: `/api/activities/${id}`,
      method: 'delete'
    })
  },

  // 获取活动列表
  getActivities: (params) => {
    return request({
      url: '/api/activities',
      method: 'get',
      params
    })
  },

  // 获取活动详情
  getActivityDetail: (id) => {
    return request({
      url: `/api/activities/${id}`,
      method: 'get'
    })
  },

  // 获取用户发布的活动
  getMyActivities: (params) => {
    return request({
      url: '/api/activities/my/list',
      method: 'get',
      params
    })
  },

  // 获取活动分类
  getActivityCategories: () => {
    return request({
      url: '/api/activities/categories',
      method: 'get'
    })
  }
}