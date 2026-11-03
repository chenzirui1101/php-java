<template>
  <div class="lostfound-detail-container">
    <div class="container py-5">
      <div class="row">
        <!-- 左侧内容 -->
        <div class="col-lg-8">
          <!-- 标题区域 -->
          <div class="card mb-4">
            <div class="card-header">
              <h1 class="card-title">{{ lostFoundItem.title }}</h1>
              <div class="d-flex align-items-center mt-2">
                <span class="badge" :class="{'bg-danger': lostFoundItem.type === 'lost', 'bg-success': lostFoundItem.type === 'found'}">
                  {{ lostFoundItem.type === 'lost' ? '寻物启事' : '失物招领' }}
                </span>
                <span class="badge bg-secondary ms-2">
                  {{ lostFoundItem.status === 'pending' ? '待处理' : lostFoundItem.status === 'looking' ? '寻找中' : '已解决' }}
                </span>
                <span class="text-muted ms-3">{{ formatDate(lostFoundItem.created_at) }}</span>
              </div>
            </div>
            <div class="card-body">
              <!-- 图片展示 -->
              <div class="mb-4">
                <img 
                  :src="lostFoundItem.coverImg || lostFoundItem.cover_img || 'https://picsum.photos/600/400'" 
                  alt="失物招领图片" 
                  class="img-fluid rounded"
                  style="max-height: 400px; object-fit: cover;"
                >
              </div>
              
              <!-- 详细描述 -->
              <div class="mb-4">
                <h5>详细描述</h5>
                <p class="text-muted">{{ lostFoundItem.description }}</p>
              </div>
              
              <!-- 信息标签 -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="d-flex align-items-center">
                    <i class="fa fa-map-marker text-danger me-2"></i>
                    <strong>地点：</strong>
                    <span class="ms-1">{{ lostFoundItem.location }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- 右侧内容 -->
        <div class="col-lg-4">
          <!-- 发布者信息 -->
          <div class="card mb-4">
            <div class="card-header">
              <h5>发布者信息</h5>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="avatar-circle">
                  {{ getAvatarLetter(lostFoundItem.user?.name || lostFoundItem.user?.username) }}
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">{{ lostFoundItem.user?.name || '匿名用户' }}</h6>
                  <p class="text-sm text-muted mb-0">{{ lostFoundItem.user?.username || '' }}</p>
                </div>
              </div>
              <button class="btn btn-primary w-100" @click="contactUser">
                <i class="fa fa-comments-o me-1"></i> 联系发布者
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getLostFoundById } from '@/api/modules/lostfound.js'
import { chatApi } from '@/api/modules/chat.js'

const route = useRoute()
const router = useRouter()
const lostFoundItem = ref({})

// 格式化日期
const formatDate = (dateString) => {
  if (!dateString) return '未知时间'
  
  const date = new Date(dateString)
  
  // 检查是否为有效日期
  if (isNaN(date.getTime())) return '未知时间'
  
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// 获取用户头像首字母
const getAvatarLetter = (name) => {
  if (!name) return '匿'
  return name.charAt(0).toUpperCase()
}

// 联系发布者
const contactUser = async () => {
  if (!localStorage.getItem('token')) {
    alert('请先登录')
    return
  }
  try {
    console.log('lostFoundItem:', lostFoundItem.value)
    console.log('lostFoundItem.id:', lostFoundItem.value.id)
    // 创建或获取与发布者的会话
    const res = await chatApi.createConversation({
      resourceType: 'lost_found',
      resourceId: lostFoundItem.value.id
    })
    // 跳转到聊天页面，并指定会话ID
    router.push({
      path: '/chat',
      query: {
        conversationId: res.data.id
      }
    })
  } catch (error) {
    console.error('创建会话失败:', error)
    alert('联系失败，请稍后重试')
  }
}

// 获取失物招领详情
const getLostFoundDetail = async () => {
  try {
    const id = route.params.id
    const res = await getLostFoundById(id)
    lostFoundItem.value = res.data
  } catch (error) {
    console.error('获取失物招领详情失败:', error)
  }
}

onMounted(() => {
  getLostFoundDetail()
})
</script>

<style scoped>
.lostfound-detail-container {
  min-height: 100vh;
  background-color: #f8f9fa;
}

/* 用户头像样式 */
.avatar-circle {
  width: 60px;
  height: 60px;
  font-size: 24px;
  font-weight: bold;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>