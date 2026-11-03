<template>
  <div class="lostfound-container">
    <div class="page-header">
      <h2>失物管理</h2>
      <div class="search-box">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            v-model="searchKeyword"
            placeholder="搜索失物名称、描述或发布者"
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
    
    <div class="card lostfound-card shadow">
      <div class="card-body">
        <!-- 加载状态 -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="ml-2">加载中...</span>
        </div>
        
        <!-- 失物列表 -->
        <table v-else class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th scope="col" class="text-center" style="width: 80px">ID</th>
              <th scope="col" class="text-center" style="width: 100px">图片</th>
              <th scope="col" style="min-width: 200px">标题</th>
              <th scope="col" class="text-center" style="width: 100px">类型</th>
              <th scope="col" class="text-center" style="width: 120px">发布者</th>
              <th scope="col" class="text-center" style="width: 150px">发布时间</th>
              <th scope="col" class="text-center" style="width: 150px">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in lostFoundList" :key="item.id">
              <td class="text-center">{{ item.id }}</td>
              <td class="text-center">
                <img
                  :src="item.coverImg || 'https://picsum.photos/60/60'"
                  alt="失物图片"
                  class="img-thumbnail"
                  style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px"
                >
              </td>
              <td class="item-title" :title="item.title">{{ truncateText(item.title) }}</td>
              <td class="text-center">{{ item.type === 'lost' ? '丢失' : '招领' }}</td>
              <td class="text-center">{{ item.publisherName }}</td>
              <td class="text-center">{{ item.createTime }}</td>
              <td class="text-center">
                <button 
                  class="btn btn-danger btn-sm" 
                  @click="handleDelete(item.id)"
                >
                  删除
                </button>
              </td>
            </tr>
            <tr v-if="lostFoundList.length === 0">
              <td colspan="7" class="text-center">暂无失物数据</td>
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

// 失物列表相关状态
const lostFoundList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(10)
const total = ref(0)
const searchKeyword = ref('')

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取失物列表
const getLostFound = async () => {
  try {
    loading.value = true
    const res = await request({
      url: '/api/admin/lostfound',
      method: 'get',
      params: {
        pageNum: pageNum.value,
        pageSize: pageSize.value,
        keyword: searchKeyword.value
      }
    })
    if (res.code === 200) {
      lostFoundList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取失物列表失败:', err)
    alert('获取失物列表失败')
  } finally {
    loading.value = false
  }
}

// 删除失物
const handleDelete = async (id) => {
  try {
    if (confirm('确定要删除这个失物信息吗？')) {
      const res = await request({
        url: `/api/admin/lostfound/${id}`,
        method: 'delete'
      })
      
      if (res.code === 200) {
        alert('删除成功')
        // 刷新列表
        await getLostFound()
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
  getLostFound()
}

// 分页处理
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getLostFound()
}

// 文本截断函数
const truncateText = (text) => {
  if (!text) return ''
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}

// 页面加载时获取数据
onMounted(() => {
  getLostFound()
})
</script>

<style scoped>
.lostfound-container {
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

.lostfound-card {
  margin-bottom: 20px;
}

.item-title {
  font-weight: 500;
}

.pagination-container {
  margin-top: 20px;
  text-align: center;
}
</style>