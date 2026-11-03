<template>
  <div class="min-h-screen d-flex flex-column">
    <div class="container mx-auto px-4 py-8 flex-1">
      <!-- 子路由内容 -->
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
      <!-- 默认内容 -->
      <div v-if="!$route.name || $route.name === 'more'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 联系我们 -->
        <router-link to="/more/contact">
          <Card class="hover:shadow-lg transition-shadow p-6 cursor-pointer">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <Mail class="w-8 h-8" />
              </div>
              <div>
                <h2 class="text-xl font-semibold">联系我们</h2>
                <p class="text-muted-foreground mt-1">有问题或建议？请联系我们</p>
              </div>
            </div>
          </Card>
        </router-link>
        <!-- 关于我们 -->
        <router-link to="/more/about">
          <Card class="hover:shadow-lg transition-shadow p-6 cursor-pointer">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <Info class="w-8 h-8" />
              </div>
              <div>
                <h2 class="text-xl font-semibold">关于我们</h2>
                <p class="text-muted-foreground mt-1">了解潮青行的故事和团队</p>
              </div>
            </div>
          </Card>
        </router-link>
        <!-- 到梦活动 -->
        <router-link to="/more/to-dream">
          <Card class="hover:shadow-lg transition-shadow p-6 cursor-pointer">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <i class="fa fa-trophy" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h2 class="text-xl font-semibold">到梦活动</h2>
                <p class="text-muted-foreground mt-1">了解和参与到梦空间活动</p>
              </div>
            </div>
          </Card>
        </router-link>
        <!-- 学习通 -->
        <router-link to="/more/xuexitong">
          <Card class="hover:shadow-lg transition-shadow p-6 cursor-pointer">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <i class="fa fa-book" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h2 class="text-xl font-semibold">学习通</h2>
                <p class="text-muted-foreground mt-1">访问超星学习通平台</p>
              </div>
            </div>
          </Card>
        </router-link>
        <!-- 志愿者 -->
        <router-link to="/more/volunteer">
          <Card class="hover:shadow-lg transition-shadow p-6 cursor-pointer">
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                <i class="fa fa-heart" style="font-size: 2rem;"></i>
              </div>
              <div>
                <h2 class="text-xl font-semibold">志愿者</h2>
                <p class="text-muted-foreground mt-1">了解和参与志愿服务</p>
              </div>
            </div>
          </Card>
        </router-link>
      </div>
    </div>
    
    <!-- 页脚 -->
    <footer class="py-10" style="background: linear-gradient(135deg, rgba(44, 62, 80, 0.7), rgba(52, 73, 94, 0.7)); color: white; box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1); margin-top: 100px;">
      <div class="container">
        <div class="row">
          <div class="col-md-8 offset-md-2 text-center">
            <h3 class="font-weight-bold mb-5" style="font-size: 1.5rem; color: #FFD93D; margin-top: 20px;">重要提示</h3>
            <p class="mb-6" style="line-height: 1.8; font-size: 1rem; color: rgba(255, 255, 255, 0.9);">
              本站仅为个人毕业设计作品，所有商品信息均由用户自行发布。<br />
              请务必在交易前仔细核实对方身份及商品信息，谨防网络诈骗。<br />
              任何用户之间的交易纠纷及违法行为均与本站无关。<br />
              如发现违规信息，请联系本人处理。
            </p>
            <div class="mb-6">
              <router-link to="/more/about" class="mx-4" style="font-size: 1.1rem; color: #FFD93D; text-decoration: none; transition: all 0.3s ease;">
                关于我们
              </router-link>
              <router-link to="/more/contact" class="mx-4" style="font-size: 1.1rem; color: #FFD93D; text-decoration: none; transition: all 0.3s ease;">
                联系我们
              </router-link>
              <button @click="showPrivacyPolicy" class="mx-4 btn btn-link" style="font-size: 1.1rem; color: #FFD93D; text-decoration: none; transition: all 0.3s ease; background: none; border: none; padding: 0;">
                隐私政策
              </button>
              <button @click="showTermsOfUse" class="mx-4 btn btn-link" style="font-size: 1.1rem; color: #FFD93D; text-decoration: none; transition: all 0.3s ease; background: none; border: none; padding: 0;">
                使用条款
              </button>
            </div>
            <p style="font-size: 0.875rem; color: rgba(255, 255, 255, 0.8);">© 2026 潮青行. All rights reserved.</p>
          </div>
        </div>
      </div>
    </footer>
    
    <!-- 用户协议模态框（用于显示隐私政策和使用条款） -->
    <AuthModal 
      v-if="showTermsModal" 
      :show-terms-directly="true"
      @close="showTermsModal = false" 
    />
  </div>
</template>

<script setup>
import Card from '@/components/ui/Card'
import { Mail, Info } from 'lucide-vue-next'
import { ref } from 'vue'
import AuthModal from '@/components/auth/AuthModal.vue'

// 显示用户协议模态框状态
const showTermsModal = ref(false)

// 显示隐私政策（复用用户协议模态框）
const showPrivacyPolicy = () => {
  showTermsModal.value = true
}

// 显示使用条款（复用用户协议模态框）
const showTermsOfUse = () => {
  showTermsModal.value = true
}
</script>