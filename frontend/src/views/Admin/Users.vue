<template>
  <div class="users-container">
    <div class="page-header">
      <h2>用户管理</h2>
      <div class="search-box">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            v-model="searchKeyword"
            placeholder="搜索用户名、姓名或邮箱"
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
    
    <div class="card users-card shadow">
      <div class="card-body">
        <!-- 加载状态 -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="ml-2">加载中...</span>
        </div>
        
        <!-- 用户列表 -->
        <table v-else class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th scope="col" class="text-center" style="width: 80px">用户ID</th>
              <th scope="col" class="text-center" style="width: 150px">用户名</th>
              <th scope="col" class="text-center" style="width: 120px">姓名</th>
              <th scope="col" style="min-width: 200px">邮箱</th>
              <th scope="col" class="text-center" style="width: 150px">注册时间</th>
              <th scope="col" class="text-center" style="width: 120px">发布商品数</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in usersList" :key="user.id">
              <td class="text-center">{{ user.id }}</td>
              <td class="text-center" :title="user.username">{{ truncateText(user.username) }}</td>
              <td class="text-center" :title="user.name">{{ truncateText(user.name) }}</td>
              <td class="user-email" :title="user.email">{{ truncateText(user.email) }}</td>
              <td class="text-center">{{ user.createTime }}</td>
              <td class="text-center">
                <span class="badge badge-success">{{ user.productCount }}</span>
              </td>
            </tr>
            <tr v-if="usersList.length === 0">
              <td colspan="6" class="text-center">暂无用户数据</td>
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

// 用户列表相关状态
const usersList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(10)
const total = ref(0)
const searchKeyword = ref('')

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取用户列表
const getUsers = async () => {
  try {
    loading.value = true
    const res = await request({
      url: '/api/admin/users',
      method: 'get',
      params: {
        pageNum: pageNum.value,
        pageSize: pageSize.value,
        keyword: searchKeyword.value
      }
    })
    if (res.code === 200) {
      usersList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取用户列表失败:', err)
    alert('获取用户列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pageNum.value = 1
  getUsers()
}

// 分页处理
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getUsers()
}

// 文本截断函数
const truncateText = (text) => {
  if (!text) return ''
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}

// 页面加载时获取数据
onMounted(() => {
  getUsers()
})
</script>

<style scoped>
.users-container {
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

.users-card {
  margin-bottom: 20px;
}

.user-email {
  word-break: break-all;
}

.pagination-container {
  margin-top: 20px;
  text-align: center;
}
</style>