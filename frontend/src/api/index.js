// 统一导出所有模块的API，便于维护
export * from './modules/user.js'

import moderationApi from './modules/moderation.js'
export { moderationApi }

import { activityApi } from './modules/activity.js'
export { activityApi }

import albumApi from './modules/album.js'
export { albumApi }
