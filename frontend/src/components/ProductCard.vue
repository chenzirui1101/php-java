<template>
  <div 
    class="product-card bg-white rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:-translate-y-1 border border-gray-100 flex flex-col h-full"
    @click="goToDetail"
  >
    <!-- 商品图片 -->
    <div class="relative w-full aspect-square overflow-hidden bg-gray-50">
      <!-- 图片加载占位符 -->
      <div v-if="!product.coverImg && !product.image" class="absolute inset-0 flex items-center justify-center bg-gray-50 text-gray-400">
        <i class="fa fa-image text-3xl"></i>
      </div>
      
      <!-- 图片预加载 -->
      <img
        :src="product.coverImg || product.image || 'https://picsum.photos/200/200'"
        alt="商品图片"
        class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
        @error="handleImageError"
        @load="handleImageLoad"
      />
      
      <!-- 图片加载错误显示 -->
      <div v-if="imageError" class="absolute inset-0 flex items-center justify-center bg-gray-50 text-gray-500">
        暂无图片
      </div>
      <!-- 收藏按钮 -->
      <button
        class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center transition-all duration-300 hover:bg-white hover:scale-110 shadow-sm"
        @click.stop="handleFavorite"
        :class="{ 'opacity-50': loading }"
        :disabled="loading"
      >
        <i
          :class="isFavorite ? 'fa fa-star text-orange-400' : 'fa fa-star-o text-gray-400'"
          class="h-4 w-4 transition-colors duration-300"
        ></i>
      </button>
    </div>
    <!-- 商品信息 -->
    <div class="p-4 flex-grow flex flex-col">
      <div class="mb-3">
        <h3 class="font-medium text-gray-800 text-sm mb-2 hover:text-orange-500 transition-colors duration-300 product-card-title">
          {{ product.title || product.name }}
        </h3>
        <div class="text-orange-500 font-bold text-lg mb-2">¥{{ formattedPrice }}</div>
      </div>
      
      <div class="d-flex justify-content-between align-items-center text-xs text-gray-500 mt-auto">
        <span class="bg-orange-50 px-2 py-1 rounded-full text-orange-600 font-medium">{{ product.category || '其他' }}</span>
        <span>{{ formattedDate }}</span>
      </div>
      
      <!-- 我的商品操作（个人中心显示） -->
      <div v-if="isMine" class="mt-3 pt-3 border-top product-actions">
        <div class="row g-2">
          <div class="col-6">
            <button 
              class="btn btn-outline-primary btn-sm w-100"
              @click.stop="handleEdit"
            >
              <i class="fa fa-pencil me-1"></i>编辑
            </button>
          </div>
          <div class="col-6">
            <button 
              class="btn btn-outline-danger btn-sm w-100"
              @click.stop="handleDelete"
            >
              <i class="fa fa-trash me-1"></i>删除
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, onMounted, computed, watch } from 'vue'
import { productApi } from '@/api/modules/product'
import { useUserStore } from '@/stores/modules/user'
import request from '@/utils/request'

// 定义 props
const props = defineProps({
  product: {
    type: Object,
    required: true,
    default: () => ({})
  },
  isMine: {
    type: Boolean,
    default: false
  },
  isFavorite: {
    type: Boolean,
    default: false
  }
})

// 定义事件
const emit = defineEmits(['delete', 'favorite-change'])

const router = useRouter()
const userStore = useUserStore()

// 图片错误状态
const imageError = ref(false)

// 收藏状态 - 如果外部传入了isFavorite，则使用外部值，否则使用内部状态
const isFavorite = ref(props.isFavorite)
const loading = ref(false)

// 监听外部isFavorite属性变化
watch(() => props.isFavorite, (newValue) => {
  isFavorite.value = newValue
})

// 处理图片加载错误
const handleImageError = () => {
  imageError.value = true
}

// 处理图片加载成功
const handleImageLoad = () => {
  imageError.value = false
}

