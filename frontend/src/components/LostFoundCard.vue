<template>
  <div 
    class="lostfound-card bg-white rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 cursor-pointer transform hover:-translate-y-1 border border-gray-100 flex flex-col h-full"
    @click="goToDetail"
  >
    <!-- 物品图片 -->
    <div class="relative w-full aspect-square overflow-hidden bg-gray-50">
      <!-- 图片加载占位符 -->
      <div v-if="!item.coverImg && !item.image" class="absolute inset-0 flex items-center justify-center bg-gray-50 text-gray-400">
        <i class="fa fa-image text-3xl"></i>
      </div>
      
      <!-- 图片 -->
      <img
        :src="item.coverImg || item.image || 'https://picsum.photos/200/200'"
        alt="物品图片"
        class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
        @error="handleImageError"
        @load="handleImageLoad"
      />
      
      <!-- 图片加载错误显示 -->
      <div v-if="imageError" class="absolute inset-0 flex items-center justify-center bg-gray-50 text-gray-500">
        暂无图片
      </div>
      
      <!-- 状态标签 -->
      <div class="absolute top-2 left-2">
        <span 
          class="px-3 py-1 text-xs font-medium rounded-full"
          :class="item.status === 'found' ? 'bg-success text-white' : 'bg-primary text-white'"
        >
          {{ item.status === 'found' ? '已找到' : '寻找中' }}
        </span>
      </div>
    </div>
    
    <!-- 物品信息 -->
    <div class="p-4 flex-grow flex flex-col">
      <!-- 类型标签（第一行） -->
      <div class="mb-2">
        <span 
          class="px-2 py-1 rounded-full font-medium text-xs"
          :class="item.type === 'lost' ? 'bg-orange-50 text-orange-600' : 'bg-blue-50 text-blue-600'"
        >
          {{ item.type === 'lost' ? '寻物启事' : '失物招领' }}
        </span>
      </div>
      
      <!-- 标题（第二行） -->
      <div class="mb-2 overflow-hidden">
        <h3 class="font-medium text-gray-800 text-sm hover:text-orange-500 transition-colors duration-300 overflow-hidden text-nowrap text-truncate">
          {{ item.title || item.name }}
        </h3>
      </div>
      
      <!-- 描述（第三行） -->
      <div class="mb-2 overflow-hidden">
        <p class="text-gray-600 text-sm overflow-hidden text-nowrap text-truncate">
          {{ item.description || '暂无描述' }}
        </p>
      </div>
      
      <!-- 地点（第四行） -->
      <div class="mb-2 overflow-hidden">
        <p class="text-gray-600 text-sm overflow-hidden text-nowrap text-truncate">
          <i class="fa fa-map-marker text-gray-400 mr-1"></i>{{ item.location || '未知地点' }}
        </p>
      </div>
      
      <!-- 空行（第五行） -->
      <div class="mb-2"></div>
      
      <!-- 按钮与日期（第六行） -->
      <div class="d-flex justify-content-between align-items-center text-xs text-gray-500 mt-auto">
        <span 
          class="px-2 py-1 rounded-full font-medium"
          :class="item.type === 'lost' ? 'bg-orange-50 text-orange-600' : 'bg-blue-50 text-blue-600'"
        >
          详细
        </span>
        <span class="overflow-hidden text-nowrap text-truncate">{{ formattedDate }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

// 定义 props
const props = defineProps({
  item: {
    type: Object,
    required: true,
    default: () => ({})
  }
})

const router = useRouter()

// 图片错误状态
const imageError = ref(false)

// 处理图片加载错误
const handleImageError = () => {
  imageError.value = true
}

// 处理图片加载成功
const handleImageLoad = () => {
  imageError.value = false
}

// 格式化日期 - 使用computed缓存结果，显示完整的日期和时间
const formattedDate = computed(() => {
  const date = props.item.createTime || props.item.created_at
  if (!date) return '未知时间'
  try {
    const d = new Date(date)
    return d.toLocaleString('zh-CN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return '未知时间'
  }
})

// 跳转到详情页
const goToDetail = () => {
  if (props.item.id) {
    router.push({ name: 'LostFoundDetail', params: { id: props.item.id } })
  }
}
</script>

<style scoped>
/* 确保卡片在Bootstrap网格中正确显示 */
.lostfound-card {
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

/* 状态标签样式 */
.bg-success {
  background-color: #28a745;
}

.bg-primary {
  background-color: #007bff;
}

/* 类型标签样式 */
.bg-orange-50 {
  background-color: #fff7ed;
}

.text-orange-600 {
  color: #ea580c;
}

.bg-blue-50 {
  background-color: #eff6ff;
}

.text-blue-600 {
  color: #2563eb;
}

/* 标签文字样式 */
.text-gray-400 {
  color: #9ca3af;
}

/* 分隔线样式 */
.border-t {
  border-top: 1px solid #f3f4f6;
}

.border-gray-100 {
  border-color: #f3f4f6;
}
</style>
