<template>
  <div class="min-h-screen bg-white relative overflow-hidden d-flex flex-column">
    <!-- 主内容容器，添加左内边距避开固定导航 -->
    <div class="main-content-container">
    <!-- 巨幕部分 -->
    <div class="jumbotron-section" id="jumbotron-section">
      <div class="h-100 w-100 d-flex align-items-center justify-content-center p-4">
        <div class="card overflow-hidden h-100 w-100 max-w-6xl shadow-lg">
          <div class="row no-gutters h-100">
            <div class="col-md-5">
              <div class="card-body d-flex flex-column justify-center">
                <h1 class="display-3 font-bold mb-6">
                  书香伴纸鸢 · 青春正当时
                </h1>
                <p class="lead mb-8 text-text-secondary">
                  阳光洒落书页，纸飞机载着梦想飞向远方。<br>
                  在淡粉、淡蓝与淡黄的色彩里，遇见最清新的校园时光。
                </p>
              </div>
            </div>
            <div class="col-md-7">
              <div class="relative h-100">
                <img 
                  src="@/assets/images/jumbotron.jpg" 
                  alt="巨幕图片" 
                  class="img-fluid"
                />
                <!-- 使用用户提供的透明背景纸飞机图片 -->
                <img
                  id="paperPlane"
                  src="@/assets/images/jumu.png"
                  alt="纸飞机"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    
    <!-- 热点话题框 -->
    <div id="hot-topics-section" class="py-8 bg-background-light">
      <div class="container">
        <h2 class="text-center display-4 font-bold mb-5 text-text-primary">热点话题</h2>
        <div class="row">
          <!-- 校园热点 -->
          <div class="col-lg-6 mb-4">
            <div class="card shadow-sm hover:shadow-lg transition-all duration-300">
              <div class="card-header bg-primary text-white">
                <h3 class="card-title font-weight-bold">校园热点</h3>
              </div>
              <div class="card-body">
                <ul class="list-unstyled">
                  <li v-for="(topic, index) in hotTopics.slice(0, 3)" :key="`hot-${index}`" class="mb-4">
                    <div class="d-flex align-items-start">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); font-size: 19px; margin-right: 12px;">
                        <span class="mx-auto">
                          {{ index + 1 }}
                        </span>
                      </div>
                      <div class="flex-grow-1 min-w-0">
                        <h4 
                          class="font-weight-medium text-text-primary hover:text-primary cursor-pointer transition-colors" 
                          :title="topic.title"
                          style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;"
                        >
                          {{ truncateTitle(topic.title) }}
                        </h4>
                        <p class="text-sm text-text-secondary">
                          {{ topic.created_at ? new Date(topic.created_at).toLocaleDateString() : '' }} · {{ topic.replies_count || 0 }}回复
                        </p>
                      </div>
                    </div>
                  </li>
                  <li v-if="hotTopics.length === 0" class="mb-4">
                    <div class="d-flex align-items-start">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); font-size: 19px; margin-right: 12px;">
                        <span class="mx-auto">
                          1
                        </span>
                      </div>
                      <div class="flex-grow-1">
                        <h4 class="font-weight-medium text-text-primary hover:text-primary cursor-pointer transition-colors"></h4>
                        <p class="text-sm text-text-secondary"></p>
                      </div>
                    </div>
                  </li>
                </ul>
                <div class="text-center">
                  <router-link 
                    to="/campus-hot" 
                    class="text-primary font-weight-medium"
                  >
                    查看更多 <i class="fa fa-arrow-right ml-1"></i>
                  </router-link>
                </div>
              </div>
            </div>
          </div>
          
          <!-- 校园活动 -->
          <div class="col-lg-6 mb-4">
            <div class="card shadow-sm hover:shadow-lg transition-all duration-300">
              <div class="card-header bg-primary text-white">
                <h3 class="card-title font-weight-bold">校园活动</h3>
              </div>
              <div class="card-body">
                <ul class="list-unstyled">
                  <li v-for="(activity, index) in campusActivities.slice(0, 3)" :key="`activity-${index}`" class="mb-4">
                    <div class="d-flex align-items-start">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); font-size: 19px; margin-right: 12px;">
                        <span class="mx-auto">
                          {{ index + 1 }}
                        </span>
                      </div>
                      <div class="flex-grow-1 min-w-0">
                        <h4 
                          class="font-weight-medium text-text-primary hover:text-primary cursor-pointer transition-colors" 
                          :title="activity.title"
                          style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;"
                        >
                          {{ truncateTitle(activity.title) }}
                        </h4>
                        <p class="text-sm text-text-secondary">
                          {{ activity.start_time ? formatDateMonthDay(activity.start_time) : '' }}至{{ activity.end_time ? formatDateMonthDay(activity.end_time) : '' }} · {{ activity.participant_count || 0 }}人参与
                        </p>
                      </div>
                    </div>
                  </li>
                  <li v-if="campusActivities.length === 0" class="mb-4">
                    <div class="d-flex align-items-start">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white font-weight-bold" style="width: 40px; height: 40px; background: linear-gradient(135deg, #FF6B9D, #FFD93D); font-size: 19px; margin-right: 12px;">
                        <span class="mx-auto">
                          1
                        </span>
                      </div>
                      <div class="flex-grow-1">
                        <h4 class="font-weight-medium text-text-primary hover:text-primary cursor-pointer transition-colors"></h4>
                        <p class="text-sm text-text-secondary"></p>
                      </div>
                    </div>
                  </li>
                </ul>
                <div class="text-center">
                  <router-link 
                    to="/campus-activities" 
                    class="text-primary font-weight-medium"
                  >
                    查看更多 <i class="fa fa-arrow-right ml-1"></i>
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 失物招领 -->
    <div id="lostfound-section" class="py-8 bg-background-light">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="display-4 font-bold text-text-primary">失物招领</h2>
          <router-link 
            to="/lostfound" 
            class="text-primary font-weight-medium"
          >
            查看全部 <i class="fa fa-arrow-right ml-1"></i>
          </router-link>
        </div>
        
        <!-- 失物招领网格 -->
        <div class="row">
          <div v-for="item in lostFoundList.slice(0, 8)" :key="item.id" class="col-sm-6 col-md-4 col-lg-3 mb-4 lostfound-card-item d-flex">
            <div class="w-100">
              <LostFoundCard 
                :item="item"
              />
            </div>
          </div>
        </div>
        
        <!-- 空状态 -->
        <div v-if="lostFoundList.length === 0" class="card shadow-sm p-5 text-center">
          <div class="text-text-secondary">暂无失物招领信息</div>
        </div>
      </div>
    </div>
    
    <!-- 商品列表（热门商品） -->
    <div id="popular-products-section" class="py-8 bg-white">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="display-4 font-bold text-text-primary">热门商品</h2>
          <router-link 
            to="/secondhand" 
            class="text-primary font-weight-medium"
          >
            查看全部 <i class="fa fa-arrow-right ml-1"></i>
          </router-link>
        </div>
        
        <!-- 商品网格 -->
        <div class="row">
          <div v-for="product in productList.slice(0, 8)" :key="product.id" class="col-sm-6 col-md-4 col-lg-3 mb-4 product-card-item d-flex">
            <div class="w-100">
              <ProductCard 
                :product="product" 
                :is-favorite="favoriteStatusMap[product.id] || false"
                @favorite-change="handleFavoriteChange"
              />
            </div>
          </div>
        </div>
        
        <!-- 空状态 -->
        <div v-if="productList.length === 0 && !loading" class="card shadow-sm p-5 text-center">
          <div class="text-text-secondary">暂无商品</div>
        </div>
        
        <!-- 加载状态 -->
        <div v-if="loading" class="card shadow-sm p-5 text-center">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    </div>
    
    <!-- 更多 -->
    <div id="campus-activities-section" class="py-12 bg-background-light">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-8">
          <h2 class="display-4 font-bold text-text-primary">更多</h2>
          <button 
            class="text-primary font-weight-medium bg-transparent border-0 p-0 outline-none"
            style="cursor: pointer;"
            @click="showDownloadAppModal = true"
          >
            下载App <i class="fa fa-arrow-right ml-1"></i>
          </button>
        </div>
        
        <!-- 活动网格容器 -->
        <div class="activity-grid-container">
          <!-- 活动卡片网格 -->
          <div class="activity-card-grid">
            <!-- 活动卡片 -->
            <div 
              v-for="activity in activityList" 
              :key="activity.id" 
              class="activity-card bg-white rounded-lg overflow-hidden hover:shadow-xl transition-all duration-500 transform hover:-translate-y-3 border border-gray-100"
              @click="goToActivity(activity.url)"
            >
              <!-- 活动图标 -->
              <div class="relative w-full aspect-square overflow-hidden bg-gradient-to-br from-primary/10 to-secondary/10 flex items-end justify-center pt-4">
                <!-- 直接使用活动指定的图标 -->
                <div class="activity-icon text-5xl text-orange-gradient d-flex items-center justify-center w-full h-full">
                  <i 
                    class="fa" 
                    :class="activity.icon"
                  ></i>
                </div>
              </div>
              <!-- 活动信息 -->
              <div class="p-4 flex flex-col h-24 pt-6">
                <h3 class="font-medium text-gray-800 text-lg mb-2 hover:text-orange-500 transition-colors duration-300 text-center flex items-center justify-center h-full">
                  {{ activity.title }}
                </h3>
              </div>
            </div>
          </div>
        </div>
        
        <!-- 空状态 -->
        <div v-if="activityList.length === 0" class="card shadow-sm p-8 text-center bg-white rounded-lg mt-8">
          <div class="text-text-secondary text-lg">暂无活动</div>
        </div>
      </div>
    </div>
    
    <!-- 页脚 -->
    <SiteFooter />
    </div>

    <!-- 返回顶部按钮 -->
    <button 
      class="btn btn-orange btn-floating" 
      style="position: fixed; right: 30px; bottom: 30px; z-index: 999; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(255, 138, 92, 0.3); transition: all 0.3s ease;" 
      @click="scrollToTop"
      :style="{ display: showBackToTop ? 'flex' : 'none' }"
    >
      <i class="fa fa-arrow-up"></i>
    </button>
    
    <!-- 用户协议模态框（用于显示隐私政策和使用条款） -->
    <AuthModal 
      v-if="showTermsModal" 
      :show-terms-directly="true"
      @close="showTermsModal = false" 
    />
    
    <!-- 下载App模态框 -->
    <DownloadAppModal 
      v-if="showDownloadAppModal" 
      @close="showDownloadAppModal = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import ProductCard from '@/components/ProductCard.vue'
import LostFoundCard from '@/components/LostFoundCard.vue'
import AuthModal from '@/components/auth/AuthModal.vue'
import DownloadAppModal from '@/components/DownloadAppModal.vue'
import SiteFooter from '@/components/SiteFooter.vue'
import { productApi } from '@/api/modules/product'
import { favoriteApi } from '@/api/modules/favorite'
import { lostFoundApi } from '@/api/modules/lostfound'
import { forumApi } from '@/api/modules/forum'
import { activityApi } from '@/api/modules/activity'
import { useUserStore } from '@/stores/modules/user'

// 初始化路由
const router = useRouter()

// 用户状态
const userStore = useUserStore()

// 轮播图数据已移除，改为固定大幕形式

// 分类列表
const categoryList = ref([
  { id: 0, name: '全部' },
  { id: 1, name: '数码产品' },
  { id: 2, name: '生活用品' },
  { id: 3, name: '学习资料' },
  { id: 4, name: '服饰鞋包' },
  { id: 5, name: '其他' }
])

// 商品列表相关
const productList = ref([])
const loading = ref(false)
const pageNum = ref(1)
const pageSize = ref(12)
const total = ref(0)
const currentCategory = ref(0)
const searchKeyword = ref('')
// 收藏状态映射表，key: productId, value: boolean
const favoriteStatusMap = ref({})

// 失物招领列表相关
const lostFoundList = ref([])

// 更多数据 - 更新为新的内容
const activityList = ref([
  {
    id: 1,
    title: '到梦活动',
    category: '活动',
    icon: 'fa-calendar-check-o',
    url: '/more/to-dream'
  },
  {
    id: 2,
    title: '学习通',
    category: '学习',
    icon: 'fa-book',
    url: '/more/xuexitong'
  },
  {
    id: 3,
    title: '志愿者',
    category: '公益',
    icon: 'fa-heart',
    url: '/more/volunteer'
  },
  {
    id: 4,
    title: '关于我们',
    category: '信息',
    icon: 'fa-info-circle',
    url: '/more/about'
  },
  {
    id: 5,
    title: '联系我们',
    category: '信息',
    icon: 'fa-envelope',
    url: '/more/contact'
  }
])

// 热点话题数据
const hotTopics = ref([])
const campusActivities = ref([])

// 滚动相关
let scrollTimeout = null



// 返回顶部按钮显示状态
const showBackToTop = ref(false)

// 显示用户协议模态框状态
const showTermsModal = ref(false)
// 显示下载App模态框状态
const showDownloadAppModal = ref(false)

// 显示隐私政策（复用用户协议模态框）
const showPrivacyPolicy = () => {
  showTermsModal.value = true
}

// 显示使用条款（复用用户协议模态框）
const showTermsOfUse = () => {
  showTermsModal.value = true
}

// 移除 tsParticles，改为使用原生 Canvas 实现粒子效果

// 绘制抽象枫叶形状的函数
function drawMapleLeaf(ctx, x, y, size, rotation) {
  ctx.save();
  ctx.translate(x, y);
  ctx.rotate(rotation);
  
  // 绘制枫叶路径（抽象5瓣枫叶）
  ctx.beginPath();
  
  // 主叶脉
  ctx.moveTo(0, 0);
  ctx.lineTo(0, -size * 0.8);
  
  // 左1瓣
  ctx.moveTo(0, 0);
  ctx.quadraticCurveTo(-size * 0.3, -size * 0.2, -size * 0.5, -size * 0.4);
  ctx.quadraticCurveTo(-size * 0.6, -size * 0.2, -size * 0.3, 0);
  
  // 左2瓣
  ctx.moveTo(0, 0);
  ctx.quadraticCurveTo(-size * 0.2, size * 0.2, -size * 0.3, size * 0.5);
  ctx.quadraticCurveTo(-size * 0.1, size * 0.4, 0, 0);
  
  // 右1瓣
  ctx.moveTo(0, 0);
  ctx.quadraticCurveTo(size * 0.3, -size * 0.2, size * 0.5, -size * 0.4);
  ctx.quadraticCurveTo(size * 0.6, -size * 0.2, size * 0.3, 0);
  
  // 右2瓣
  ctx.moveTo(0, 0);
  ctx.quadraticCurveTo(size * 0.2, size * 0.2, size * 0.3, size * 0.5);
  ctx.quadraticCurveTo(size * 0.1, size * 0.4, 0, 0);
  
  ctx.closePath();
  ctx.restore();
}

// 粒子系统类 - 枫叶版
class Particle {
  constructor(canvas) {
    this.canvas = canvas;
    this.reset();
  }
  
  reset() {
    // 初始化粒子位置（从画布顶部进入）
    this.x = Math.random() * this.canvas.width;
    this.y = -Math.random() * 100; // 从顶部外部进入
    // 粒子大小：3-8px（枫叶需要一定大小才明显）
    this.size = 3 + Math.random() * 5;
    // 流动速度：0.8-1.5px/帧（适中，更自然）
    this.speedX = (Math.random() - 0.5) * 1; // 左右飘动
    this.speedY = 0.8 + Math.random() * 0.7; // 向下飘落
    // 旋转速度
    this.rotationSpeed = (Math.random() - 0.5) * 0.02;
    this.rotation = Math.random() * Math.PI * 2; // 初始旋转角度
    // 透明度：0.3-0.6（适中，可见但不刺眼）
    this.opacity = 0.3 + Math.random() * 0.3;
    // 颜色：枫叶红橙黄渐变（秋季主题）
    const hue = 0 + Math.random() * 40; // 0-40度，红橙黄色系
    this.color = `hsla(${hue}, 70%, 50%, ${this.opacity})`;
    // 光晕大小
    this.glow = this.size * 2;
    // 拖尾长度
    this.trailLength = 6;
    // 拖尾数组
    this.trail = [];
  }
  
  update() {
    // 更新拖尾
    this.trail.push({ x: this.x, y: this.y, rotation: this.rotation });
    if (this.trail.length > this.trailLength) {
      this.trail.shift();
    }
    
    // 移动粒子
    this.x += this.speedX;
    this.y += this.speedY;
    this.rotation += this.rotationSpeed;
    
    // 添加轻微的随机飘动效果
    this.speedX += (Math.random() - 0.5) * 0.1;
    if (this.speedX > 1.5) this.speedX = 1.5;
    if (this.speedX < -1.5) this.speedX = -1.5;
    
    // 超出边界后重置
    if (this.x < -50 || this.x > this.canvas.width + 50 || this.y > this.canvas.height + 50) {
      this.reset();
    }
  }
  
  draw(ctx) {
    // 绘制拖尾
    for (let i = 0; i < this.trail.length; i++) {
      const trailPoint = this.trail[i];
      const progress = i / this.trail.length;
      const trailOpacity = this.opacity * progress * 0.6;
      const trailSize = this.size * progress;
      
      // 拖尾枫叶
      ctx.fillStyle = this.color.replace(this.opacity, trailOpacity);
      drawMapleLeaf(ctx, trailPoint.x, trailPoint.y, trailSize, trailPoint.rotation);
      ctx.fill();
    }
    
    // 绘制粒子核心
    // 枫叶光晕
    const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.glow);
    gradient.addColorStop(0, this.color.replace(this.opacity, this.opacity * 0.8));
    gradient.addColorStop(0.5, this.color.replace(this.opacity, this.opacity * 0.3));
    gradient.addColorStop(1, this.color.replace(this.opacity, 0));
    
    ctx.fillStyle = gradient;
    drawMapleLeaf(ctx, this.x, this.y, this.size * 1.5, this.rotation);
    ctx.fill();
    
    // 枫叶核心
    ctx.fillStyle = this.color;
    drawMapleLeaf(ctx, this.x, this.y, this.size, this.rotation);
    ctx.fill();
  }
}

