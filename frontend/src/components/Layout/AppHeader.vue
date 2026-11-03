<template>
  <div>
    <!-- 导航条2：滚动显示/隐藏导航条 -->
  <nav class="navbar navbar-expand-md navbar-light fixed-top" :style="navbarStyle" id="navbar-scroll">
    <div class="container-fluid" style="padding: 0 1.5rem;">
      <!-- Logo -->
      <router-link to="/" class="navbar-brand font-weight-bold d-flex align-items-center" style="background-clip: text; -webkit-background-clip: text; color: transparent; background-image: linear-gradient(to right, var(--color-primary), var(--color-secondary-yellow)); font-size: 1.8rem;">
        <img src="/logo3.ico" alt="潮青行" class="mr-2" style="height: 50px; width: auto;" />
        潮青行
      </router-link>

        <!-- 移动端汉堡菜单按钮 -->
        <button class="navbar-toggler" type="button" @click="toggleMobileMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- 桌面端导航菜单 -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link text-text-primary hover:text-primary transition-colors font-weight-bold" style="font-size: 1.1rem;">
                <i class="fa fa-home mr-1"></i> 首页
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/campus-hot" class="nav-link text-text-primary hover:text-primary transition-colors font-weight-bold" style="font-size: 1.1rem;">
                <i class="fa fa-comments mr-1"></i> 校园论坛
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/lostfound" class="nav-link text-text-primary hover:text-primary transition-colors font-weight-bold" style="font-size: 1.1rem;">
                <i class="fa fa-search mr-1"></i> 失物招领
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/secondhand" class="nav-link text-text-primary hover:text-primary transition-colors font-weight-bold" style="font-size: 1.1rem;">
                <i class="fa fa-shopping-cart mr-1"></i> 物品展示
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/campus-activities" class="nav-link text-text-primary hover:text-primary transition-colors font-weight-bold" style="font-size: 1.1rem;">
                <i class="fa fa-calendar-check-o mr-1"></i> 校园活动
              </router-link>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-text-primary hover:text-primary transition-colors font-weight-bold" href="#" id="moreDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 1.1rem;">
                <i class="fa fa-ellipsis-v mr-1"></i> 更多
              </a>
              <div class="dropdown-menu shadow-lg border-0 rounded-lg mt-2" aria-labelledby="moreDropdown" style="min-width: 180px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); padding: 8px 0;">
                <router-link to="/diy-album" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-paint-brush mr-3 text-primary" style="width: 20px;"></i> DIY纪念册
                </router-link>
                <router-link to="/more/to-dream" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-trophy mr-3 text-primary" style="width: 20px;"></i> 到梦活动
                </router-link>
                <router-link to="/more/xuexitong" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-book mr-3 text-primary" style="width: 20px;"></i> 学习通
                </router-link>
                <router-link to="/more/volunteer" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-heart mr-3 text-primary" style="width: 20px;"></i> 志愿者
                </router-link>
                <div class="dropdown-divider my-1 mx-4"></div>
                <router-link to="/more/about" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-info-circle mr-3 text-primary" style="width: 20px;"></i> 关于我们
                </router-link>
                <router-link to="/more/contact" class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';">
                  <i class="fa fa-envelope mr-3 text-primary" style="width: 20px;"></i> 联系我们
                </router-link>
                <button class="dropdown-item d-flex items-center py-2 px-4" style="transition: all 0.3s ease; background: transparent; border: none; text-align: left; width: 100%;" onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';" @click="showDownloadAppModal = true">
                  <i class="fa fa-download mr-3 text-primary" style="width: 20px;"></i> 下载App
                </button>
              </div>
            </li>
          </ul>

          <!-- 右侧操作区 -->
          <div class="ml-auto d-flex align-items-center">
            <!-- 消息中心图标 -->
            <router-link 
              to="/chat" 
              class="btn btn-link mr-3 position-relative" 
              style="font-size: 1.2rem; color: var(--color-primary); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s ease;"
              onmouseover="this.style.backgroundColor='rgba(255, 138, 92, 0.1)'; this.style.transform='scale(1.1)';"
              onmouseout="this.style.backgroundColor='transparent'; this.style.transform='scale(1)';"
            >
              <i class="fa fa-bell"></i>
              <!-- 未读消息数量徽章 -->
              <span v-if="userStore.unreadMessageCount > 0" class="position-absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-danger text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold" style="min-width: 20px; min-height: 20px;">
                {{ userStore.unreadMessageCount > 99 ? '99+' : userStore.unreadMessageCount }}
              </span>
            </router-link>
            
            <!-- 未登录状态 -->
            <div v-if="!isLogin" class="d-flex align-items-center">
              <button 
                class="btn btn-outline-primary mr-2 d-flex align-items-center" 
                @click="showAuthModal = true" 
                style="font-size: 0.95rem; padding: 0.5rem 1.25rem; border-radius: 20px; transition: all 0.3s ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(255, 138, 92, 0.3)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';"
              >
                <i class="fa fa-sign-in mr-2"></i> 登录
              </button>
              <button 
                class="btn btn-primary d-flex align-items-center" 
                @click="showAuthModal = true" 
                style="font-size: 0.95rem; padding: 0.5rem 1.25rem; border-radius: 20px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); border: none; transition: all 0.3s ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 157, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';"
              >
                <i class="fa fa-user-plus mr-2"></i> 注册
              </button>
            </div>

            <!-- 已登录状态 - 用户菜单 -->
            <div v-else class="dropdown d-inline-block user-dropdown">
              <button 
                class="btn btn-link dropdown-toggle d-flex align-items-center" 
                type="button" 
                id="userDropdown" 
                data-toggle="dropdown" 
                aria-haspopup="true" 
                aria-expanded="false"
                style="font-size: 1rem; padding: 0.5rem 1rem; border-radius: 25px; background: rgba(255, 138, 92, 0.1); border: 1px solid rgba(255, 138, 92, 0.2); transition: all 0.3s ease; text-decoration: none;"
                onmouseover="this.style.background='rgba(255, 138, 92, 0.2)'; this.style.boxShadow='0 4px 12px rgba(255, 138, 92, 0.2)';"
                onmouseout="this.style.background='rgba(255, 138, 92, 0.1)'; this.style.boxShadow='none';"
              >
                <div class="rounded-circle text-white font-weight-bold d-flex align-items-center justify-content-center mr-2" style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); width: 32px; height: 32px; font-size: 0.9rem;">
                  {{ user.name?.charAt(0) || 'U' }}
                </div>
                <span class="text-dark font-weight-medium">{{ user.name }}</span>
                <i class="fa fa-chevron-down ml-2 text-muted" style="font-size: 0.75rem;"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right shadow-lg border-0 mt-2" aria-labelledby="userDropdown" style="border-radius: 12px; min-width: 180px;">
                <div class="dropdown-header bg-light text-center py-3" style="border-radius: 12px 12px 0 0;">
                  <div class="rounded-circle text-white font-weight-bold d-flex align-items-center justify-content-center mx-auto mb-2" style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); width: 50px; height: 50px; font-size: 1.2rem;">
                    {{ user.name?.charAt(0) || 'U' }}
                  </div>
                  <span class="font-weight-bold text-dark">{{ user.name }}</span>
                </div>
                <router-link 
                  to="/profile" 
                  class="dropdown-item py-2 px-4 d-flex align-items-center" 
                  style="transition: all 0.2s ease;"
                  onmouseover="this.style.backgroundColor='#fff5f0'; this.style.color='#FF6B9D';"
                  onmouseout="this.style.backgroundColor='transparent'; this.style.color='inherit';"
                >
                  <i class="fa fa-user mr-3 text-primary" style="width: 20px;"></i> 个人中心
                </router-link>
                <div class="dropdown-divider my-1"></div>
                <button 
                  class="dropdown-item py-2 px-4 d-flex align-items-center text-danger" 
                  @click="handleLogout"
                  style="transition: all 0.2s ease;"
                  onmouseover="this.style.backgroundColor='#fff5f5';"
                  onmouseout="this.style.backgroundColor='transparent';"
                >
                  <i class="fa fa-sign-out mr-3" style="width: 20px;"></i> 退出登录
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- 移动端侧边菜单 -->
    <div class="mobile-side-menu" :class="{ 'open': isMobileMenuOpen }">
      <div class="mobile-menu-header">
        <h3 class="mb-0">菜单</h3>
        <button class="close-btn" @click="toggleMobileMenu">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="mobile-menu-content">
        <ul class="mobile-nav-list">
          <li class="mobile-nav-item">
            <router-link to="/" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-home mr-2"></i> 首页
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/campus-hot" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-comments mr-2"></i> 校园论坛
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/lostfound" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-search mr-2"></i> 失物招领
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/secondhand" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-shopping-cart mr-2"></i> 物品展示
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/campus-activities" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-calendar-check-o mr-2"></i> 校园活动
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/diy-album" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-paint-brush mr-2"></i> DIY纪念册
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/more/to-dream" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-trophy mr-2"></i> 到梦活动
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/more/xuexitong" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-book mr-2"></i> 学习通
            </router-link>
          </li>
          <li class="mobile-nav-item">
            <router-link to="/more/volunteer" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-heart mr-2"></i> 志愿者
            </router-link>
          </li>
          <li class="mobile-nav-item" v-if="!userStore.isLogin">
            <button class="mobile-nav-link btn-link" @click="showAuthModal = true; toggleMobileMenu()">
              <i class="fa fa-sign-in mr-2"></i> 登录/注册
            </button>
          </li>
          <li class="mobile-nav-item" v-else>
            <router-link to="/profile" class="mobile-nav-link" @click="toggleMobileMenu">
              <i class="fa fa-user mr-2"></i> 个人中心
            </router-link>
          </li>
          <li class="mobile-nav-item" v-if="userStore.isLogin">
            <button class="mobile-nav-link btn-link text-danger" @click="handleLogout; toggleMobileMenu()">
              <i class="fa fa-sign-out mr-2"></i> 退出登录
            </button>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- 侧边菜单遮罩层 -->
    <div class="mobile-menu-overlay" v-if="isMobileMenuOpen" @click="toggleMobileMenu"></div>
    
    <!-- 导航条占位元素，避免内容重叠 -->
  <div id="navbar-placeholder" style="height: 70px;"></div>
    
    <!-- 登录注册模态框 -->
    <AuthModal 
      v-if="showAuthModal" 
      @close="showAuthModal = false" 
      @success="handleAuthSuccess"
    />
    
    <!-- 下载App模态框 -->
    <DownloadAppModal 
      v-if="showDownloadAppModal" 
      @close="showDownloadAppModal = false"
    />

  </div>
