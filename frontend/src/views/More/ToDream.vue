<template>
  <div class="py-12">
    <h1 class="text-3xl font-bold mb-10" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">到梦活动</h1>
    <div class="card shadow-lg p-8" style="border-radius: 16px; min-height: 400px; margin-top: 50px; padding-top: 60px;">
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full bg-gradient-to-r from-pink-100 to-yellow-100 flex items-center justify-center mx-auto mb-6">
          <i class="fa fa-trophy" style="font-size: 3.5rem; background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);"></i>
        </div>
        <h2 class="text-2xl font-semibold" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">到梦空间活动管理</h2>
      </div>
      
      <div class="mb-10" style="padding: 0 10px;">
        <h3 class="text-xl font-semibold mb-4" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">关于到梦空间</h3>
        <p class="text-text-secondary leading-relaxed">
          到梦空间是一个专注于大学生第二课堂活动管理的平台，通过系统化、规范化的活动管理，帮助学生记录和管理参与的各类课外活动，提升综合素质。
        </p>
      </div>
      
      <div class="text-center mb-4" style="margin-top: 50px;">
        <button class="btn btn-primary" @click="openExternalLink('https://www.5idream.net/')">
          <i class="fa fa-external-link mr-2"></i> 访问到梦空间
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// 打开外部链接
const openExternalLink = (url) => {
  window.open(url, '_blank')
}
</script>

<style scoped>
.text-text-secondary {
  color: var(--color-text-secondary);
}

.card {
  border-radius: 12px;
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.btn-primary {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 25px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(255, 107, 157, 0.4);
}
</style>