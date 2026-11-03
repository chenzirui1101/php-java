<template>
  <div class="container py-8">
    <div class="d-flex justify-content-between align-items-center mb-8">
      <h1 class="h1 font-bold m-0">&nbsp;</h1>
    </div>
    <div class="row">
      <!-- 左侧栏目分类 - 桌面端 -->
      <div class="col-md-2 d-none d-md-block">
        <div class="category-card">
          <h2 class="h5 font-semibold mb-0 px-4 py-3 category-header">栏目分类</h2>
          <div class="category-list">
            <button 
              v-for="category in categories" 
              :key="category.id"
              @click="handleCategoryChange(category.id)"
              class="category-item"
              :class="currentCategory === category.id ? 'active' : ''"
            >
              {{ category.name }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- 栏目分类 - 移动端 -->
      <div class="col-12 d-md-none mb-4">
        <div class="category-mobile">
          <div class="category-scroll">
            <button 
              v-for="category in categories" 
              :key="category.id"
              @click="handleCategoryChange(category.id)"
              class="category-mobile-item"
              :class="currentCategory === category.id ? 'active' : ''"
            >
              {{ category.name }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- 中间内容区 -->
      <div class="col-md-7">
        <!-- 搜索框和排序按钮 -->
        <div class="card mb-4">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex">
                <button 
                  @click="sortBy = 'hot'; handleSortChange()"
                  class="sort-btn"
                  :class="sortBy === 'hot' ? 'active' : ''"
                >
                  推荐
                </button>
                <button 
                  @click="sortBy = 'new'; handleSortChange()"
                  class="sort-btn"
                  :class="sortBy === 'new' ? 'active' : ''"
                >
                  最新
                </button>
              </div>
              <div class="search-wrapper">
                <input 
                  type="text" 
                  v-model="searchKeyword"
                  @input="handleSearch"
                  class="search-input" 
                  placeholder="搜索话题..."
                >
                <button 
                  class="search-btn"
                  @click="handleSearch"
                >
                  <i class="fa fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- 话题列表 -->
        <div class="card mb-6">
          <div class="card-body">
            <div v-if="loading.topics" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">加载话题中...</p>
            </div>
            <div v-else-if="filteredTopics.length === 0" class="text-center py-8">
              <i class="fa fa-comments-o" style="font-size: 3rem; color: #e9ecef;"></i>
              <h4 class="mt-3 text-muted">暂无话题</h4>
              <p class="text-muted">成为第一个发布话题的人吧！</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="topic in displayTopics" :key="topic.id" class="border-bottom pb-4">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge badge-secondary mr-2">{{ getCategoryName(topic.category_id) }}</span>
                    <h3 
                      class="font-medium text-primary-hover cursor-pointer mt-1 truncate-title"
                      @click="goToTopicDetail(topic.id)"
                      :title="topic.title"
                    >
                      {{ truncateTitle(topic.title) }}
                    </h3>
                  </div>
                  <div class="d-flex flex-column align-items-end">
                    <span class="text-sm text-muted">{{ topic.likes_count || 0 }} <i class="fa fa-heart"></i></span>
                    <span class="text-sm text-muted">{{ topic.replies_count || 0 }} <i class="fa fa-comment"></i></span>
                  </div>
                </div>
                <p class="text-gray-600 mt-2 line-clamp-2">{{ topic.content }}</p>
                <div class="d-flex align-items-center text-sm text-muted mt-2">
                  <div class="avatar-mini">
                    {{ (topic.author.username || '匿名用户').charAt(0).toUpperCase() }}
                  </div>
                  <span class="ml-2">{{ topic.author.username || '匿名用户' }}</span>
                  <span class="mx-2">·</span>
                  <span>{{ formatTime(topic.created_at) }}</span>
                </div>
              </div>
            </div>
            
            <!-- 加载更多按钮 -->
            <div v-if="hasMore && !loading.loadingMore" class="text-center py-4">
              <button 
                @click="loadMore"
                class="btn btn-outline-secondary rounded-lg px-6 py-2"
              >
                <i class="fa fa-refresh mr-2"></i>加载更多
              </button>
            </div>
            <div v-if="loading.loadingMore" class="text-center py-4">
              <div class="spinner-border text-primary" role="status" style="width: 24px; height: 24px;"></div>
              <span class="ml-2 text-muted">加载中...</span>
            </div>
            <div v-if="!hasMore && filteredTopics.length > 0" class="text-center py-4 text-muted">
              已加载全部话题
            </div>
          </div>
        </div>
      </div>
      
      <!-- 右侧边栏 - 仅桌面端显示 -->
      <div class="col-md-3 d-none d-md-block">
        <div class="card mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h2 class="h5 font-semibold mb-0">热门话题榜</h2>
              <a href="#" class="text-primary-theme text-sm">查看更多</a>
            </div>
            <div v-if="loading.hotTopics" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">加载中...</p>
            </div>
            <div v-else-if="hotTopics.length === 0" class="text-center py-4 text-muted">
              暂无热门话题
            </div>
            <div v-else class="space-y-3">
              <div v-for="(topic, index) in hotTopics" :key="topic.id" class="d-flex align-items-center" style="white-space: nowrap;">
                <span 
                  class="rank-badge"
                  :class="index < 3 ? 'top-three' : ''"
                >{{ index + 1 }}</span>
                <span 
                  class="ml-2 text-sm text-primary-hover cursor-pointer truncate-text"
                  @click="goToTopicDetail(topic.id)"
                >
                  {{ topic.title }}
                </span>
                <span class="text-sm text-muted ml-2 flex-shrink-0">{{ topic.replies_count || 0 }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <h2 class="h5 font-semibold mb-4">活跃用户</h2>
            <div v-if="loading.activeUsers" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">加载中...</p>
            </div>
            <div v-else-if="activeUsers.length === 0" class="text-center py-4 text-muted">
              暂无活跃用户
            </div>
            <div v-else class="space-y-4">
              <div v-for="user in activeUsers" :key="user.id" class="d-flex align-items-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px; font-weight: bold; background: linear-gradient(135deg, #FF6B9D, #FFD93D); color: white; line-height: 32px; text-align: center;">
                  {{ user.username.charAt(0).toUpperCase() }}
                </div>
                <span class="ml-2 text-sm">{{ user.username }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 发布话题模态框 -->
  <div v-if="showPublishModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" style="background: white; border-radius: 16px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2); max-width: 500px; width: 90%;">
      <div class="bg-gradient-to-r from-orange-500 to-yellow-500 text-white rounded-t-2xl p-4" style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); color: white; border-radius: 16px 16px 0 0; padding: 16px;">
        <div class="flex justify-between items-center" style="display: flex; justify-content: space-between; align-items: center;">
          <h5 class="font-bold text-lg" style="font-weight: bold; font-size: 18px;">发布新话题</h5>
          <button type="button" class="text-white hover:text-gray-200 rounded-full w-8 h-8 flex items-center justify-center hover:bg-gradient-to-r hover:from-orange-500 hover:to-yellow-500 transition-all" @click="showPublishModal = false" style="color: white; cursor: pointer; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #FF6B9D, #FFD93D); transition: all 0.3s ease; border: none; outline: none;">
            <i class="fa fa-times"></i>
          </button>
        </div>
      </div>
      <div class="p-4" style="padding: 16px;">
        <form @submit.prevent="handlePublish">
          <div class="mb-4" style="margin-bottom: 16px;">
            <label for="topicCategory" class="block font-medium text-gray-700 mb-1" style="display: block; font-weight: 500; color: #4B5563; margin-bottom: 4px;">选择分类</label>
            <select 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-orange-500 focus:ring focus:ring-orange-200"
              id="topicCategory" 
              v-model="topicForm.category_id" 
              required
              style="width: 100%; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px;"
            >
              <option value="" disabled>请选择栏目分类</option>
              <option v-for="category in categories.filter(c => c.id !== 'all')" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>
          <div class="mb-4" style="margin-bottom: 16px;">
            <label for="topicTitle" class="block font-medium text-gray-700 mb-1" style="display: block; font-weight: 500; color: #4B5563; margin-bottom: 4px;">标题</label>
            <input 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-orange-500 focus:ring focus:ring-orange-200"
              id="topicTitle" 
              v-model="topicForm.title" 
              placeholder="请输入话题标题"
              required
              style="width: 100%; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px;"
            >
          </div>
          <div class="mb-4" style="margin-bottom: 16px;">
            <label for="topicContent" class="block font-medium text-gray-700 mb-1" style="display: block; font-weight: 500; color: #4B5563; margin-bottom: 4px;">内容</label>
            <textarea 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-orange-500 focus:ring focus:ring-orange-200"
              id="topicContent" 
              v-model="topicForm.content" 
              rows="6"
              placeholder="请输入话题内容"
              required
              style="width: 100%; padding: 8px 12px; border: 1px solid #D1D5DB; border-radius: 6px; resize: vertical;"
            ></textarea>
          </div>
          <div class="flex justify-end space-x-3 pt-2" style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px;">
            <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50" @click="showPublishModal = false" style="padding: 6px 16px; border: 1px solid #D1D5DB; border-radius: 6px; color: #4B5563; background: white; cursor: pointer;">
              取消
            </button>
            <button 
              type="submit" 
              class="btn-orange px-4 py-2 rounded-lg"
              :disabled="publishing"
              style="padding: 6px 16px; border: none; border-radius: 6px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); color: white; cursor: pointer;"
            >
              <span v-if="publishing">
                <i class="fa fa-spinner fa-spin mr-2"></i>发布中...
              </span>
              <span v-else>
                <i class="fa fa-paper-plane mr-2"></i>发布
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- 页脚 -->
  <SiteFooter />
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import SiteFooter from '@/components/SiteFooter.vue'
import { forumApi } from '@/api/modules/forum'

// 路由和导航
const router = useRouter()
const route = useRoute()

// 数据状态
const topics = ref([])
const hotTopics = ref([])
const activeUsers = ref([])

// 分页相关状态
const currentPage = ref(1)
const pageSize = ref(5)
const total = ref(0)
const hasMore = ref(true)

// 加载状态
const loading = ref({
  topics: false,
  hotTopics: false,
  activeUsers: false,
  loadingMore: false
})

// 发布话题相关状态
const showPublishModal = ref(false)
const topicForm = ref({
  category_id: '',
  title: '',
  content: ''
})
const publishing = ref(false)

// 排序方式：hot（推荐/点赞数）, new（最新/发布时间）
const sortBy = ref('hot')

// 搜索关键词
const searchKeyword = ref('')

// 当前选中的分类
const currentCategory = ref('all')

// 栏目分类数据
const categories = ref([
  { id: 'all', name: '综合', icon: 'fa fa-compass' },
  { id: '1', name: '校园趣事', icon: 'fa fa-smile-o' },
  { id: '2', name: '校园闲聊', icon: 'fa fa-comments' },
  { id: '3', name: '学习交流', icon: 'fa fa-book' },
  { id: '4', name: '创意分享', icon: 'fa fa-lightbulb-o' },
  { id: '5', name: '求职招聘', icon: 'fa fa-briefcase' }
])

// 检查用户是否登录
const isLoggedIn = computed(() => {
  return !!localStorage.getItem('token')
})

// 过滤后的话题列表
const filteredTopics = computed(() => {
  let result = [...topics.value]
  
  // 按分类过滤
  if (currentCategory.value !== 'all') {
    result = result.filter(topic => String(topic.category_id) === currentCategory.value)
  }
  
  // 按关键词搜索（模糊搜索）
  if (searchKeyword.value.trim()) {
    const keyword = searchKeyword.value.toLowerCase()
    result = result.filter(topic => 
      topic.title.toLowerCase().includes(keyword) || 
      topic.content.toLowerCase().includes(keyword) ||
      (topic.author && topic.author.username && topic.author.username.toLowerCase().includes(keyword))
    )
  }
  
  // 排序：推荐按点赞数，最新按发布时间
  if (sortBy.value === 'hot') {
    result.sort((a, b) => (b.likes_count || 0) - (a.likes_count || 0))
  } else {
    result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  }
  
  return result
})

// 当前显示的话题列表
const displayTopics = computed(() => {
  return filteredTopics.value.slice(0, currentPage.value * pageSize.value)
})

// 获取分类名称
const getCategoryName = (categoryId) => {
  const category = categories.value.find(c => String(c.id) === String(categoryId))
  return category ? category.name : '其他'
}

// 搜索
const handleSearch = () => {
  currentPage.value = 1
}

// 处理登录
const handleLogin = () => {
  const event = new CustomEvent('show-login-modal')
  window.dispatchEvent(event)
}

// 处理发布话题
const handlePublish = async () => {
  if (!topicForm.value.category_id) {
    alert('请选择栏目分类')
    return
  }
  if (!topicForm.value.title.trim() || !topicForm.value.content.trim()) {
    alert('请填写标题和内容')
    return
  }

  try {
    publishing.value = true
    const res = await forumApi.createTopic({
      category_id: parseInt(topicForm.value.category_id),
      title: topicForm.value.title,
      content: topicForm.value.content
    })
    
    if (res.code === 200) {
      alert('发布成功，请等待审核结果')
      showPublishModal.value = false
      topicForm.value = {
        category_id: '',
        title: '',
        content: ''
      }
      currentPage.value = 1
      getTopics()
    } else {
      alert(res.message || '发布失败，请重试')
    }
  } catch (err) {
    console.error('发布话题失败:', err)
    alert('网络错误，请稍后重试')
  } finally {
    publishing.value = false
  }
}

// 格式化时间
const formatTime = (timeStr) => {
  const now = new Date()
  const time = new Date(timeStr)
  const diff = now - time
  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  
  if (minutes < 60) {
    return `${minutes} 分钟前`
  } else if (hours < 24) {
    return `${hours} 小时前`
  } else {
    return `${days} 天前`
  }
}

// 标题截断函数：超出8个字用...代替
const truncateTitle = (title) => {
  if (!title) return ''
  if (title.length <= 8) return title
  return title.slice(0, 8) + '...'
}

// 跳转到话题详情页
const goToTopicDetail = (topicId) => {
  router.push(`/forum/topic/${topicId}`)
}

// 获取话题列表
const getTopics = async (isLoadMore = false) => {
  try {
    if (!isLoadMore) {
      loading.value.topics = true
    } else {
      loading.value.loadingMore = true
    }
    
    const params = {
      category_id: currentCategory.value,
      sort_by: sortBy.value,
      page: currentPage.value,
      page_size: pageSize.value
    }
    const res = await forumApi.getTopics(params)
    if (res.code === 200) {
      if (isLoadMore) {
        const newTopics = Array.isArray(res.data) ? res.data : []
        topics.value = [...topics.value, ...newTopics]
      } else {
        topics.value = Array.isArray(res.data) ? res.data : []
      }
      
      // 检查是否还有更多数据
      const receivedCount = isLoadMore ? topics.value.length : (Array.isArray(res.data) ? res.data.length : 0)
      hasMore.value = receivedCount >= currentPage.value * pageSize.value
    }
  } catch (err) {
    console.error('获取话题列表失败:', err)
  } finally {
    loading.value.topics = false
    loading.value.loadingMore = false
  }
}

// 加载更多
const loadMore = () => {
  if (hasMore.value && !loading.value.loadingMore) {
    currentPage.value++
    getTopics(true)
  }
}

// 处理分类变化
const handleCategoryChange = (categoryId) => {
  currentCategory.value = categoryId
  currentPage.value = 1
  getTopics()
}

// 处理排序变化
const handleSortChange = () => {
  currentPage.value = 1
  getTopics()
}

// 获取热门话题
const getHotTopics = async () => {
  try {
    loading.value.hotTopics = true
    const res = await forumApi.getHotTopics()
    if (res.code === 200) {
      hotTopics.value = Array.isArray(res.data) ? res.data : []
    }
  } catch (err) {
    console.error('获取热门话题失败:', err)
  } finally {
    loading.value.hotTopics = false
  }
}

// 获取活跃用户
const getActiveUsers = async () => {
  try {
    loading.value.activeUsers = true
    const res = await forumApi.getActiveUsers()
    if (res.code === 200) {
      activeUsers.value = Array.isArray(res.data) ? res.data : []
    }
  } catch (err) {
    console.error('获取活跃用户失败:', err)
  } finally {
    loading.value.activeUsers = false
  }
}

// 监听搜索关键词变化
watch(searchKeyword, () => {
  currentPage.value = 1
})

// 组件挂载时获取数据
onMounted(() => {
  getTopics()
  getHotTopics()
  getActiveUsers()
  
  // 检查 URL 参数是否需要自动打开发布模态框
  if (route.query.publish === '1' && isLoggedIn.value) {
    showPublishModal.value = true
    router.replace({ query: {} })
  }
})
</script>

<style scoped>
.text-primary-hover:hover {
  color: #FF6B9D;
  transition: all 0.3s ease;
}

.btn-primary-theme {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border: none;
  transition: all 0.3s ease;
}

.btn-primary-theme:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
  color: white;
}

.btn-outline-theme {
  border: 1px solid #FF6B9D;
  color: #FF6B9D;
  background: transparent;
  transition: all 0.3s ease;
}

.btn-outline-theme:hover {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border-color: transparent;
}

.btn-orange {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border: none;
  transition: all 0.3s ease;
}

.btn-orange:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
  color: white;
}

.btn-orange:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.form-control:focus {
  border-color: #FF6B9D !important;
  box-shadow: 0 0 0 0.2rem rgba(255, 107, 157, 0.25) !important;
}

/* 栏目分类卡片样式 */
.category-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.category-header {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  margin: 0;
  border-radius: 12px 12px 0 0;
}

.category-list {
  padding: 8px;
}

.category-item {
  width: 100%;
  text-align: left;
  padding: 10px 14px;
  border: none;
  background: transparent;
  color: #333;
  font-size: 14px;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s ease;
  margin-bottom: 4px;
}

.category-item:hover {
  background-color: #FFF5F0;
  color: #FF6B9D;
}

.category-item.active {
  background: #FFF0F5;
  color: #FF6B9D;
  font-weight: 500;
}

.category-item:last-child {
  margin-bottom: 0;
}

/* 排序按钮样式 */
.sort-btn {
  background: none;
  border: none;
  padding: 8px 16px;
  font-size: 14px;
  color: #666;
  cursor: pointer;
  position: relative;
  transition: color 0.3s ease;
}

.sort-btn:hover {
  color: #FF6B9D;
}

.sort-btn.active {
  color: #FF6B9D;
  font-weight: bold;
}

.sort-btn.active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 2px;
  background-color: #FF6B9D;
}

