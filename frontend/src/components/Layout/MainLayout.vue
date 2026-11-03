<template>
  <div class="min-h-screen flex flex-col">
    <AppHeader />
    <!-- 大屏图片（仅在关于我们页面显示） -->
    <div v-if="$route.path === '/more/about'" class="w-100 overflow-hidden p-0 m-0" style="width: 100vw; height: 80vh; min-height: 300px; margin: 0; padding: 0;">
      <img src="/R-C (1).png" alt="关于我们" class="w-100 h-100 object-cover" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <main class="flex-1 bg-background-light">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import AppHeader from './AppHeader.vue'
</script>