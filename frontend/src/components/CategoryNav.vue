<template>
  <div class="category-nav-wrapper">
    <div class="category-nav">
      <div 
        class="category-item"
        :class="{ active: item.id === currentId }"
        v-for="item in categories"
        :key="item.id"
        @click="handleClick(item.id)"
      >
        {{ item.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

// 定义 props
const props = defineProps({
  categories: {
    type: Array,
    required: true,
    default: () => []
  },
  defaultId: {
    type: [Number, String],
    default: 0
  }
})

// 定义事件 - 修复：使用 defineEmits
const emit = defineEmits(['change'])

// 当前选中分类
const currentId = ref(props.defaultId)

// 监听 defaultId 变化
watch(() => props.defaultId, (newVal) => {
  currentId.value = newVal
})

// 点击分类
const handleClick = (id) => {
  currentId.value = id
  emit('change', id)
}
</script>

<style scoped>
.category-nav-wrapper {
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 10px;
  /* 添加滚动条样式 */
  scrollbar-width: thin;
  scrollbar-color: #ccc transparent;
}

/* 滚动条样式 - WebKit */
.category-nav-wrapper::-webkit-scrollbar {
  height: 6px;
}

.category-nav-wrapper::-webkit-scrollbar-track {
  background: transparent;
}

.category-nav-wrapper::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 3px;
}

.category-nav {
  display: flex;
  gap: 20px;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
  min-width: min-content;
}
.category-item {
  padding: 8px 16px;
  font-size: 16px;
  cursor: pointer;
  border-radius: 20px;
  transition: all 0.3s;
  white-space: nowrap;
}
.category-item.active {
  background-color: #409eff;
  color: #fff;
}
.category-item:hover:not(.active) {
  color: #409eff;
}
</style>