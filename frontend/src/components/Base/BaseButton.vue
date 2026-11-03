<template>
  <button
    :class="buttonClasses"
    :disabled="disabled || loading"
    @click="handleClick"
    class="base-button"
  >
    <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
    <slot></slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// 定义属性
const props = defineProps({
  type: {
    type: String,
    default: 'primary' // primary / success / warning / danger / info
  },
  size: {
    type: String,
    default: 'default' // large / default / small
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// 定义事件
const emit = defineEmits(['click'])

// 映射Element Plus类型到Bootstrap样式
const typeMap = {
  primary: 'btn-primary',
  success: 'btn-success',
  warning: 'btn-warning',
  danger: 'btn-danger',
  info: 'btn-info'
}

// 映射Element Plus尺寸到Bootstrap样式
const sizeMap = {
  large: 'btn-lg',
  default: '',
  small: 'btn-sm'
}

// 计算按钮类名
const buttonClasses = computed(() => {
  return [
    'btn',
    typeMap[props.type] || typeMap.primary,
    sizeMap[props.size] || '',
    {
      'disabled': props.disabled || props.loading
    }
  ]
})

// 点击事件
const handleClick = (e) => {
  emit('click', e)
}
</script>

<style scoped lang="scss">
.base-button {
  border-radius: 4px;
  &:hover:not(:disabled) {
    opacity: 0.9;
  }
}
</style>