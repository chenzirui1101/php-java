<template>
  <div>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-3xl font-bold mb-8 text-center" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">关于我们</h1>
    <div class="card shadow-lg rounded-lg overflow-hidden">
      <div class="card-body p-6">
        <div class="space-y-8">
          <div>
            <h2 class="text-xl font-semibold mb-4" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">关于潮青行</h2>
            <p class="text-muted leading-relaxed">
              潮青行是一个面向大学生的校园生活服务平台，致力于为同学们提供便捷、安全的物品展示、校园闲聊、失物招领等服务。
              我们的愿景是打造一个充满活力和温暖的校园社区，让每一位同学都能在这里找到所需，分享快乐。
            </p>
          </div>
          <div>
            <h2 class="text-xl font-semibold mb-4" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">我们的使命</h2>
            <p class="text-muted leading-relaxed">
              连接校园里的每一个人，让闲置资源得到有效利用，让信息传递更加顺畅，让校园生活更加丰富多彩。
            </p>
          </div>
          <div>
            <h2 class="text-xl font-semibold mb-4" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);">团队介绍</h2>
            <div class="row justify-content-center">
              <div v-for="i in 5" :key="i" class="col-sm-6 col-md-4 mb-4 d-flex justify-content-center">
                <div class="text-center">
                  <div class="w-24 h-24 rounded-full bg-gradient-to-r from-pink-100 to-yellow-100 d-flex align-items-center justify-center mx-auto mb-3">
                    <span class="text-2xl font-bold text-primary">{{ i }}</span>
                  </div>
                  <h3 class="font-medium">团队成员 {{ i }}</h3>
                  <p class="text-sm text-muted mt-1">kkbb135</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
</script>