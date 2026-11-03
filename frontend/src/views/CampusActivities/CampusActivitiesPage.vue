<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 页面标题 -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-text-primary">
        <i class="fa fa-calendar-check-o mr-2"></i> 校园活动
      </h1>
    </div>
    
    <!-- 搜索和分类筛选区 -->
    <div class="mb-6 p-4 bg-white rounded-lg shadow-sm">
      <div class="row">
        <div class="col-md-8 mb-4 mb-md-0">
          <div class="search-wrapper">
            <input 
              type="text" 
              class="search-input" 
              placeholder="搜索活动..." 
              v-model="searchKeyword"
              @input="handleSearch"
            >
            <button class="search-btn" @click="handleSearch">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>
        <div class="col-md-4">
          <select 
            class="form-control" 
            v-model="currentCategory"
            @change="handleCategoryChange"
          >
            <option value="0">全部分类</option>
            <option value="1">学术活动</option>
            <option value="2">文体活动</option>
            <option value="3">志愿服务</option>
            <option value="4">社团活动</option>
            <option value="5">其他活动</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- 活动列表 -->
    <div class="row">
      <div v-for="activity in activityList" :key="activity.id" class="col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="h-100">
          <div class="card shadow-sm h-100">
            <div v-if="activity.poster" class="card-img-top">
              <img :src="activity.poster" :alt="activity.title" class="w-100" style="height: 150px; object-fit: cover;">
            </div>
            <div v-else class="activity-icon text-orange-gradient p-4 text-center">
              <i class="fa fa-star" style="font-size: 2.5rem;"></i>
            </div>
            <div class="card-body d-flex flex-column">
              <h3 class="card-title font-bold mb-2">{{ activity.title || '活动标题' }}</h3>
              <div class="mb-3">
                <span class="text-sm text-gray-500">
                  {{ formatDateTime(activity.start_time) || '开始时间' }}<br>
                  至<br>
                  {{ formatDateTime(activity.end_time) || '结束时间' }}
                </span>
              </div>
              <div class="mt-auto">
                <button 
                  class="btn btn-primary w-100"
                  @click="openExternalLink('https://www.5idream.net/')"
                >
                  <i class="fa fa-external-link mr-1"></i> 参与活动
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 空状态 -->
    <div v-if="activityList.length === 0 && !loading" class="card shadow-sm p-5 text-center">
      <div class="text-text-secondary">暂无活动</div>
    </div>
    
    <!-- 加载状态 -->
    <div v-if="loading" class="card shadow-sm p-5 text-center">
      <div class="spinner-border text-primary" role="status"></div>
    </div>
    
    <!-- 分页 -->
    <div v-if="total > 0 && !loading" class="mt-6 d-flex justify-content-center">
      <nav aria-label="Page navigation">
        <ul class="pagination">
          <li class="page-item" :class="{ disabled: pageNum === 1 }">
            <button class="page-link" @click="handlePageChange(pageNum - 1)">上一页</button>
          </li>
          <li class="page-item" :class="{ disabled: pageNum === totalPages }">
            <button class="page-link" @click="handlePageChange(pageNum + 1)">下一页</button>
          </li>
        </ul>
      </nav>
      <div class="ml-4 d-flex align-items-center">
        <span class="text-text-secondary">
          第 {{ pageNum }} / {{ totalPages }} 页，共 {{ total }} 条
        </span>
      </div>
    </div>
  </div>
  
  <!-- 页脚 -->
  <SiteFooter />
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { activityApi } from '@/api/modules/activity'
import SiteFooter from '@/components/SiteFooter.vue'

// 活动列表相关
const activityList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(12)
const total = ref(0)
const currentCategory = ref(0)
const searchKeyword = ref('')
// 初始化router
const router = useRouter()

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取活动列表
const getActivityList = async () => {
  try {
    loading.value = true
    
    const res = await activityApi.getActivities({
      page: pageNum.value,
      pageSize: pageSize.value,
      category_id: currentCategory.value || undefined,
      keyword: searchKeyword.value || undefined
    })
    
    if (res.code === 200) {
      activityList.value = res.data.data || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取活动列表失败:', err)
  } finally {
    loading.value = false
  }
}

// 处理搜索
const handleSearch = () => {
  pageNum.value = 1
  getActivityList()
}

// 处理分类变化
const handleCategoryChange = () => {
  pageNum.value = 1
  getActivityList()
}

// 处理分页变化
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getActivityList()
}

// 格式化日期时间
const formatDateTime = (dateTime) => {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${year}年${month}月${day}日${hours}时${minutes}分`
}

// 打开外部链接
const openExternalLink = (url) => {
  window.open(url, '_blank')
}

// 页面加载时获取数据
onMounted(() => {
  getActivityList()
})
</script>

<style scoped>
/* 自定义样式 */
.container {
  max-width: 1200px;
}

.text-text-primary {
  color: var(--color-primary);
}

.text-text-secondary {
  color: var(--color-text-secondary);
}

/* 响应式调整 */
@media (max-width: 768px) {
  h1 {
    font-size: 2rem;
  }
  
  .row {
    --bs-gutter-x: 1rem;
  }
}

/* 确保卡片容器高度一致 */
.col-sm-6.col-md-4.col-lg-3 {
  display: flex;
  align-items: stretch;
}

.h-100 {
  height: 100%;
  width: 100%;
}

/* 活动图标样式 */
.activity-icon {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
}

.text-orange-gradient {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* 卡片样式 */
.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* 搜索框样式 */
.search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.search-input {
  width: 100%;
  padding: 8px 40px 8px 16px;
  border: 1px solid #E8E8E8;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
}

.search-input:focus {
  border-color: #FF6B9D;
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
</style>