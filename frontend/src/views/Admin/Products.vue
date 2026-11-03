<template>
  <div class="products-container">
    <div class="page-header">
      <h2>商品管理</h2>
      <div class="search-box">
        <div class="input-group">
          <input
            type="text"
            class="form-control"
            v-model="searchKeyword"
            placeholder="搜索商品名称、描述或卖家"
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
    
    <div class="card products-card shadow">
      <div class="card-body">
        <!-- 加载状态 -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <span class="ml-2">加载中...</span>
        </div>
        
        <!-- 商品列表 -->
        <table v-else class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th scope="col" class="text-center" style="width: 80px">商品ID</th>
              <th scope="col" class="text-center" style="width: 100px">商品图片</th>
              <th scope="col" style="min-width: 200px">商品名称</th>
              <th scope="col" class="text-right" style="width: 100px">价格</th>
              <th scope="col" class="text-center" style="width: 120px">分类</th>
              <th scope="col" class="text-center" style="width: 120px">卖家</th>
              <th scope="col" class="text-center" style="width: 150px">发布时间</th>
              <th scope="col" class="text-center" style="width: 150px">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in productsList" :key="product.id">
              <td class="text-center">{{ product.id }}</td>
              <td class="text-center">
                <img
                  :src="product.coverImg || 'https://picsum.photos/60/60'"
                  alt="商品图片"
                  class="img-thumbnail"
                  style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px"
                >
              </td>
              <td class="product-title" :title="product.title">{{ truncateText(product.title) }}</td>
              <td class="text-right product-price">¥{{ product.price }}</td>
              <td class="text-center">{{ product.category }}</td>
              <td class="text-center">{{ product.sellerName }}</td>
              <td class="text-center">{{ product.createTime }}</td>
              <td class="text-center">
                <button 
                  class="btn btn-danger btn-sm" 
                  @click="handleDelete(product.id)"
                >
                  删除
                </button>
              </td>
            </tr>
            <tr v-if="productsList.length === 0">
              <td colspan="8" class="text-center">暂无商品数据</td>
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

// 商品列表相关状态
const productsList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(10)
const total = ref(0)
const searchKeyword = ref('')

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取商品列表
const getProducts = async () => {
  try {
    loading.value = true
    const res = await request({
      url: '/api/admin/products',
      method: 'get',
      params: {
        pageNum: pageNum.value,
        pageSize: pageSize.value,
        keyword: searchKeyword.value
      }
    })
    if (res.code === 200) {
      productsList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取商品列表失败:', err)
    alert('获取商品列表失败')
  } finally {
    loading.value = false
  }
}

// 删除商品
const handleDelete = async (id) => {
  try {
    if (confirm('确定要删除这个商品吗？')) {
      const res = await request({
        url: `/api/admin/products/${id}`,
        method: 'delete'
      })
      
      if (res.code === 200) {
        alert('删除成功')
        // 刷新列表
        await getProducts()
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
  getProducts()
}

// 分页处理
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getProducts()
}

// 文本截断函数
const truncateText = (text) => {
  if (!text) return ''
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}

// 页面加载时获取数据
onMounted(() => {
  getProducts()
})
</script>

<style scoped>
.products-container {
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

.products-card {
  margin-bottom: 20px;
}

.product-title {
  font-weight: 500;
}

.product-price {
  font-weight: 600;
  color: #f56c6c;
}

.pagination-container {
  margin-top: 20px;
  text-align: center;
}
</style>