/* 搜索框样式 */
.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 250px;
}

.search-input {
  width: 100%;
  padding: 8px 40px 8px 16px !important;
  border: 1px solid #E8E8E8 !important;
  border-radius: 8px !important;
  box-shadow: none !important;
  font-size: 14px;
  outline: none;
}

.search-input:focus {
  border-color: #FF6B9D !important;
}

.search-btn {
  position: absolute;
  right: 8px;
  background: none;
  color: #FF6B9D;
  border: none;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
}

.search-btn:hover {
  color: #FF4D8A;
}

/* 热门话题排名徽章 */
.card-body .space-y-3 .rank-badge {
  width: 18px !important;
  height: 18px !important;
  font-size: 11px !important;
  font-weight: bold !important;
  border-radius: 3px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  background-color: #DDDDDD !important;
  color: #666 !important;
  flex-shrink: 0 !important;
}

.card-body .space-y-3 .rank-badge.top-three {
  background: linear-gradient(135deg, #FF6B9D, #FF9F43) !important;
  color: white !important;
}

/* 文字截断样式 */
.card-body .space-y-3 .truncate-text {
  flex: 1 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  display: block !important;
  max-width: 150px !important;
}

/* 用户头像样式 */
.user-avatar {
  width: 40px;
  height: 40px;
  font-size: 16px;
  font-weight: bold;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 迷你头像样式 */
.avatar-mini {
  width: 28px;
  height: 28px;
  font-size: 12px;
  font-weight: bold;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.badge-secondary {
  background-color: #FFE4E1;
  color: #FF6B9D;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 4px;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* 移动端分类标签样式 */
.category-mobile {
  background: white;
  border-radius: 12px;
  padding: 8px 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.category-scroll {
  display: flex;
  overflow-x: auto;
  white-space: nowrap;
  padding: 0 12px;
  scrollbar-width: thin;
  scrollbar-color: #CCCCCC #F5F5F5;
}

.category-scroll::-webkit-scrollbar {
  height: 6px;
}

.category-scroll::-webkit-scrollbar-track {
  background: #F5F5F5;
  border-radius: 3px;
}

.category-scroll::-webkit-scrollbar-thumb {
  background: #CCCCCC;
  border-radius: 3px;
}

.category-scroll::-webkit-scrollbar-thumb:hover {
  background: #BBBBBB;
}

.category-mobile-item {
  flex-shrink: 0;
  width: 90px;
  padding: 8px 0;
  margin-right: 8px;
  border: none;
  background: transparent;
  color: #666;
  font-size: 14px;
  cursor: pointer;
  border-radius: 20px;
  transition: all 0.3s ease;
  text-align: center;
}

.category-mobile-item:hover {
  background-color: #FFF5F0;
  color: #FF6B9D;
}

.category-mobile-item.active {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  font-weight: 500;
}
</style>