</template>

<style scoped>
/* 移动端侧边菜单样式 */
.mobile-side-menu {
  position: fixed;
  top: 0;
  left: -300px;
  width: 300px;
  height: 100vh;
  background-color: white;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
  z-index: 1050;
  transition: left 0.3s ease;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.mobile-side-menu.open {
  left: 0;
}

.mobile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e0e0e0;
}

.mobile-menu-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: #333;
}

.mobile-menu-content {
  flex: 1;
  padding: 1rem 0;
}

.mobile-nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.mobile-nav-item {
  border-bottom: 1px solid #f0f0f0;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  color: #333;
  text-decoration: none;
  transition: all 0.3s ease;
  font-weight: 500;
}

.mobile-nav-link:hover {
  background-color: #f5f5f5;
  color: #ff6b6b;
  text-decoration: none;
}

.mobile-nav-item:last-child {
  border-bottom: none;
}

/* 侧边菜单遮罩层 */
.mobile-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1049;
  transition: opacity 0.3s ease;
}

/* 响应式设计 */
@media (min-width: 768px) {
  .mobile-side-menu {
    display: none;
  }
  
  .mobile-menu-overlay {
    display: none;
  }
}
</style>

<script setup>
import { ref, onMounted, onUnmounted, reactive, watch, computed, watchEffect } from 'vue'
import { useUserStore } from '@/stores/modules/user'
import { useRouter } from 'vue-router'
import AuthModal from '@/components/auth/AuthModal.vue'
import DownloadAppModal from '@/components/DownloadAppModal.vue'
import { chatApi } from '@/api/modules/chat'

