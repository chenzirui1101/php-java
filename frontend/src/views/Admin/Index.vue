<template>
  <div class="admin-container">
    <!-- 顶部导航栏 -->
    <header class="admin-header bg-dark text-white">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col header-left">
            <h1 class="h4 mb-0">管理员后台</h1>
          </div>
          <div class="col-auto header-right">
            <span class="mr-4">欢迎，{{ userInfo.name || userInfo.username }}</span>
            <button class="btn btn-link text-white" @click="handleLogout">退出登录</button>
          </div>
        </div>
      </div>
    </header>
    
    <!-- 主体内容区 -->
    <div class="row no-gutters" style="height: calc(100vh - 60px);">
      <!-- 左侧侧边栏 -->
      <aside class="admin-sidebar col-2 bg-white border-right">
        <nav class="sidebar-menu h-100">
          <ul class="nav flex-column">
            <li class="nav-item">
              <router-link 
                to="/admin/products" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/products' }"
              >
                <i class="fa fa-cubes mr-2"></i> 商品管理
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/admin/lostfound" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/lostfound' }"
              >
                <i class="fa fa-search mr-2"></i> 失物管理
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/admin/forum" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/forum' }"
              >
                <i class="fa fa-comments mr-2"></i> 话题管理
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/admin/activities" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/activities' }"
              >
                <i class="fa fa-calendar mr-2"></i> 活动管理
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/admin/users" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/users' }"
              >
                <i class="fa fa-users mr-2"></i> 用户管理
              </router-link>
            </li>
            <li class="nav-item">
              <router-link 
                to="/admin/moderation" 
                class="nav-link"
                :class="{ 'active bg-primary text-white': activeMenu === '/admin/moderation' }"
              >
                <i class="fa fa-shield mr-2"></i> 内容审核
              </router-link>
            </li>
          </ul>
        </nav>
      </aside>
      
      <!-- 右侧主内容 -->
      <main class="admin-main col-10">
        <div class="container-fluid p-4">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import { useUserStore } from '@/stores/modules/user'

const router = useRouter()
const userStore = useUserStore()
const activeMenu = ref('/admin/products')
const userInfo = ref({})

onMounted(() => {
  userInfo.value = userStore.userInfo || {}
  // 监听路由变化
  router.afterEach((to) => {
    activeMenu.value = to.path
  })
})

const handleLogout = () => {
  userStore.logout()
  alert('退出成功')
  router.push('/')
}
</script>

<style scoped>
.admin-container {
  height: 100vh;
  background-color: #f5f7fa;
  display: flex;
  flex-direction: column;
}

.admin-header {
  height: 60px;
  display: flex;
  align-items: center;
}

.admin-sidebar {
  height: 100%;
  overflow-y: auto;
}

.sidebar-menu {
  padding: 20px 0;
}

.sidebar-menu .nav-link {
  color: #333;
  padding: 10px 20px;
  border-radius: 0;
  transition: all 0.3s ease;
}

.sidebar-menu .nav-link:hover {
  background-color: #e9ecef;
}

.admin-main {
  height: 100%;
  overflow-y: auto;
  background-color: #f5f7fa;
}
</style>