// 粒子系统管理类 - 优化版
class ParticleSystem {
  constructor() {
    this.canvas = null;
    this.ctx = null;
    this.particles = [];
    this.animationId = null;
    this.isRunning = false;
  }
  
  init() {
    // 获取或创建 Canvas 元素
    let canvasElement = document.getElementById('particleCanvas');
    if (!canvasElement) {
      canvasElement = document.createElement('canvas');
      canvasElement.id = 'particleCanvas';
      canvasElement.style.position = 'fixed';
      canvasElement.style.top = '0';
      canvasElement.style.left = '0';
      canvasElement.style.width = '100%';
      canvasElement.style.height = '100%';
      canvasElement.style.zIndex = '1'; // 保持在内容下方
      canvasElement.style.pointerEvents = 'none';
      canvasElement.style.opacity = '1';
      document.body.appendChild(canvasElement);
    }
    
    this.canvas = canvasElement;
    this.ctx = this.canvas.getContext('2d');
    if (!this.ctx) {
      return;
    }
    
    this.resizeCanvas();
    
    // 创建30-40个粒子（枫叶形状更复杂，减少数量以保持性能）
    const particleCount = 30 + Math.floor(Math.random() * 11);
    this.particles = [];
    for (let i = 0; i < particleCount; i++) {
      this.particles.push(new Particle(this.canvas));
    }
    
    // 启动动画
    this.isRunning = true;
    this.animate();
    
    // 监听窗口大小变化
    window.addEventListener('resize', this.resizeCanvas.bind(this));
  }
  
