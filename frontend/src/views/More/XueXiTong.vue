<template>
  <div class="py-12">
    <h1 class="text-3xl font-bold mb-10" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">学习通</h1>
    <div class="card shadow-lg p-8" style="border-radius: 16px; min-height: 400px; margin-top: 50px; padding-top: 60px;">
      <div class="text-center mb-8">
        <div class="w-24 h-24 rounded-full bg-gradient-to-r from-pink-100 to-yellow-100 flex items-center justify-center mx-auto mb-6">
          <i class="fa fa-book" style="font-size: 3.5rem; background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);"></i>
        </div>
        <h2 class="text-2xl font-semibold" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">超星学习通</h2>
      </div>
      
      <div class="mb-10" style="padding: 0 10px;">
        <h3 class="text-xl font-semibold mb-4" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">关于学习通</h3>
        <p class="text-text-secondary leading-relaxed">
          学习通是超星公司开发的一款在线学习平台，为学生提供课程学习、考试、作业、讨论等功能，支持多种终端访问，方便学生随时随地进行学习。
        </p>
      </div>
      
      <div class="text-center mb-4" style="margin-top: 50px;">
        <button class="btn btn-primary" @click="openExternalLink('https://i.chaoxing.com/')">
          <i class="fa fa-external-link mr-2"></i> 访问学习通
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