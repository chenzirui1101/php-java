<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 页面标题 -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-text-primary">物品展示</h1>
    </div>
    
    <!-- 搜索和分类筛选区 -->
    <div class="mb-6 p-4 bg-white rounded-lg shadow-sm">
      <div class="row">
        <div class="col-md-8 mb-4 mb-md-0">
          <div class="search-wrapper">
            <input 
              type="text" 
              class="search-input" 
              placeholder="搜索商品..." 
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
            <option v-for="category in categoryList" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- 商品列表 -->
    <div class="row">
      <div v-for="product in productList" :key="product.id" class="col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="h-100">
          <ProductCard 
            :product="product" 
            :is-favorite="favoriteStatusMap[product.id] || false"
            @favorite-change="handleFavoriteChange"
          />
        </div>
      </div>
    </div>
    
    <!-- 空状态 -->
    <div v-if="productList.length === 0 && !loading" class="card shadow-sm p-5 text-center">
      <div class="text-text-secondary">暂无商品</div>
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
import { productApi } from '@/api/modules/product'
import ProductCard from '@/components/ProductCard.vue'
import SiteFooter from '@/components/SiteFooter.vue'

// 分类列表
const categoryList = ref([
  { id: 1, name: '数码产品' },
  { id: 2, name: '生活用品' },
  { id: 3, name: '学习资料' },
  { id: 4, name: '服饰鞋包' },
  { id: 5, name: '其他' }
])

// 商品列表相关
const productList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(12)
const total = ref(0)
const currentCategory = ref(0)
const searchKeyword = ref('')
// 初始化router
const router = useRouter()

// 收藏状态映射表，key: productId, value: boolean
const favoriteStatusMap = ref({})

// 计算总页数
const totalPages = computed(() => {
  return Math.ceil(total.value / pageSize.value)
})

// 获取商品列表
const getProductList = async () => {
  try {
    loading.value = true
    
    const params = {
      pageNum: pageNum.value,
      pageSize: pageSize.value,
      categoryId: currentCategory.value,
      keyword: searchKeyword.value
    }
    
    const res = await productApi.getProductList(params)
    
    if (res.code === 200) {
      productList.value = res.data.list || []
      total.value = res.data.total || 0
    }
  } catch (err) {
    console.error('获取商品列表失败:', err)
  } finally {
    loading.value = false
  }
}

// 处理搜索
const handleSearch = () => {
  pageNum.value = 1
  getProductList()
}

// 处理分类变化
const handleCategoryChange = () => {
  pageNum.value = 1
  getProductList()
}

// 处理分页变化
const handlePageChange = (newPage) => {
  if (newPage < 1 || newPage > totalPages.value) return
  pageNum.value = newPage
  getProductList()
}

// 处理收藏状态变化
const handleFavoriteChange = ({ productId, isFavorite }) => {
  favoriteStatusMap.value[productId] = isFavorite
}

// 跳转到发布商品页面
const goToPublish = () => {
  // 直接跳转，让路由守卫处理登录逻辑
  router.push('/publish')
}

// 页面加载时获取数据
onMounted(() => {
  getProductList()
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