  resizeCanvas() {
    if (!this.canvas) return;
    this.canvas.width = window.innerWidth;
    this.canvas.height = window.innerHeight;
    console.log(`Canvas resized to ${this.canvas.width}x${this.canvas.height}`);
  }
  
  animate() {
    if (!this.isRunning || !this.ctx) return;
    
    // 清空画布
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    
    // 更新和绘制所有粒子
    for (const particle of this.particles) {
      particle.update();
      particle.draw(this.ctx);
    }
    
    // 请求下一帧
    this.animationId = requestAnimationFrame(this.animate.bind(this));
  }
  
  destroy() {
    console.log('Destroying particle system...');
    this.isRunning = false;
    if (this.animationId) {
      cancelAnimationFrame(this.animationId);
      this.animationId = null;
    }
    window.removeEventListener('resize', this.resizeCanvas.bind(this));
    // 移除 Canvas 元素
    if (this.canvas && this.canvas.parentNode) {
      this.canvas.parentNode.removeChild(this.canvas);
    }
    this.canvas = null;
    this.ctx = null;
    this.particles = [];
  }
}

// 初始化粒子系统
const particleSystem = new ParticleSystem();

// 纸飞机动画控制
let planeAnimationId = null;
let planeProgress = 0;
let isReversing = false;

