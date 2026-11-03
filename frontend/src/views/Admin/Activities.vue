<template>
  <div class="activities-container">
    <div class="page-header">
      <h2>活动管理</h2>
      <div class="search-box">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            v-model="searchKeyword"
            placeholder="搜索活动标题、描述或发布者"
            @input="handleSearch"
          >
          <div class="input-group-append">
            <button class="btn btn-primary" @click="handleSearch">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div class="card activities-card shadow">
      <div class="card-body">
        <!-- 加载状态 -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="ml-2">加载中...</span>
        </div>
        
        <!-- 活动列表 -->
        <table v-else class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th scope="col" class="text-center" style="width: 80px">ID</th>
              <th scope="col" class="text-center" style="width: 100px">活动图片</th>
              <th scope="col" style="min-width: 200px">活动标题</th>
              <th scope="col" class="text-center" style="width: 120px">发布者</th>
              <th scope="col" class="text-center" style="width: 150px">活动时间</th>
              <th scope="col" class="text-center" style="width: 150px">发布时间</th>
              <th scope="col" class="text-center" style="width: 150px">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="activity in activitiesList" :key="activity.id">
              <td class="text-center">{{ activity.id }}</td>
              <td class="text-center">
                <img
                  :src="activity.coverImg || 'https://picsum.photos/60/60'"
                  alt="活动图片"
                  class="img-thumbnail"
                  style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px"
                >
              </td>
              <td class="activity-title" :title="activity.title">{{ truncateText(activity.title) }}</td>
              <td class="text-center">{{ activity.publisherName }}</td>
              <td class="text-center">{{ activity.activityTime }}</td>
              <td class="text-center">{{ activity.createTime }}</td>
              <td class="text-center">
                <button 
                  class="btn btn-danger btn-sm" 
                  @click="handleDelete(activity.id)"
                >
                  删除
                </button>
              </td>
            </tr>
            <tr v-if="activitiesList.length === 0">
              <td colspan="7" class="text-center">暂无活动数据</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- 分页 -->
      <div class="card-footer" v-if="total > 0">
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted">
            第 {{ pageNum }} / {{ totalPages }} 页，共 {{ total }} 条
          </div>
          <nav>
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: pageNum === 1 }">
                <button class="page-link" @click="handlePageChange(pageNum - 1)">上一页</button>
              </li>
              <li class="page-item" :class="{ disabled: pageNum === totalPages }">
                <button class="page-link" @click="handlePageChange(pageNum + 1)">下一页</button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

import request from '@/utils/request'

// 活动列表相关状态
const activitiesList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(10)
const total = ref(0)
const searchKeyword = ref('')

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取活动列表
const getActivities = async () => {
  try {
    loading.value = true
    const res = await request({
      url: '/api/admin/activities',
      method: 'get',
      params: {
        pageNum: pageNum.value,
        pageSize: pageSize.value,
        keyword: searchKeyword.value
      }
    })
    if (res.code === 200) {
      activitiesList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取活动列表失败:', err)
    alert('获取活动列表失败')
  } finally {
    loading.value = false
  }
}

// 删除活动
const handleDelete = async (id) => {
  try {
    if (confirm('确定要删除这个活动吗？')) {
      const res = await request({
        url: `/api/admin/activities/${id}`,
        method: 'delete'
      })
      
      if (res.code === 200) {
        alert('删除成功')
        // 刷新列表
        await getActivities()
      } else {
        alert(res.msg || '删除失败')
      }
    }
  } catch (err) {
    console.error('删除失败:', err)
    alert('删除失败')
  }
}

// 搜索
const handleSearch = () => {
  pageNum.value = 1
  getActivities()
}

// 分页处理
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getActivities()
}

// 文本截断函数
const truncateText = (text) => {
  if (!text) return ''
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}

// 页面加载时获取数据
onMounted(() => {
  getActivities()
})
</script>

<style scoped>
.activities-container {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.search-box {
  width: 300px;
}

.search-input {
  width: 100%;
}

.activities-card {
  margin-bottom: 20px;
}

.activity-title {
  font-weight: 500;
}

.pagination-container {
  margin-top: 20px;
  text-align: center;
}
</style>