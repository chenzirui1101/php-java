<template>
  <div class="not-found-container">
    <div class="not-found-content">
      <h1 class="error-code">404</h1>
      <h2 class="error-message">页面找不到了 😕</h2>
      <p class="error-desc">你访问的页面不存在或已被删除</p>
      <base-button type="primary" @click="goBack">返回首页</base-button>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import BaseButton from '@/components/Base/BaseButton.vue'

const router = useRouter()

// 返回首页
const goBack = () => {
  router.push('/home')
}
</script>

<style scoped lang="scss">
.not-found-container {
  width: 100%;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f5f5f5;

  .not-found-content {
    text-align: center;

    .error-code {
      font-size: 100px;
      font-weight: 700;
      color: #409eff;
      margin-bottom: 20px;
    }

    .error-message {
      font-size: 24px;
      color: #333;
      margin-bottom: 10px;
    }

    .error-desc {
      font-size: 16px;
      color: #666;
      margin-bottom: 30px;
    }
  }
}
</style>