// 纸飞机动画函数
const animatePlane = () => {
  const plane = document.getElementById('paperPlane');
  if (!plane) return;
  
  // 获取父容器宽度，用于响应式调整
  const parentWidth = plane.parentElement ? plane.parentElement.clientWidth : 800;
  
  // 更新进度，实现来回动画
  if (!isReversing) {
    planeProgress += 0.005;
    if (planeProgress >= 1) {
      isReversing = true;
    }
  } else {
    planeProgress -= 0.005;
    if (planeProgress <= 0) {
      isReversing = false;
    }
  }
  
  // 计算位置（在指定范围内来回移动，响应式调整）
  const startX = parentWidth * 0.1875; // 150px / 800px = 0.1875
  const endX = parentWidth * 0.25; // 200px / 800px = 0.25
  const startY = 50; // 开始Y坐标
  const endY = 100; // 结束Y坐标
  
  // 线性插值计算当前位置
  const x = startX + (endX - startX) * planeProgress;
  const y = startY + (endY - startY) * planeProgress;
  
  // 保持正面朝向，不旋转
  const rotation = 0; // 固定旋转角度
  const scale = 1; // 保持固定大小
  
  // 保持完全可见
  let opacity = 1;
  
  // 应用变换
  plane.style.transform = `translate(${x}px, ${y}px) rotate(${rotation}deg) scale(${scale})`;
  plane.style.opacity = opacity;
  plane.style.transition = 'all 0.3s ease'; // 添加过渡效果，使移动更平滑
  
  // 请求下一帧
  planeAnimationId = requestAnimationFrame(animatePlane);
};