// 检查商品是否已收藏 - 只有当没有外部传入isFavorite时才执行
const checkFavorite = async () => {
  // 如果外部传入了isFavorite，就不需要再检查了
  if (props.isFavorite !== undefined || !userStore.isLogin || !props.product.id) return
  
  // 防止重复请求
  if (loading.value) return
  
  try {
    loading.value = true
    // 添加请求超时处理，5秒后自动超时
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => reject(new Error('请求超时')), 5000)
    })
    
    const apiPromise = request({
      url: `/api/favorites/check/${props.product.id}`,
      method: 'get'
    })
    
    // 竞争条件，谁先完成就用谁的结果
    const res = await Promise.race([apiPromise, timeoutPromise])
    
    if (res.code === 200) {
      isFavorite.value = res.data.is_favorite
    }
  } catch (err) {
    // 只在控制台打印警告，避免产生大量错误日志
    console.warn('检查收藏状态超时，使用默认状态', err.message)
    // 设置默认状态为未收藏，避免影响用户体验
    isFavorite.value = false
  } finally {
    loading.value = false
  }
}

// 处理收藏/取消收藏
const handleFavorite = async () => {
  if (!userStore.isLogin) {
    alert('请先登录')
    // 触发全局登录模态框，而不是跳转到老的登录页面
    // 这里可以使用事件总线或Pinia store来触发登录模态框
    return
  }
  
  if (loading.value) return
  
  try {
    loading.value = true
    
    let newIsFavorite = !isFavorite.value
    
    if (isFavorite.value) {
      // 取消收藏
      const res = await request({
        url: `/api/favorites/${props.product.id}`,
        method: 'delete'
      })
      if (res.code === 200) {
        isFavorite.value = false
        alert('取消收藏成功')
      } else {
        newIsFavorite = true
      }
    } else {
      // 添加收藏
      const res = await request({
        url: '/api/favorites',
        method: 'post',
        data: {
          product_id: props.product.id
        }
      })
      if (res.code === 200) {
        isFavorite.value = true
        alert('收藏成功')
      } else {
        newIsFavorite = false
      }
    }
    
    // 触发收藏状态变化事件
    emit('favorite-change', {
      productId: props.product.id,
      isFavorite: newIsFavorite
    })
  } catch (err) {
    console.error('收藏操作失败:', err)
    alert('收藏操作失败')
  } finally {
    loading.value = false
  }
}

// 组件挂载时检查收藏状态
onMounted(() => {
  checkFavorite()
})

// 格式化价格 - 使用computed缓存结果
const formattedPrice = computed(() => {
  const price = props.product.price
  if (!price) return '0.00'
  return Number(price).toFixed(2)
})

// 格式化日期 - 使用computed缓存结果
const formattedDate = computed(() => {
  const date = props.product.createTime || props.product.created_at
  if (!date) return '未知时间'
  try {
    const d = new Date(date)
    return d.toLocaleDateString()
  } catch (e) {
    return '未知时间'
  }
})

// 跳转到商品详情
const goToDetail = () => {
  if (props.product.id) {
    router.push(`/product/${props.product.id}`)
  }
}

// 编辑商品（跳转到发布页，带参数）
const handleEdit = (e) => {
  e.stopPropagation()
  if (props.product.id) {
    router.push({ 
      name: 'PublishProduct', 
      query: { id: props.product.id, edit: 'true' } 
    })
  }
}

// 删除商品
const handleDelete = async (e) => {
  e.stopPropagation()
  
  try {
    if (confirm('确定要删除这个商品吗？')) {
      // 调用删除 API
      const res = await productApi.deleteProduct(props.product.id)
      
      if (res.code === 200) {
        alert('删除成功')
        emit('delete', props.product.id) // 通知父组件刷新列表
      } else {
        alert(res.msg || '删除失败')
      }
    }
  } catch (err) {
    console.error('删除失败:', err)
    alert('删除失败')
  }
}
</script>

<style scoped>
/* 确保卡片在Bootstrap网格中正确显示 */
.product-card {
  width: 100% !important;
  max-width: 100% !important;
  box-sizing: border-box;
}

/* 确保图片容器正确显示 */
.aspect-square {
  aspect-ratio: 1 / 1;
  overflow: hidden;
}

/* 确保图片正确填充 */
.aspect-square img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* 商品标题单行省略 */
.product-card-title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 产品操作按钮样式 */
.product-actions {
  border-color: #e9ecef !important;
}

.product-actions .btn {
  font-size: 0.8rem;
  padding: 0.375rem 0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.product-actions .btn-outline-primary:hover {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.product-actions .btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}
</style>