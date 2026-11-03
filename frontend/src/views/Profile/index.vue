<template>
  <div class="container py-5">
    <div class="profile-page">
      <!-- 用户信息卡片 -->
      <div class="profile-header mb-4">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <!-- 用户头像（首字母显示） -->
              <div class="col-auto">
                <div class="avatar-wrapper position-relative">
                  <div class="avatar-circle">
                    {{ (userInfo.username || '用').charAt(0).toUpperCase() }}
                  </div>
                </div>
              </div>
              
              <!-- 用户信息 -->
              <div class="col">
                <div class="user-info">
                  <h4 class="user-name mb-2">
                    {{ userInfo.username }}
                    <span v-if="userInfo.role === 'admin'" class="badge bg-danger ms-2">管理员</span>
                  </h4>
                  <div class="user-meta">
                    <span class="meta-item me-3">
                      <i class="fa fa-calendar me-1"></i>
                      注册时间：{{ userInfo.created_at || '2026-03-02' }}
                    </span>
                    <span class="meta-item">
                      <i class="fa fa-envelope me-1"></i>
                      {{ userInfo.email || '未设置' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- 操作按钮 -->
              <div class="col-auto">
                <div class="action-buttons">
                  <!-- 发布下拉菜单 -->
                  <div class="publish-dropdown relative me-2" @click="togglePublishDropdown">
                    <button class="btn btn-primary dropdown-toggle-btn">
                      <i class="fa fa-plus me-1"></i> 发布
                      <i class="fa fa-chevron-down ml-1"></i>
                    </button>
                    <div v-if="showPublishDropdown" class="dropdown-menu-content">
                      <button class="dropdown-item" @click.stop="goToPublish('product')"><i class="fa fa-shopping-bag me-2"></i>发布商品</button>
                      <button class="dropdown-item" @click.stop="goToPublish('forum')"><i class="fa fa-comment me-2"></i>发布话题</button>
                      <button class="dropdown-item" @click.stop="goToPublish('lostfound')"><i class="fa fa-search me-2"></i>发布失物</button>
                      <button class="dropdown-item" @click.stop="goToPublish('activity')"><i class="fa fa-calendar me-2"></i>发布活动</button>
                    </div>
                  </div>
                  <button class="btn btn-outline-secondary me-2" @click="showEditDialog">
                    <i class="fa fa-pencil me-1"></i> 编辑资料
                  </button>
                  <button v-if="isAdmin" class="btn btn-success me-2" @click="goToAdmin">
                    <i class="fa fa-cog me-1"></i> 管理后台
                  </button>
                  <button class="btn btn-outline-danger" @click="handleLogout">
                    <i class="fa fa-sign-out me-1"></i> 退出
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 内容Tab栏 -->
      <div class="profile-content">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <ul class="nav nav-tabs profile-tabs" role="tablist">
              <li class="nav-item">
                <button 
                  class="nav-link" 
                  :class="{ active: activeTab === 'myProducts' }"
                  @click="activeTab = 'myProducts'"
                >
                  <i class="fa fa-shopping-bag me-2"></i>我的发布
                </button>
              </li>
              <li class="nav-item">
                <button 
                  class="nav-link"
                  :class="{ active: activeTab === 'favoriteProducts' }"
                  @click="activeTab = 'favoriteProducts'"
                >
                  <i class="fa fa-star me-2"></i>我的收藏
                </button>
              </li>
              <li class="nav-item">
                <button 
                  class="nav-link"
                  :class="{ active: activeTab === 'completed' }"
                  @click="activeTab = 'completed'"
                >
                  <i class="fa fa-check-circle me-2"></i>已完成
                </button>
              </li>
            </ul>
          </div>
          <div class="card-body p-4">
            <!-- 我的发布 -->
            <div v-show="activeTab === 'myProducts'">
              <div v-if="loading.myProducts" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">加载中...</p>
              </div>
              
              <div v-else>
                <div class="row g-4">
                  <div class="col-6 col-md-4 col-lg-3" v-for="product in myProducts" :key="product.id">
                    <ProductCard 
                      :product="product" 
                      isMine
                      @delete="getMyProducts"
                    />
                  </div>
                </div>

                <div v-if="myProducts.length === 0" class="empty-state text-center py-5">
                  <div class="empty-icon mb-3">
                    <i class="fa fa-shopping-bag"></i>
                  </div>
                  <h5 class="mb-2">你还没有发布任何商品</h5>
                  <p class="text-muted mb-3">开始发布你的第一个商品吧！</p>
                  <button class="btn btn-primary" @click="goToPublish">
                    <i class="fa fa-plus me-1"></i> 发布商品
                  </button>
                </div>
              </div>
            </div>

            <!-- 我的收藏 -->
            <div v-show="activeTab === 'favoriteProducts'">
              <div v-if="loading.favoriteProducts" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">加载中...</p>
              </div>
              
              <div v-else>
                <div class="forum-favorites-list">
                  <div 
                    v-for="topic in favoriteTopics" 
                    :key="topic.id" 
                    class="forum-favorite-item card mb-3"
                    @click="goToTopicDetail(topic.id)"
                  >
                    <div class="card-body">
                      <h5 class="card-title font-medium text-primary-hover">{{ topic.title }}</h5>
                      <p class="card-text text-muted text-sm mt-2 line-clamp-2">{{ topic.content }}</p>
                      <div class="mt-3 flex items-center text-xs text-gray-500">
                        <span class="mr-3">{{ formatDate(topic.created_at) }}</span>
                        <span class="mr-3"><i class="fa fa-thumbs-up mr-1"></i>{{ topic.likes_count || 0 }}</span>
                        <span><i class="fa fa-comment mr-1"></i>{{ topic.replies_count || 0 }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="favoriteTopics.length === 0" class="empty-state text-center py-5">
                  <div class="empty-icon mb-3">
                    <i class="fa fa-star-o"></i>
                  </div>
                  <h5 class="mb-2">你还没有收藏任何话题</h5>
                  <p class="text-muted mb-3">去浏览论坛话题并收藏你感兴趣的文章吧！</p>
                  <router-link to="/forum" class="btn btn-primary">
                    <i class="fa fa-comment me-1"></i> 浏览话题
                  </router-link>
                </div>
              </div>
            </div>

            <!-- 已完成 -->
            <div v-show="activeTab === 'completed'">
              <div v-if="loading.completedProducts" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">加载中...</p>
              </div>
              
              <div v-else>
                <div class="row g-4">
                  <div class="col-6 col-md-4 col-lg-3" v-for="product in completedProducts" :key="product.id">
                    <ProductCard :product="product" />
                  </div>
                </div>

                <div v-if="completedProducts.length === 0" class="empty-state text-center py-5">
                  <div class="empty-icon mb-3">
                    <i class="fa fa-check-circle"></i>
                  </div>
                  <h5 class="mb-2">你还没有已完成的商品</h5>
                  <p class="text-muted mb-3">完成的商品会显示在这里</p>
                  <router-link to="/secondhand" class="btn btn-primary">
                    <i class="fa fa-shopping-cart me-1"></i> 浏览商品
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- 编辑资料模态框 -->
      <div v-if="showEditModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h5 class="modal-title">编辑资料</h5>
              <button type="button" class="btn-close" @click="closeEditModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="submitEdit" ref="editFormRef">
                <div class="mb-3">
                  <label class="form-label">用户名</label>
                  <input type="text" class="form-control" v-model="editForm.username" required minlength="3" maxlength="20">
                </div>
                <div class="mb-3">
                  <label class="form-label">邮箱</label>
                  <input type="email" class="form-control" v-model="editForm.email" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">原密码（修改密码时必填）</label>
                  <input type="password" class="form-control" v-model="editForm.oldPassword">
                </div>
                <div class="mb-3">
                  <label class="form-label">新密码（修改密码时必填）</label>
                  <input type="password" class="form-control" v-model="editForm.newPassword" minlength="6">
                </div>
                <div class="mb-3">
                  <label class="form-label">确认新密码</label>
                  <input type="password" class="form-control" v-model="editForm.confirmPassword" minlength="6">
                  <div class="text-danger small mt-1" v-if="editForm.newPassword && editForm.newPassword !== editForm.confirmPassword">
                    两次输入的密码不一致
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" @click="closeEditModal">取消</button>
                  <button type="submit" class="btn btn-primary" :disabled="submitting">
                    <span v-if="submitting"><i class="fa fa-spinner fa-spin me-1"></i>提交中...</span>
                    <span v-else>保存</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/modules/user'
import ProductCard from '@/components/ProductCard.vue'
import { productApi } from '@/api/modules/product'
import { forumApi } from '@/api/modules/forum'
import request from '@/utils/request'

const router = useRouter()
const userStore = useUserStore()

const isAdmin = computed(() => userStore.isAdmin)

const userInfo = ref({
  username: localStorage.getItem('username') || 'admin',
  email: '',
  avatar: '',
  created_at: '',
  role: ''
})

const loading = ref({
  userInfo: false,
  myProducts: false,
  favoriteProducts: false,
  completedProducts: false
})

const editFormRef = ref(null)
const submitting = ref(false)
const showEditModal = ref(false)
const editForm = ref({
  username: '',
  email: '',
  oldPassword: '',
  newPassword: '',
  confirmPassword: ''
})
const activeTab = ref('myProducts')
const showPublishDropdown = ref(false)
const myProducts = ref([])
const favoriteProducts = ref([])
const completedProducts = ref([])
const favoriteTopics = ref([])

const getMyProducts = async () => {
  try {
    loading.value.myProducts = true
    const res = await productApi.getMyProducts()
    if (res.code === 200) {
      myProducts.value = res.data.list || []
    }
  } catch (err) {
    myProducts.value = []
  } finally {
    loading.value.myProducts = false
  }
}

const getFavoriteProducts = async () => {
  try {
    loading.value.favoriteProducts = true
    const res = await forumApi.getUserFavorites()
    if (res.code === 200) {
      favoriteTopics.value = res.data || []
    }
  } catch (err) {
    favoriteTopics.value = []
  } finally {
    loading.value.favoriteProducts = false
  }
}

const goToTopicDetail = (topicId) => {
  router.push(`/forum/topic/${topicId}`)
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN')
}

const getCompletedProducts = async () => {
  try {
    loading.value.completedProducts = true
    completedProducts.value = []
  } catch (err) {
    completedProducts.value = []
  } finally {
    loading.value.completedProducts = false
  }
}

const getUserInfo = async () => {
  try {
    loading.value.userInfo = true
    const res = await request({
      url: '/api/auth/me',
      method: 'get'
    })
    if (res.code === 200) {
      userInfo.value = res.data
      localStorage.setItem('username', res.data.username)
    }
  } catch (err) {
    userInfo.value.username = localStorage.getItem('username') || 'admin'
  } finally {
    loading.value.userInfo = false
  }
}

const showEditDialog = () => {
  editForm.value = {
    username: userInfo.value.username,
    email: userInfo.value.email,
    oldPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  showEditModal.value = true
}

const submitEdit = async () => {
  if (!editFormRef.value) return
  
  if (!editForm.value.username || editForm.value.username.length < 3 || editForm.value.username.length > 20) {
    alert('用户名长度在3-20个字符之间')
    return
  }
  if (!editForm.value.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(editForm.value.email)) {
    alert('请输入正确的邮箱格式')
    return
  }
  if (editForm.value.newPassword && !editForm.value.oldPassword) {
    alert('修改密码需要填写原密码')
    return
  }
  if (editForm.value.oldPassword && !editForm.value.newPassword) {
    alert('请输入新密码')
    return
  }
  if (editForm.value.newPassword && editForm.value.newPassword.length < 6) {
    alert('新密码长度不能少于6个字符')
    return
  }
  if (editForm.value.newPassword !== editForm.value.confirmPassword) {
    alert('两次输入的密码不一致')
    return
  }
  
  try {
    submitting.value = true
    
    const data = {
      username: editForm.value.username,
      email: editForm.value.email
    }
    
    if (editForm.value.oldPassword && editForm.value.newPassword) {
      data.old_password = editForm.value.oldPassword
      data.new_password = editForm.value.newPassword
    }
    
    const res = await request({
      url: '/api/auth/update',
      method: 'post',
      data: data
    })
    
    if (res.code === 200) {
      alert('资料更新成功')
      closeEditModal()
      await getUserInfo()
    } else {
      alert(res.msg || '更新失败')
    }
  } catch (err) {
    alert('更新失败，请稍后重试')
  } finally {
    submitting.value = false
  }
}

const closeEditModal = () => {
  showEditModal.value = false
}

const goToPublish = (type = 'product') => {
  showPublishDropdown.value = false
  const routes = {
    product: '/publish',
    forum: '/forum?publish=1',
    lostfound: '/lostfound/publish',
    activity: '/campus-activities/publish'
  }
  router.push(routes[type] || '/publish')
}

const togglePublishDropdown = () => {
  showPublishDropdown.value = !showPublishDropdown.value
}

const closePublishDropdown = () => {
  showPublishDropdown.value = false
}

const goToAdmin = () => {
  router.push('/admin')
}

const handleLogout = () => {
  if (confirm('确定退出登录吗？')) {
    userStore.logout()
    alert('退出成功')
    router.push('/')
  }
}

onMounted(() => {
  getUserInfo()
  getMyProducts()
  getFavoriteProducts()
  getCompletedProducts()
  
  // 点击外部关闭下拉菜单
  document.addEventListener('click', handleOutsideClick)
})

const handleOutsideClick = (event) => {
  const dropdown = document.querySelector('.publish-dropdown')
  if (dropdown && !dropdown.contains(event.target)) {
    showPublishDropdown.value = false
  }
}
</script>

<style scoped>
.profile-page {
  max-width: 1400px;
  margin: 0 auto;
}

/* 用户头像样式（首字母显示） */
.avatar-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 3px solid #e9ecef;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: white;
  font-size: 36px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 用户信息样式 */
.user-info {
  flex: 1;
}

.user-name {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.user-meta {
  color: #6c757d;
  font-size: 0.9rem;
}

.meta-item {
  display: inline-flex;
  align-items: center;
}

.meta-item i {
  color: #adb5bd;
}

/* 操作按钮样式 */
.action-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* 发布下拉菜单样式 */
.publish-dropdown {
  position: relative;
}

.dropdown-toggle-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.dropdown-menu-content {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  min-width: 160px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  border: 1px solid #e9ecef;
  padding: 4px 0;
  z-index: 1000;
  overflow: hidden;
}

.dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 10px 16px;
  background: transparent;
  border: none;
  text-align: left;
  font-size: 14px;
  color: #333;
  cursor: pointer;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background: #f5f5f5;
  color: #FF6B9D;
}

.dropdown-item i {
  width: 16px;
  text-align: center;
}

/* Tab栏样式 */
.profile-tabs {
  border-bottom: 2px solid #e9ecef;
  gap: 1rem;
}

.profile-tabs .nav-item {
  margin-bottom: -2px;
}

.profile-tabs .nav-link {
  color: #6c757d;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 1rem 1.5rem;
  font-weight: 500;
  transition: all 0.3s ease;
  background: transparent;
}

.profile-tabs .nav-link:hover {
  color: #FF8A5C;
  border-bottom-color: #FF8A5C;
}

.profile-tabs .nav-link.active {
  color: #FF8A5C;
  border-bottom-color: #FF8A5C;
  background: transparent;
}

/* 空状态样式 */
.empty-state {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 3rem 1rem;
}

.empty-icon {
  font-size: 4rem;
  color: #dee2e6;
}

.empty-state h5 {
  color: #495057;
  font-weight: 600;
}

.empty-state p {
  color: #6c757d;
}

/* 响应式样式 */
@media (max-width: 992px) {
  .profile-header .card-body {
    padding: 1.5rem;
  }
  
  .avatar-circle {
    width: 80px;
    height: 80px;
  }
  
  .user-name {
    font-size: 1.25rem;
  }
  
  .action-buttons {
    width: 100%;
    margin-top: 1rem;
    justify-content: flex-start;
  }
}

@media (max-width: 768px) {
  .profile-page {
    padding: 0 0.5rem;
  }
  
  .profile-header .card-body {
    padding: 1rem;
  }
  
  .avatar-circle {
    width: 60px;
    height: 60px;
  }
  
  .user-info {
    text-align: center;
  }
  
  .user-meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .action-buttons {
    justify-content: center;
  }
  
  .action-buttons .btn {
    flex: 1;
    min-width: 120px;
  }
  
  .profile-tabs {
    gap: 0.5rem;
  }
  
  .profile-tabs .nav-link {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
  }
  
  .profile-tabs .nav-link i {
    margin-right: 0.25rem !important;
  }
}

@media (max-width: 576px) {
  .col-6.col-md-4.col-lg-3 {
    flex: 0 0 50%;
    max-width: 50%;
  }
  
  .action-buttons .btn {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
  }
}

/* 确保ProductCard组件在个人中心正确显示 */
.profile-content :deep(.product-card),
.profile-content :deep(.lostfound-card) {
  width: 100%;
  max-width: 100%;
  height: 100%;
}

/* 论坛收藏列表样式 */
.forum-favorites-list {
  padding: 0;
}

.forum-favorite-item {
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}

.forum-favorite-item:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
  border-color: #FF8A5C;
}

.forum-favorite-item .card-title {
  color: #333;
  font-size: 1.1rem;
  font-weight: 600;
}

.forum-favorite-item .card-title:hover {
  color: #FF8A5C;
}

.forum-favorite-item .card-text {
  color: #6c757d;
  font-size: 0.9rem;
  line-height: 1.6;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.profile-content :deep(.aspect-square) {
  aspect-ratio: 1 / 1;
  overflow: hidden;
}

.profile-content :deep(.aspect-square img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* 编辑资料模态框样式 */
.modal.fade.show {
  display: flex !important;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
}

.modal-content {
  width: 100%;
  max-width: 480px;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
  border: none;
  overflow: hidden;
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #FF6B9D 0%, #FF8A5C 50%, #FFD93D 100%);
  color: white;
  padding: 24px;
  border-bottom: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
}

.modal-header .modal-title {
  font-size: 20px;
  font-weight: 700;
  margin: 0;
  letter-spacing: 1px;
}

.modal-header .btn-close {
  color: white !important;
  opacity: 0.8;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: all 0.3s ease;
}

.modal-header .btn-close:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.25);
  transform: rotate(90deg);
}

.modal-header .btn-close::before {
  content: '\00D7';
  font-size: 20px;
  line-height: 1;
  font-weight: 300;
}

.modal-body {
  padding: 28px;
  background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
}

.modal-body .form-label {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 10px;
  font-size: 14px;
  letter-spacing: 0.5px;
}

.modal-body .form-group {
  margin-bottom: 24px;
}

.modal-body .form-control {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
  font-size: 15px;
  transition: all 0.3s ease;
  background: #ffffff;
}

.modal-body .form-control:focus {
  outline: none;
  border-color: #FF6B9D;
  box-shadow: 0 0 0 4px rgba(255, 107, 157, 0.12);
  transform: translateY(-1px);
}

.modal-body .form-control::placeholder {
  color: #a0aec0;
}

.modal-footer {
  padding: 20px 28px;
  background: #f7fafc;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 14px;
}

.modal-footer .btn-secondary {
  background-color: #e2e8f0;
  border: none;
  color: #4a5568;
  padding: 12px 28px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.modal-footer .btn-secondary:hover {
  background-color: #cbd5e0;
  transform: translateY(-1px);
}

.modal-footer .btn-primary {
  background: linear-gradient(135deg, #FF6B9D 0%, #FF8A5C 100%);
  border: none;
  color: white;
  padding: 12px 32px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(255, 107, 157, 0.35);
}

.modal-footer .btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 107, 157, 0.45);
}

.modal-footer .btn-primary:active {
  transform: translateY(0);
}

.modal-footer .btn-primary:disabled {
  opacity: 0.6;
  transform: none;
  box-shadow: none;
  cursor: not-allowed;
}

.modal-footer .btn-primary:disabled:hover {
  transform: none;
}
</style>