// 获取商品列表
const getProductList = async () => {
  try {
    loading.value = true
    
    const params = {
      pageNum: pageNum.value,
      pageSize: 8, // 限制为8个，最多两行（4列布局）
      categoryId: currentCategory.value,
      keyword: searchKeyword.value
    }
    
    const res = await productApi.getProductList(params)
    
    if (res.code === 200) {
      productList.value = res.data.list || []
      total.value = res.data.total || 0
      
      // 如果用户已登录，批量检查收藏状态
      if (userStore.isLogin && productList.value.length > 0) {
        await checkBatchFavoriteStatus()
      }
    }
  } catch (err) {
    console.error('获取商品列表失败:', err)
  } finally {
    loading.value = false
  }
}

// 获取失物招领列表
const getLostFoundList = async () => {
  try {
    const params = {
      pageNum: 1,
      pageSize: 8, // 限制为8个，最多两行（4列布局）
      type: 'all',
      status: 'all',
      keyword: ''
    }
    
    const res = await lostFoundApi.getLostFoundList(params)
    
    if (res.code === 200) {
      lostFoundList.value = res.data.data || res.data.list || []
    }
  } catch (err) {
    console.error('获取失物招领列表失败:', err)
  }
}

// 获取热点话题列表
const getHotTopics = async () => {
  try {
    const res = await forumApi.getHotTopics()
    
    if (res.code === 200) {
      hotTopics.value = res.data || []
    }
  } catch (err) {
    console.error('获取热点话题列表失败:', err)
  }
}

// 获取校园活动列表
const getCampusActivities = async () => {
  try {
    const params = {
      page: 1,
      pageSize: 3,
      category_id: 0
    }
    
    const res = await activityApi.getActivities(params)
    
    if (res.code === 200) {
      // 检查数据格式，如果是paginate对象，使用data.data，否则使用data.list
      campusActivities.value = res.data.data || res.data.list || []
    }
  } catch (err) {
    console.error('获取校园活动列表失败:', err)
  }
}

// 批量检查收藏状态
const checkBatchFavoriteStatus = async () => {
  try {
    const productIds = productList.value.map(product => product.id)
    if (productIds.length === 0) return
    
    const res = await favoriteApi.batchCheckFavorite(productIds)
    if (res.code === 200) {
      // 更新收藏状态映射表
      favoriteStatusMap.value = res.data
    }
  } catch (err) {
    console.error('批量检查收藏状态失败:', err)
  }
}

// 搜索商品
const handleSearch = () => {
  pageNum.value = 1
  getProductList()
}

// 分类切换
const handleCategoryChange = (categoryId) => {
  currentCategory.value = categoryId
  pageNum.value = 1 // 重置页码
  getProductList()
}

// 监听分类变化
watch(currentCategory, () => {
  pageNum.value = 1
  getProductList()
})





// 窗口大小变化时的处理
const handleResize = () => {
  // 窗口大小变化时的处理逻辑
}

// 页面加载时获取数据
onMounted(() => {
  getProductList()
  getLostFoundList()
  getHotTopics()
  getCampusActivities()
  
  // 添加事件监听
  window.addEventListener('resize', handleResize)
  
  // 添加滚动事件监听，用于返回顶部按钮显示
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY
    
    // 返回顶部按钮显示逻辑：超过一定高度后显示
    showBackToTop.value = scrollTop > 500
  })
  
  // 初始化粒子系统和纸飞机动画
  console.log('Home component mounted, initializing particles...');
  // 延迟一点时间，确保 DOM 完全渲染
  setTimeout(() => {
    particleSystem.init();
    // 启动纸飞机动画
    animatePlane();
  }, 100);
})