const userStore = useUserStore()
// 使用computed属性确保user状态响应式更新，注意：userStore中的state属性是userInfo，不是user
const user = computed(() => userStore.userInfo)
// 登录状态计算属性
const isLogin = computed(() => userStore.isLogin)
const router = useRouter()
const showAuthModal = ref(false)
// 下载App模态框状态
const showDownloadAppModal = ref(false)
// 移动端菜单状态
const isMobileMenuOpen = ref(false)



// 切换移动端菜单
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

// 导航条样式
const navbarStyle = reactive({
  backgroundColor: 'rgba(255, 255, 255, 0.85)',
  backdropFilter: 'blur(10px)',
  WebkitBackdropFilter: 'blur(10px)',
  boxShadow: '0 4px 20px rgba(0, 0, 0, 0.1)',
  transition: 'all 0.3s ease',
  transform: 'translateY(0)',
  opacity: '1',
  height: '70px',
  padding: '0.5rem 1rem'
})

// 记录上次滚动位置
let lastScrollTop = 0

// 滚动监听处理函数
const handleScroll = () => {
  const scrollTop = window.scrollY
  
  // 计算滚动距离
  const scrollDiff = scrollTop - lastScrollTop
  
  // 向下滚动，隐藏导航条
  if (scrollDiff > 5 && scrollTop > 50) {
    navbarStyle.transform = 'translateY(-100%)'
    navbarStyle.opacity = '0'
  } 
  // 向上滚动，显示导航条
  else if (scrollDiff < -5) {
    navbarStyle.transform = 'translateY(0)'
    navbarStyle.opacity = '1'
    
    // 根据滚动位置调整导航条样式
    if (scrollTop > 50) {
      navbarStyle.backgroundColor = 'rgba(255, 255, 255, 0.95)'
      navbarStyle.boxShadow = '0 6px 30px rgba(0, 0, 0, 0.15)'
    } else {
      navbarStyle.backgroundColor = 'rgba(255, 255, 255, 1)'
      navbarStyle.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)'
    }
  }
  
  // 更新上次滚动位置
  lastScrollTop = scrollTop
}

