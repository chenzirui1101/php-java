<template>
  <div class="container py-8">
    <div class="row">
      <!-- 左侧交互按钮区 -->
      <div class="col-md-1 d-none d-md-block">
        <div class="interaction-sidebar">
          <button class="interaction-btn-vertical" @click="handleBack">
            <i class="fa fa-arrow-left"></i>
            <span>返回</span>
          </button>
          <button class="interaction-btn-vertical" @click="handleLike" :class="{ liked: isLiked }">
            <i class="fa fa-thumbs-up"></i>
            <span>{{ topic.likes_count || 0 }}</span>
          </button>
          <button class="interaction-btn-vertical" @click="handleFavorite" :class="{ favorited: isFavorited }">
            <i class="fa fa-star"></i>
            <span>{{ topic.favorites_count || 0 }}</span>
          </button>
          <button class="interaction-btn-vertical" @click="handleShare">
            <i class="fa fa-share-alt"></i>
            <span>分享</span>
          </button>
        </div>
      </div>
      
      <!-- 中间内容区 -->
      <div class="col-md-7">
        <!-- 话题详情卡片 -->
        <div class="card mb-6">
          <div class="card-body">
            <h1 class="h2 font-bold mb-4">{{ topic.title }}</h1>
            <div class="d-flex align-items-center text-sm text-muted mb-4">
              <div class="avatar-circle-sm mr-2">{{ (topic.author?.username || '匿').charAt(0).toUpperCase() }}</div>
              <span class="cursor-pointer text-primary-hover" @click="contactUser(topic.author?.id, topic.author?.username)">
                {{ topic.author?.username || '匿名用户' }}
              </span>
              <span class="mx-2">·</span>
              <span>{{ formatTime(topic.created_at) }}</span>
              <span class="mx-2">·</span>
              <span>{{ comments.length }} 回复</span>
            </div>
            <div class="topic-content mb-6 text-gray-700" v-html="formatContent(topic.content)"></div>
          </div>
        </div>

        <!-- 评论区 -->
        <div class="card mb-6">
          <div class="card-body">
            <h3 class="h5 font-semibold mb-4">评论 ({{ comments.length }})</h3>
            <div v-if="loading.comments" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">加载评论中...</p>
            </div>
            <div v-else-if="comments.length === 0" class="text-center py-8">
              <i class="fa fa-comment-o" style="font-size: 3rem; color: #e9ecef;"></i>
              <h4 class="mt-3 text-muted">暂无评论</h4>
              <p class="text-muted">成为第一个评论的人吧！</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="comment in comments" :key="comment.id" class="border-bottom pb-4">
                <div class="d-flex align-items-start">
                  <div class="avatar-circle-sm mr-3 flex-shrink-0">{{ (comment.user?.username || '匿').charAt(0).toUpperCase() }}</div>
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center text-sm">
                      <strong class="cursor-pointer text-primary-hover" @click="contactUser(comment.user?.id, comment.user?.username)">{{ comment.user?.username || '匿名用户' }}</strong>
                      <span class="text-muted mx-2">·</span>
                      <span class="text-muted text-sm">{{ formatTime(comment.created_at) }}</span>
                    </div>
                    <p class="mt-2 text-gray-700">{{ comment.content }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- 评论输入框 -->
            <div class="mt-6">
              <h4 class="h6 font-semibold mb-3">发表评论</h4>
              <textarea v-model="newComment" rows="3" class="form-control mb-3" placeholder="写下你的评论..." :disabled="!isLoggedIn"></textarea>
              <button class="btn btn-primary" @click="submitComment" :disabled="!isLoggedIn || !newComment.trim()">发表评论</button>
              <p v-if="!isLoggedIn" class="text-muted text-sm mt-2">请先登录后再评论</p>
            </div>
          </div>
        </div>

        <!-- 为你推荐 -->
        <div class="card mb-6">
          <div class="card-body">
            <h3 class="h5 font-semibold mb-4">为你推荐</h3>
            <div v-if="loading.recommendations" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="mt-2 text-muted">加载推荐中...</p>
            </div>
            <div v-else-if="recommendations.length === 0" class="text-center py-8">
              <p class="text-muted">暂无推荐内容</p>
            </div>
            <div v-else class="recommend-list">
              <button 
                v-for="item in recommendations.slice(0, 5)" 
                :key="item.id" 
                class="recommend-item"
                @click="handleRecommendClick(item.id)"
              >
                <div class="font-medium text-primary-hover">{{ item.title }}</div>
                <div class="text-sm text-muted mt-1">
                  {{ item.author_username }} · {{ formatTime(item.created_at) }} · {{ item.reads_count || 0 }} 阅读 · {{ item.replies_count || 0 }} 评论
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- 切换详情页 -->
        <div class="card mb-6">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <button 
                class="topic-nav-btn flex-1 mr-3"
                :class="{ disabled: !prevTopic }"
                @click="handlePrevTopic"
              >
                <div class="text-sm text-muted">上一篇</div>
                <div class="font-medium mt-1">{{ prevTopic?.title || '暂无' }}</div>
              </button>
              <button 
                class="topic-nav-btn flex-1"
                :class="{ disabled: !nextTopic }"
                @click="handleNextTopic"
              >
                <div class="text-sm text-muted">下一篇</div>
                <div class="font-medium mt-1">{{ nextTopic?.title || '暂无' }}</div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 右侧边栏 - 用户资料卡片 -->
      <div class="col-md-4" id="author-section">
        <div class="card mb-6" style="position: sticky; top: 80px;">
          <div class="card-body text-center">
            <div class="avatar-circle-lg mx-auto mb-3">{{ (authorInfo.name || '用').charAt(0).toUpperCase() }}</div>
            <h3 class="h5 font-bold mb-1">{{ authorInfo.name }}</h3>
            <p class="text-muted text-sm mb-3">{{ authorInfo.bio || '暂无简介' }}</p>

            <div class="d-flex justify-content-around mb-4">
              <div class="text-center">
                <div class="font-bold text-lg">{{ authorInfo.topics }}</div>
                <div class="text-muted text-xs">话题</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg">{{ authorInfo.reads }}</div>
                <div class="text-muted text-xs">阅读</div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg">{{ authorInfo.fans }}</div>
                <div class="text-muted text-xs">粉丝</div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary flex-1" @click="handleFollow">
                <i class="fa fa-plus mr-1"></i>{{ authorInfo.isFollowing ? '已关注' : '关注' }}
              </button>
              <button class="btn btn-outline-primary flex-1" @click="handleMessage">
                <i class="fa fa-comment mr-1"></i>私信
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 移动端固定交互栏 -->
  <div class="mobile-bottom-bar d-md-none">
    <button class="mobile-bar-btn" @click="handleBack">
      <i class="fa fa-arrow-left"></i>
      <span>返回</span>
    </button>
    <button class="mobile-bar-btn" @click="handleLike" :class="{ active: isLiked }">
      <i class="fa fa-thumbs-up"></i>
      <span>{{ topic.likes_count || 0 }}</span>
    </button>
    <button class="mobile-bar-btn" @click="handleFavorite" :class="{ active: isFavorited }">
      <i class="fa fa-star"></i>
      <span>{{ topic.favorites_count || 0 }}</span>
    </button>
    <button class="mobile-bar-btn" @click="handleShare">
      <i class="fa fa-share-alt"></i>
      <span>分享</span>
    </button>
    <button class="mobile-bar-btn avatar-btn" @click="scrollToAuthor">
      <div class="avatar-circle-small">{{ (topic.author?.username || '用').charAt(0).toUpperCase() }}</div>
    </button>
  </div>
  
  <AuthModal v-if="showAuthModal" :showTermsDirectly="false" @close="showAuthModal = false" @success="location.reload()" />
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AuthModal from "@/components/auth/AuthModal.vue"
import { forumApi } from '@/api/modules/forum'
import { chatApi } from '@/api/modules/chat'
import request from '@/utils/request'

// 路由和导航
const route = useRoute()
const router = useRouter()
const showAuthModal = ref(false)

// 数据状态
const topic = ref({})
const comments = ref([])
const newComment = ref('')
const authorInfo = ref({ name: '', bio: '', topics: 0, reads: 0, fans: 0, isFollowing: false })

// 交互状态
const isLiked = ref(false)
const isFavorited = ref(false)

// 推荐数据
const recommendations = ref([])
const prevTopic = ref(null)
const nextTopic = ref(null)

// 加载状态
const loading = ref({ topic: false, comments: false, recommendations: false })

// 用户登录状态
const isLoggedIn = computed(() => !!localStorage.getItem('token'))

// 格式化时间为具体日期时间
const formatTime = (timeStr) => {
  if (!timeStr) return '未知时间'
  
  const time = new Date(timeStr)
  
  // 检查是否为有效日期
  if (isNaN(time.getTime())) return '未知时间'
  
  return time.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// 格式化内容，保留换行
const formatContent = (content) => {
  if (!content) return ''
  // 将换行符转换为 <br> 标签
  return content
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\n/g, '<br>')
}

// 跳转到其他话题
const goToTopic = (topicId) => {
  router.push(`/forum/topic/${topicId}`)
}

// 联系评论用户
const contactUser = async (userId, username) => {
  if (!isLoggedIn.value) { alert('请先登录后再联系用户'); showAuthModal.value = true; return }
  try {
    const res = await chatApi.createConversation({ resourceType: 'forum_comment', resourceId: userId })
    if (res?.code === 200 && res.data?.id) router.push({ path: '/chat', query: { conversation_id: res.data.id } })
    else alert(res?.msg || '创建会话失败')
  } catch (err) { alert('联系失败，请稍后重试') }
}

// 获取话题详情
const getTopicDetail = async () => {
  loading.value.topic = true
  try {
    const id = route.params.id
    const res = await forumApi.getTopicDetail(id)
    if (res.code === 200) {
      topic.value = res.data
      if (res.data.author?.id) getUserProfile(res.data.author.id)
      // 获取当前用户对该话题的点赞和收藏状态
      await getUserTopicStatus(id)
    }
  } catch (err) {} finally { loading.value.topic = false }
}

// 获取用户对话题的状态（点赞、收藏）
const getUserTopicStatus = async (topicId) => {
  if (!isLoggedIn.value) return
  try {
    const res = await forumApi.getUserTopicStatus(topicId)
    if (res.code === 200) {
      isLiked.value = res.data.is_liked || false
      isFavorited.value = res.data.is_favorited || false
    }
  } catch (err) {}
}

// 获取评论列表
const getComments = async () => {
  loading.value.comments = true
  try {
    const id = route.params.id
    const res = await forumApi.getComments(id)
    if (res.code === 200) comments.value = res.data || []
  } catch (err) {} finally { loading.value.comments = false }
}

const getUserProfile = async (userId) => {
  try {
    const res = await request({ url: `/api/user/${userId}/profile`, method: 'get' })
    if (res.code === 200) authorInfo.value = res.data
  } catch (err) {}
}

const handleFollow = async () => {
  if (!isLoggedIn.value) { alert('请先登录'); showAuthModal.value = true; return }
  try {
    const res = await request({ url: `/api/user/${topic.value.author?.id}/follow`, method: 'post' })
    if (res.code === 200) {
      authorInfo.value.isFollowing = !authorInfo.value.isFollowing
      authorInfo.value.fans += authorInfo.value.isFollowing ? 1 : -1
    }
  } catch (err) { alert('操作失败') }
}

const handleMessage = () => {
  if (!isLoggedIn.value) { alert('请先登录'); showAuthModal.value = true; return }
  contactUser(topic.value.author?.id, topic.value.author?.username)
}

// 返回上一级
const handleBack = () => {
  router.push('/forum')
}

// 点赞功能
const handleLike = async () => {
  if (!isLoggedIn.value) { alert('请先登录'); showAuthModal.value = true; return }
  
  try {
    const id = route.params.id
    const res = await forumApi.likeTopic(id)
    if (res.code === 200) {
      isLiked.value = !isLiked.value
      if (isLiked.value) {
        topic.value.likes_count = (topic.value.likes_count || 0) + 1
      } else {
        topic.value.likes_count = Math.max(0, (topic.value.likes_count || 0) - 1)
      }
    }
  } catch (err) {
    alert('操作失败，请稍后重试')
  }
}

// 收藏功能
const handleFavorite = async () => {
  if (!isLoggedIn.value) { alert('请先登录'); showAuthModal.value = true; return }
  
  try {
    const id = route.params.id
    const res = await forumApi.favoriteTopic(id)
    if (res.code === 200) {
      isFavorited.value = !isFavorited.value
      if (isFavorited.value) {
        topic.value.favorites_count = (topic.value.favorites_count || 0) + 1
      } else {
        topic.value.favorites_count = Math.max(0, (topic.value.favorites_count || 0) - 1)
      }
    }
  } catch (err) {
    alert('操作失败，请稍后重试')
  }
}

// 分享功能
const handleShare = () => {
  const url = window.location.href
  navigator.clipboard.writeText(url).then(() => {
    alert('链接已复制到剪贴板')
  }).catch(() => {
    alert('复制失败，请手动复制')
  })
}

// 获取推荐话题（点赞数前五）
const getRecommendations = async () => {
  loading.value.recommendations = true
  try {
    const res = await forumApi.getRecommendations()
    if (res.code === 200) {
      recommendations.value = res.data || []
    }
  } catch (err) {} finally { loading.value.recommendations = false }
}

// 点击推荐话题
const handleRecommendClick = (topicId) => {
  console.log('推荐话题点击:', topicId)
  goToTopic(topicId)
}

// 获取上下篇话题
const getTopicNavigation = async () => {
  try {
    const id = route.params.id
    const res = await forumApi.getTopicNavigation(id)
    if (res.code === 200) {
      prevTopic.value = res.data.prev || null
      nextTopic.value = res.data.next || null
    }
  } catch (err) {}
}

// 上一篇话题
const handlePrevTopic = () => {
  console.log('上一篇点击:', prevTopic.value)
  if (prevTopic.value) {
    goToTopic(prevTopic.value.id)
  }
}

// 下一篇话题
const handleNextTopic = () => {
  console.log('下一篇点击:', nextTopic.value)
  if (nextTopic.value) {
    goToTopic(nextTopic.value.id)
  }
}

// 滚动到作者区域
const scrollToAuthor = () => {
  const authorSection = document.getElementById('author-section')
  if (authorSection) {
    authorSection.scrollIntoView({ behavior: 'smooth', block: 'center' })
  }
}

// 提交评论
const submitComment = async () => {
  if (!newComment.value.trim()) return
  try {
    const id = route.params.id
    const res = await forumApi.addComment(id, { content: newComment.value })
    if (res.code === 200) { alert('评论提交成功，请等待管理员审核'); newComment.value = '' }
  } catch (err) { alert('评论提交失败，请稍后重试') }
}

let refreshInterval = null
onMounted(() => {
  getTopicDetail()
  getComments()
  getRecommendations()
  getTopicNavigation()
  refreshInterval = setInterval(() => getComments(), 10000)
})

// 监听路由参数变化，当从一个话题跳转到另一个话题时重新加载数据
watch(() => route.params.id, (newId, oldId) => {
  if (newId !== oldId) {
    // 重置状态
    topic.value = {}
    comments.value = []
    isLiked.value = false
    isFavorited.value = false
    prevTopic.value = null
    nextTopic.value = null
    // 重新加载数据
    getTopicDetail()
    getComments()
    getRecommendations()
    getTopicNavigation()
  }
})

// 组件卸载时清除定时器
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
.text-primary-hover:hover { color: #FF6B9D; transition: all 0.3s ease; }
.btn-primary { background: linear-gradient(135deg, #FF6B9D, #FFD93D); border: none; color: white; }
.btn-primary:hover { opacity: 0.9; }
.btn-outline-primary { border: 1px solid #FF6B9D; color: #FF6B9D; background: transparent; }
.btn-outline-primary:hover { background: #FFF5F0; }
.gap-2 { gap: 8px; }
.flex-1 { flex: 1; }
.avatar-circle-sm { width: 32px; height: 32px; font-size: 14px; font-weight: bold; background: linear-gradient(135deg, #FF6B9D, #FFD93D); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.avatar-circle-lg { width: 72px; height: 72px; font-size: 28px; font-weight: bold; background: linear-gradient(135deg, #FF6B9D, #FFD93D); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

/* 话题内容区域样式 */
.topic-content {
  line-height: 1.8;
  font-size: 15px;
  color: #333;
  white-space: pre-wrap;
  word-break: break-word;
  padding: 16px;
  background: #fafafa;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
}

.topic-content br {
  display: block;
  content: '';
  margin-top: 12px;
}

/* 左侧交互侧边栏 */
.interaction-sidebar {
  position: sticky;
  top: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 16px 0;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  width: 100%;
}

.interaction-btn-vertical {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background: #f8f9fa;
  border: none;
  border-radius: 50%;
  color: #666;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.interaction-btn-vertical i {
  font-size: 16px;
  margin-bottom: 3px;
}

.interaction-btn-vertical:hover {
  background: #FFF5F0;
  color: #FF6B9D;
  transform: scale(1.05);
}

.interaction-btn-vertical.liked,
.interaction-btn-vertical.favorited {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
}

.interaction-btn-vertical.liked:hover,
.interaction-btn-vertical.favorited:hover {
  opacity: 0.9;
  transform: scale(1.05);
}

/* 话题导航按钮 */
.topic-nav-btn {
  padding: 12px;
  text-align: left;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  pointer-events: auto;
  position: relative;
  z-index: 1;
}

.topic-nav-btn:hover {
  border-color: #FF6B9D;
  background: #FFF5F0;
}

.topic-nav-btn.disabled {
  cursor: not-allowed;
  opacity: 0.5;
  border-color: #e9ecef;
  background: #f8f9fa;
  pointer-events: none;
}

.topic-nav-btn.disabled:hover {
  border-color: #e9ecef;
  background: #f8f9fa;
}

/* 推荐列表样式 */
.recommend-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.recommend-item {
  display: block;
  width: 100%;
  text-align: left;
  padding: 12px 0;
  background: transparent;
  border: none;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.recommend-item:last-child {
  border-bottom: none;
}

.recommend-item:hover {
  background: #fafafa;
}

/* 移动端固定底部栏 */
.mobile-bottom-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: white;
  border-top: 1px solid #e9ecef;
  padding: 10px 0;
  padding-bottom: calc(10px + env(safe-area-inset-bottom));
  z-index: 1000;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
}

.mobile-bar-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  background: transparent;
  border: none;
  color: #666;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.3s ease;
  flex: 1;
  max-width: 70px;
}

.mobile-bar-btn i {
  font-size: 18px;
  margin-bottom: 3px;
}

.mobile-bar-btn.active {
  color: #FF6B9D;
}

.mobile-bar-btn:hover {
  color: #FF6B9D;
}

.mobile-bar-btn.avatar-btn {
  max-width: 50px;
}

.avatar-circle-small {
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
  box-shadow: 0 2px 8px rgba(255, 107, 157, 0.3);
}
</style>