// 页面卸载时移除事件监听
onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('scroll', () => {})
  clearTimeout(scrollTimeout)
  
  // 清理粒子系统和纸飞机动画
  particleSystem.destroy();
  // 停止纸飞机动画
  if (planeAnimationId) {
    cancelAnimationFrame(planeAnimationId);
    planeAnimationId = null;
  }
})

// 滚动到顶部
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

// 处理收藏状态变化事件
const handleFavoriteChange = ({ productId, isFavorite }) => {
  // 更新收藏状态映射表
  favoriteStatusMap.value[productId] = isFavorite
}

// 根据活动分类获取对应的图标
const getActivityIcon = (category) => {
  const iconMap = {
    '科技': 'fa-microchip',
    '体育': 'fa-futbol-o',
    '文艺': 'fa-paint-brush',
    '就业': 'fa-briefcase',
    '学术': 'fa-graduation-cap',
    '公益': 'fa-heart',
    '其他': 'fa-calendar-check-o'
  }
  return iconMap[category] || 'fa-calendar-check-o'
}

// 格式化日期为月日格式
const formatDateMonthDay = (dateTime) => {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${month}月${day}日`
}

// 跳转到活动页面
const goToActivity = (url) => {
  // 使用活动数据中的 URL 进行跳转
  router.push(url)
}

// 标题截断函数：超出8个字用...代替（移动端响应式）
const truncateTitle = (title, maxLength = 8) => {
  if (!title) return ''
  if (title.length <= maxLength) return title
  return title.slice(0, maxLength) + '...'
}
</script>

<style scoped>
/* 边框流光动画 */
@keyframes borderGlow {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* 纸飞机飞行动画 - 缓慢自然 */
@keyframes paperPlaneFly {
  0% {
    transform: translate(-150px, 50px) rotate(-30deg) scale(0.3);
    opacity: 0;
  }
  10% {
    opacity: 0.5;
    transform: translate(-120px, 60px) rotate(-25deg) scale(0.5);
  }
  20% {
    opacity: 1;
    transform: translate(-90px, 70px) rotate(-20deg) scale(0.7);
  }
  30% {
    transform: translate(-60px, 80px) rotate(-15deg) scale(0.85);
  }
  40% {
    transform: translate(-30px, 90px) rotate(-10deg) scale(0.95);
  }
  50% {
    transform: translate(0px, 100px) rotate(-5deg) scale(1.0);
  }
  60% {
    transform: translate(30px, 110px) rotate(0deg) scale(0.95);
  }
  70% {
    transform: translate(60px, 120px) rotate(5deg) scale(0.85);
  }
  80% {
    opacity: 1;
    transform: translate(90px, 130px) rotate(10deg) scale(0.7);
  }
  90% {
    opacity: 0.5;
    transform: translate(120px, 140px) rotate(15deg) scale(0.5);
  }
  100% {
    transform: translate(150px, 150px) rotate(20deg) scale(0.3);
    opacity: 0;
  }
}

/* 返回顶部按钮样式 */
.fixed-back-to-top {
  position: fixed;
  right: 40px;
  bottom: 60px;
  width: 50px;
  height: 50px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  color: #fff;
  border-radius: 50%;
  box-shadow: 0 4px 20px rgba(255, 107, 157, 0.3);
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 12px;
  padding: 5px;
  text-align: center;
  z-index: 9999;
  font-weight: bold;
}

.fixed-back-to-top:hover {
  transform: translateY(-5px) scale(1.1);
  box-shadow: 0 8px 30px rgba(255, 107, 157, 0.4);
}

/* 响应式设计 */
@media (max-width: 768px) {
  .fixed-back-to-top {
    width: 45px;
    height: 45px;
    right: 20px;
    bottom: 40px;
    font-size: 11px;
  }
  
  /* 小屏幕下调整区域高度 */
  .jumbotron-section,
  .core-features-section,
  #hot-topics-section,
  #popular-products-section {
    min-height: 100vh;
    height: auto;
  }
}

/* 巨幕样式 */
.jumbotron-section {
  min-height: 100vh; /* 全屏高度 */
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fff;
  overflow: hidden;
  margin: 0;
  padding: 0;
  position: relative;
  transition: all 0.8s ease;
  margin-top: -70px; /* 负margin，与导航栏重叠，实现全屏效果 */
  padding-top: 70px; /* 补偿负margin，确保内容不被导航栏遮挡 */
}

/* 核心功能区域样式 */
.core-features-section {
  min-height: auto;
  padding: 2.5rem 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.8s ease;
  width: 100%;
}

/* 核心功能卡片悬停效果 */
.core-feature-card:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

/* 热点话题区域样式 */
#hot-topics-section {
  min-height: auto;
  padding: 2.5rem 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  background-color: #f0f9ff;
}

/* 热门商品区域样式 */
#popular-products-section {
  padding: 2.5rem 0;
  background-color: #ffffff;
  min-height: auto;
  display: block;
  width: 100%;
}

/* 热点话题卡片样式改进 */
#hot-topics-section .card {
  border: 1px solid #e9ecef;
  border-radius: 8px;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

#hot-topics-section .card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

#hot-topics-section .card-header {
  background: linear-gradient(135deg, #FF8A5C, #FFD93D);
  color: #ffffff;
  border-bottom: 2px solid #e9ecef;
  padding: 1rem 1.5rem;
}

#hot-topics-section .card-body {
  padding: 1.25rem;
}

/* 修复列表项分隔线 - 使用更具体的选择器 */
#hot-topics-section .card-body ul.list-unstyled li {
  border-bottom: 1px solid #e0f2fe !important;
  padding-bottom: 0.75rem !important;
  margin-bottom: 0.75rem !important;
  display: block !important;
}

#hot-topics-section .card-body ul.list-unstyled li:last-child {
  border-bottom: none !important;
  padding-bottom: 0 !important;
  margin-bottom: 0 !important;
}

#hot-topics-section h4 {
  color: #333;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

#hot-topics-section p {
  color: #6c757d;
  font-size: 0.9rem;
}

#hot-topics-section .bg-primary {
  background-color: #FF8A5C !important;
}

#hot-topics-section .bg-secondary-blue {
  background-color: #6B7A8F !important;
}

#hot-topics-section .text-primary {
  color: #FF8A5C !important;
}

#hot-topics-section .text-secondary-blue {
  color: #6B7A8F !important;
}

/* 文字溢出省略样式 */
.text-ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 热点话题和校园活动标题响应式处理 */
#hot-topics-section .card-body .flex-grow-1 {
  flex: 1;
  min-width: 0; /* 允许子元素收缩 */
}

#hot-topics-section .card-body .flex-grow-1 h4 {
  display: block !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  white-space: nowrap !important;
  width: 100% !important;
  max-width: none !important;
  box-sizing: border-box !important;
}

/* 各区域容器样式 */
.core-features-section .container,
#hot-topics-section .container {
  width: 100%;
  max-width: 1200px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 1.5rem;
}

/* 热门商品容器样式 */
#popular-products-section .container {
  width: 100%;
  max-width: 1200px;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
  margin: 0 auto;
}

/* 渐变文字样式 */
.text-gradient {
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  background-image: linear-gradient(to right, #FF8A5C, #FFD93D);
}

/* 橙色渐变文字样式（与返回顶部按钮一致） */
.text-orange-gradient {
  background-clip: text;
  -webkit-background-clip: text;
  color: transparent;
  background-image: linear-gradient(135deg, #FF6B9D, #FFD93D);
}

/* 核心功能卡片图标样式 */
.core-features-section .feature-icon {
  display: inline-block;
  line-height: 1;
  margin-bottom: 1.5rem;
}

/* 确保图标渐变效果正确显示 */
.core-features-section .feature-icon i {
  display: block;
  line-height: 1;
}

/* 核心功能卡片图片容器样式 - 只应用于核心功能区域 */
.core-features-section .card-image-container {
  height: 280px;
  background-color: #f5f5f5;
  overflow: hidden;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 核心功能卡片图片样式 - 优化显示效果 */
.core-features-section .card-img-top {
  object-fit: contain !important;
  width: auto !important;
  height: 80% !important;
  max-width: 100%;
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  background-color: transparent;
  padding: 0;
  filter: brightness(1);
}

/* 图片悬停效果 */
.core-features-section .card-image-container:hover .card-img-top {
  transform: scale(1.1);
  filter: brightness(1.05);
}

/* 核心功能卡片样式 */
.core-features-section .card {
  height: 100%;
  min-height: 400px;
}

/* 热点话题卡片样式 */
#hot-topics-section .card {
  height: 100%;
  min-height: 350px;
}

/* 失物招领区域样式 */
#lostfound-section {
  min-height: auto;
  background-color: #f0f9ff;
  padding: 4rem 2rem;
  display: block;
  width: 100%;
}

/* 失物招领容器样式 */
#lostfound-section .container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

/* 热门商品区域样式 */
#popular-products-section {
  min-height: auto;
  background-color: #ffffff;
  padding: 4rem 2rem;
  display: block;
  width: 100%;
}

/* 热门商品容器样式 */
#popular-products-section .container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

/* 校园活动区域样式 */
#campus-activities-section {
  min-height: auto;
  background-color: #fef2f2;
  padding: 2.5rem 1rem;
  display: block;
  width: 100%;
}

/* 校园活动容器样式 */
#campus-activities-section .container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}

/* 热门商品标题区域 */
#popular-products-section .d-flex {
  margin-bottom: 2rem;
  justify-content: space-between;
  align-items: center;
}

/* 热门商品标题 */
#popular-products-section h2 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #333;
  margin: 0;
}

/* 查看全部链接 */
#popular-products-section a.text-primary {
  color: #ff6b6b;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
}

#popular-products-section a.text-primary:hover {
  color: #ff5252;
}

/* 失物招领卡片容器样式 */
#lostfound-section .lostfound-card-item {
  /* 确保卡片容器不影响内部布局 */
  padding: 0;
}

/* 确保LostFoundCard组件内部样式统一 */
#lostfound-section :deep(.bg-white) {
  background-color: #ffffff;
}

/* 确保LostFoundCard组件的宽度适应容器 */
#lostfound-section :deep(.rounded-lg) {
  border-radius: 8px;
}

/* 确保LostFoundCard组件的阴影效果 */
#lostfound-section :deep(.hover\:shadow-lg) {
  transition: all 0.3s ease;
}

/* 确保LostFoundCard组件的图片容器样式 */
#lostfound-section :deep(.aspect-square) {
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background-color: #f8f9fa;
  border-radius: 8px 8px 0 0;
}

/* 确保LostFoundCard组件的图片样式 */
#lostfound-section :deep(.aspect-square img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

/* 确保LostFoundCard组件的图片悬停效果 */
#lostfound-section :deep(.aspect-square:hover img) {
  transform: scale(1.05);
}

/* 确保LostFoundCard组件的内容区域样式 */
#lostfound-section :deep(.p-4) {
  padding: 1rem;
}

/* 确保LostFoundCard组件的标题样式 */
#lostfound-section :deep(.font-medium) {
  font-weight: 600;
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.5rem;
  display: block;
  text-decoration: none;
  transition: all 0.3s ease;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  min-height: 2.5rem;
}

/* 确保LostFoundCard组件的标题悬停效果 */
#lostfound-section :deep(.font-medium:hover) {
  color: #ff6b6b;
}

/* 商品卡片容器样式 */
#popular-products-section .product-card-item {
  /* 确保卡片容器不影响内部布局 */
  padding: 0;
}

/* 确保ProductCard组件内部样式统一 */
#popular-products-section :deep(.bg-white) {
  background-color: #ffffff;
}

/* 确保ProductCard组件的宽度适应容器 */
#popular-products-section :deep(.rounded-lg) {
  border-radius: 8px;
}

/* 确保ProductCard组件的阴影效果 */
#popular-products-section :deep(.hover\:shadow-lg) {
  transition: all 0.3s ease;
}

/* 确保ProductCard组件的图片容器样式 */
#popular-products-section :deep(.aspect-square) {
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background-color: #f8f9fa;
  border-radius: 8px 8px 0 0;
}

/* 确保ProductCard组件的图片样式 */
#popular-products-section :deep(.aspect-square img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

/* 确保ProductCard组件的图片悬停效果 */
#popular-products-section :deep(.aspect-square:hover img) {
  transform: scale(1.05);
}

/* 确保ProductCard组件的内容区域样式 */
#popular-products-section :deep(.p-4) {
  padding: 1rem;
}

/* 确保ProductCard组件的标题样式 */
#popular-products-section :deep(.font-medium) {
  font-weight: 600;
  font-size: 1rem;
  color: #333;
  margin-bottom: 0.5rem;
  display: block;
  text-decoration: none;
  transition: all 0.3s ease;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  min-height: 2.5rem;
}

/* 确保ProductCard组件的标题悬停效果 */
#popular-products-section :deep(.font-medium:hover) {
  color: #ff6b6b;
}

/* 确保ProductCard组件的价格样式 */
#popular-products-section :deep(.text-orange-500) {
  color: #ff6b6b;
  font-weight: 700;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
  display: block;
}

/* 确保ProductCard组件的元信息样式 */
#popular-products-section :deep(.text-xs) {
  font-size: 0.75rem;
  color: #6c757d;
}

/* 确保ProductCard组件的分类标签样式 */
#popular-products-section :deep(.bg-orange-50) {
  background-color: #fff5f5;
  color: #ff6b6b;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}



/* 主内容容器样式 */
.main-content-container {
  margin-left: 0; /* 汉堡图标是浮动的，不占据左侧空间 */
  width: 100%;
  transition: all 0.3s ease;
  flex: 1 0 auto;
  display: flex;
  flex-direction: column;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .main-content-container {
    margin-left: 0;
    width: 100%;
  }
}

/* 确保页面内容在粒子效果之上 */
.main-content-container {
  position: relative;
  z-index: 1;
  background-color: white;
  flex: 1;
}

.jumbotron-section,
.core-features-section,
#hot-topics-section,
#popular-products-section,
#campus-activities-section,
footer {
  position: relative;
  z-index: 1;
}

/* 校园活动网格布局 */
.activity-grid-container {
  position: relative;
  width: 100%;
}

.activity-card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.5rem;
  padding: 0;
  width: 100%;
}

.activity-card {
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}

.activity-card:hover {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

/* 活动图标样式 */
.activity-icon {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  height: 100% !important;
  font-size: 4.5rem !important;
  line-height: 1 !important;
  background-clip: text !important;
  -webkit-background-clip: text !important;
  color: transparent !important;
  background-image: linear-gradient(135deg, #FF6B9D, #FFD93D) !important;
}

.activity-icon i {
  display: block !important;
  text-align: center !important;
  margin: 0 !important;
  padding: 0 !important;
}

/* 响应式调整 */
@media (max-width: 768px) {
  .activity-card-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    padding: 0;
  }
  
  .activity-icon {
    font-size: 3.5rem !important;
  }
  
  .activity-card .p-4 {
    padding: 1rem !important;
  }
  
  .activity-card h3 {
    font-size: 1rem !important;
  }
}

@media (min-width: 769px) and (max-width: 1200px) {
  .activity-card-grid {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1.25rem;
    padding: 0;
  }
}
</style>