// 检查是否需要显示登录模态框
const checkAuthModal = () => {
  // 获取当前路由查询参数
  const query = router.currentRoute.value.query
  if (query.redirect) {
    // 显示登录模态框
    showAuthModal.value = true
  }
}

// 监听路由变化，检查是否需要显示登录模态框
watch(
  () => router.currentRoute.value,
  (newRoute) => {
    checkAuthModal()
  },
  { immediate: true }
)

// 组件挂载时添加滚动监听和检查登录模态框
onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  checkAuthModal()
  
  // 初始获取未读消息数量
  if (userStore.isLogin) {
    getUnreadMessageCount()
  }
  
  // 添加页面可见性变化监听
  window.addEventListener('visibilitychange', handleVisible)
})

// 组件卸载时移除滚动监听
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('visibilitychange', handleVisible)
})

// 处理登录/注册成功
const handleAuthSuccess = () => {
  // 刷新用户信息
  userStore.getUserInfo()
  
  // 检查是否有重定向地址
  const query = router.currentRoute.value.query
  if (query.redirect) {
    // 跳转到重定向地址
    router.push(query.redirect)
  }
}

// 获取未读消息数量
const getUnreadMessageCount = async () => {
  if (!userStore.isLogin) {
    userStore.updateUnreadMessageCount(0)
    return
  }
  
  try {
    const res = await chatApi.getConversations()
    if (res.code === 200 && Array.isArray(res.data)) {
      // 计算所有会话的未读消息总数
      const totalUnread = res.data.reduce((sum, conversation) => sum + (conversation.unread_count || 0), 0)
      userStore.updateUnreadMessageCount(totalUnread)
    }
  } catch (err) {
    console.error('获取未读消息数量失败:', err)
  }
}

// 处理退出登录
const handleLogout = () => {
  userStore.logout()
}

// 页面切回来时自动刷新消息数量
const handleVisible = () => {
  if (!document.hidden && userStore.isLogin) {
    getUnreadMessageCount()
  }
}

// 监听登录状态变化
watch(isLogin, (newVal) => {
  if (newVal) {
    getUnreadMessageCount()
  } else {
    userStore.updateUnreadMessageCount(0)
  }
})
</script>