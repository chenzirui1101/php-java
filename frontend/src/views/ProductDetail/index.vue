<template>
  <div class="product-detail-page">
    <!-- 固定定位的返回按钮 -->
    <div class="fixed-back-button" @click="goBack">
      <i class="fa fa-arrow-left"></i>
      <span>返回</span>
    </div>
    
    <!-- 面包屑导航 -->
    <div class="top-navigation">
      <nav aria-label="breadcrumb" class="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item" @click="goBackHome"><a href="#">首页</a></li>
          <li class="breadcrumb-item active">{{ product.category || '商品详情' }}</li>
        </ol>
      </nav>
    </div>
    
    <div class="card detail-card shadow-sm">
      <div class="card-body">
        <div class="row detail-container">
          <!-- 商品图片 -->
          <div class="col-12 col-md-12 col-lg-6">
            <div class="detail-img-wrapper">
              <img 
                :src="product.coverImg || 'https://picsum.photos/600/600'" 
                class="detail-img img-fluid" 
                alt="商品图片"
              >
            </div>
          </div>

          <!-- 商品信息 -->
          <div class="col-12 col-md-12 col-lg-6">
            <div class="detail-info">
              <h1 class="detail-title">{{ product.title }}</h1>
              <div class="detail-price">¥{{ product.price }}</div>
              
              <hr class="info-divider">
              
              <div class="info-row row">
                <div class="col-4 info-label">分类</div>
                <div class="col-8 info-value">{{ product.category }}</div>
              </div>
              <div class="info-row row">
                <div class="col-4 info-label">卖家</div>
                <div class="col-8 info-value">{{ product.sellerName }}</div>
              </div>
              <div class="info-row row">
                <div class="col-4 info-label">发布时间</div>
                <div class="col-8 info-value">{{ product.createTime }}</div>
              </div>

              <hr class="info-divider">
              
              <div class="detail-desc">
                <h3 class="desc-title">商品描述</h3>
                <div class="desc-content">{{ product.description || '暂无描述' }}</div>
              </div>

              <!-- 操作按钮 -->
              <div class="detail-actions">
                <button class="btn btn-primary btn-lg action-btn primary-btn" @click="contactSeller">
                  <i class="fa fa-comments"></i>
                  联系卖家
                </button>
                <button class="btn btn-success btn-lg action-btn success-btn" @click="buyNow">
                  <i class="fa fa-shopping-cart"></i>
                  立即购买
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 评价系统 -->
    <div class="card detail-card ratings-card shadow-sm" style="margin-top: 20px;">
      <div class="card-header">
        <div class="ratings-header">
          <h2>商品评价</h2>
          <div class="rating-stats">
            <span class="average-rating">
              <strong>{{ averageRating || 0 }}</strong>分
            </span>
            <span class="rating-count">({{ ratingCount || 0 }}条评价)</span>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- 评价表单 -->
        <div v-if="userStore.isLogin" class="rating-form">
          <h3>发表评价</h3>
          <form ref="ratingFormRef">
            <div class="form-group">
              <label for="rating">评分</label>
              <div class="rating-stars">
                <i v-for="star in 5" 
                   :key="star"
                   class="fa" 
                   :class="star <= ratingForm.rating ? 'fa-star' : 'fa-star-o'" 
                   @click="ratingForm.rating = star"
                   style="color: #ff9900; cursor: pointer; font-size: 20px; margin-right: 5px;">
                </i>
                <span class="rating-score">{{ ratingForm.rating }}分</span>
              </div>
            </div>
            <div class="form-group">
              <label for="comment">评论</label>
              <textarea 
                v-model="ratingForm.comment" 
                class="form-control" 
                rows="4" 
                placeholder="请输入您的评价..."
              ></textarea>
            </div>
            <div class="form-group">
              <button type="button" class="btn btn-primary" @click="submitRating" :disabled="submittingRating">
                {{ submittingRating ? '提交中...' : '提交评价' }}
              </button>
            </div>
          </form>
        </div>
        
        <!-- 评价列表 -->
        <div class="ratings-list">
          <h3>评价列表</h3>
          <div v-if="ratings.length === 0" class="empty-ratings alert alert-info">
            暂无评价，快来发表第一条评价吧
          </div>
          <div v-else class="timeline">
            <div v-for="rating in ratings" :key="rating.id" class="timeline-item">
              <div class="timeline-marker"></div>
              <div class="timeline-content">
                <div class="card rating-item shadow-sm">
                  <div class="card-body">
                    <div class="rating-item-header">
                      <span class="username">{{ rating.user.name }}</span>
                      <div class="rating-stars">
                        <i v-for="star in 5" 
                           :key="star"
                           class="fa" 
                           :class="star <= rating.rating ? 'fa-star' : 'fa-star-o'" 
                           style="color: #ff9900; font-size: 16px; margin-right: 3px;">
                        </i>
                        <span class="rating-score">{{ rating.rating }}分</span>
                      </div>
                      <span class="rating-time">{{ formatDate(rating.created_at) }}</span>
                    </div>
                    <div class="rating-item-content">
                      {{ rating.comment || '该用户没有留下评论' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- 分页 -->
          <div class="ratings-pagination" v-if="ratingTotal > 0">
            <nav aria-label="Page navigation">
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: ratingPageNum === 1 }">
                  <a class="page-link" href="#" @click.prevent="handleRatingCurrentChange(ratingPageNum - 1)">上一页</a>
                </li>
                <li class="page-item" :class="{ active: ratingPageNum === page }" v-for="page in Math.ceil(ratingTotal / ratingPageSize)" :key="page">
                  <a class="page-link" href="#" @click.prevent="handleRatingCurrentChange(page)">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: ratingPageNum === Math.ceil(ratingTotal / ratingPageSize) }">
                  <a class="page-link" href="#" @click.prevent="handleRatingCurrentChange(ratingPageNum + 1)">下一页</a>
                </li>
              </ul>
              <div class="pagination-info">
                <span>共 {{ ratingTotal }} 条记录</span>
                <select v-model="ratingPageSize" class="form-control-sm" @change="handleRatingSizeChange(ratingPageSize)">
                  <option :value="5">5条/页</option>
                  <option :value="10">10条/页</option>
                  <option :value="20">20条/页</option>
                </select>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

import { productApi } from '@/api/modules/product'
import { chatApi } from '@/api/modules/chat'
import { ratingApi } from '@/api/modules/rating'
import { useUserStore } from '@/stores/modules/user'

const router = useRouter()
const route = useRoute()

// 用户状态
const userStore = useUserStore()

// 商品详情数据
const product = ref({})

// 评价系统相关状态
const ratings = ref([])
const ratingTotal = ref(0)
const ratingPageNum = ref(1)
const ratingPageSize = ref(10)
const submittingRating = ref(false)
const ratingFormRef = ref(null)
const ratingForm = ref({
  product_id: route.params.id,
  rating: 5,
  comment: ''
})
const ratingRules = {
  rating: [{ required: true, message: '请选择评分', trigger: 'change' }]
}

// 评价统计
const averageRating = ref(0)
const ratingCount = ref(0)

// 获取商品详情
const getProductDetail = async () => {
  try {
    const res = await productApi.getProductDetail(route.params.id)
    product.value = res.data
  } catch (err) {
    console.error('获取商品详情失败:', err)
      alert('获取商品详情失败')
    // 失败后不再自动跳转到首页，让用户停留在当前页面
    // router.push('/')
  }
}

// 获取商品评价
const getProductRatings = async () => {
  try {
    const res = await ratingApi.getProductRatings(route.params.id, {
      page: ratingPageNum.value,
      per_page: ratingPageSize.value
    })
    if (res.code === 200) {
      ratings.value = res.data.data || res.data
      ratingTotal.value = res.data.total || 0
      // 更新评价统计
      if (res.data.data && res.data.data.length > 0) {
        averageRating.value = res.data.data.reduce((sum, rating) => sum + rating.rating, 0) / res.data.data.length
        ratingCount.value = res.data.data.length
      }
    }
  } catch (err) {
    console.error('获取评价失败:', err)
  }
}

// 提交评价
const submitRating = async () => {
  // 简单的表单验证
  if (!ratingForm.value.rating) {
    alert('请选择评分')
    return
  }
  
  try {
    submittingRating.value = true
    const res = await ratingApi.createRating({
      product_id: route.params.id,
      rating: ratingForm.value.rating,
      comment: ratingForm.value.comment
    })
    if (res.code === 200) {
      alert('评价已提交，等待管理员审核')
      // 重置表单
      ratingForm.value = {
        product_id: route.params.id,
        rating: 5,
        comment: ''
      }
      // 刷新评价列表
      await getProductRatings()
    }
  } catch (err) {
    console.error('评价提交失败:', err)
    alert('评价提交失败')
  } finally {
    submittingRating.value = false
  }
}

// 格式化日期
const formatDate = (date) => {
  if (!date) return ''
  try {
    return new Date(date).toLocaleString()
  } catch (e) {
    return date
  }
}

// 评价分页处理
const handleRatingSizeChange = (size) => {
  ratingPageSize.value = size
  ratingPageNum.value = 1
  getProductRatings()
}

const handleRatingCurrentChange = (current) => {
  ratingPageNum.value = current
  getProductRatings()
}

// 联系卖家
const contactSeller = async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('请先登录后联系卖家')
    router.push('/login')
    return
  }
  
  if (!product.value.id) {
      console.error('商品ID不存在:', product.value)
      alert('商品信息错误，请刷新页面重试')
      return
    }
    
    // 检查是否是自己发布的商品，避免与自己聊天
    if (product.value.seller_id === userStore.userInfo.id) {
      alert('不能与自己聊天')
      return
    }
  
  try {
    console.log('开始创建会话，商品ID:', product.value.id)
    // 创建或获取会话
    const res = await chatApi.createConversation({
      resourceType: 'product',
      resourceId: product.value.id
    })
    console.log('会话创建响应:', res)
    if (res && res.code === 200 && res.data && res.data.id) {
      console.log('会话创建成功，会话ID:', res.data.id)
      // 跳转到聊天页面，并传递会话ID
      router.push({
        path: '/chat',
        query: { conversation_id: res.data.id }
      })
    } else {
          console.error('创建会话失败:', res)
          alert(res?.msg || '创建会话失败')
        }
  } catch (error) {
    console.error('联系卖家失败:', error)
    console.error('错误详情:', error.response || error.message || error)
    alert('联系卖家失败，请稍后重试')
  }
}

// 立即购买（模拟）
const buyNow = () => {
  const token = localStorage.getItem('token')
  if (!token) {
    alert('请先登录后购买')
    router.push('/login')
    return
  }
  if (confirm('确定购买该商品吗？')) {
    alert('购买请求已提交，等待卖家确认')
    // 后续可对接后端购买接口
  } else {
    // 用户取消购买
  }
}

// 返回首页
const goBackHome = () => {
  router.push('/')
}

// 返回上一页
const goBack = () => {
  router.back()
}

// 页面加载获取详情和评价
onMounted(() => {
  getProductDetail()
  getProductRatings()
})
</script>

<style scoped>
/* 页面容器 */
.product-detail-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  min-height: 100vh;
  background-color: #f5f7fa;
}

/* 确保顶部导航可见 */
.top-navigation {
  display: flex !important;
  align-items: center !important;
  gap: 20px !important;
  margin-bottom: 20px !important;
  flex-wrap: wrap !important;
}

/* 确保返回按钮可见 */
.back-button {
  display: flex !important;
  align-items: center !important;
  gap: 5px !important;
  padding: 8px 15px !important;
  font-size: 14px !important;
  border-radius: 8px !important;
  transition: all 0.3s ease !important;
  background-color: #fff !important;
  border: 1px solid #dcdfe6 !important;
  color: #606266 !important;
}

.back-button:hover {
  background-color: #ecf5ff !important;
  border-color: #c6e2ff !important;
  color: #409eff !important;
}

/* 固定定位的返回按钮，确保在模态框中可见 */
.fixed-back-button {
  position: fixed !important;
  top: 80px !important;
  left: 20px !important;
  z-index: 9999 !important;
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 12px 24px !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  border-radius: 25px !important;
  background: linear-gradient(135deg, #409eff 0%, #66b1ff 100%) !important;
  color: #fff !important;
  cursor: pointer !important;
  box-shadow: 0 4px 15px rgba(64, 158, 255, 0.4) !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  border: none !important;
  backdrop-filter: blur(10px) !important;
}

.fixed-back-button:hover {
  background: linear-gradient(135deg, #66b1ff 0%, #8cc6ff 100%) !important;
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 6px 20px rgba(64, 158, 255, 0.6) !important;
}

.fixed-back-button:active {
  transform: translateY(-1px) scale(0.98) !important;
}

.fixed-back-button i {
  font-size: 18px !important;
  transition: transform 0.3s ease !important;
}

.fixed-back-button:hover i {
  transform: translateX(-3px) !important;
}

/* 顶部导航容器 */
.top-navigation {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

/* 返回按钮 */
.back-button {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 8px 15px;
  font-size: 14px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

/* 面包屑导航 */
.breadcrumb {
  font-size: 14px;
  margin-bottom: 0;
  flex: 1;
}

/* 卡片容器 */
.detail-card {
  border-radius: 12px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  background-color: #fff;
  overflow: hidden;
}

/* 内容容器 */
.detail-container {
  padding: 20px 0;
}

/* 图片容器 */
.detail-img-wrapper {
  width: 100%;
  border-radius: 8px;
  overflow: hidden;
  background-color: #fafafa;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  max-height: 600px;
}

/* 商品图片 */
.detail-img {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.detail-img:hover {
  transform: scale(1.02);
}

/* 商品信息 */
.detail-info {
  padding: 0 10px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* 商品标题 */
.detail-title {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 15px;
  color: #303133;
  line-height: 1.3;
}

/* 商品价格 */
.detail-price {
  font-size: 32px;
  font-weight: 700;
  color: #f56c6c;
  margin-bottom: 20px;
}

/* 信息分隔线 */
.info-divider {
  margin: 20px 0;
  border-color: #ebeef5;
}

/* 信息行 */
.info-row {
  display: flex;
  margin-bottom: 12px;
  align-items: center;
}

/* 信息标签 */
.info-label {
  font-size: 14px;
  color: #909399;
  font-weight: 500;
  flex-shrink: 0;
}

/* 信息值 */
.info-value {
  font-size: 14px;
  color: #606266;
  flex: 1;
}

/* 商品描述 */
.detail-desc {
  margin: 20px 0;
}

/* 描述标题 */
.desc-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 12px;
}

/* 描述内容 */
.desc-content {
  font-size: 14px;
  color: #606266;
  line-height: 1.8;
  white-space: pre-wrap;
  word-break: break-word;
  background-color: #fafafa;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #f0f0f0;
}

/* 操作按钮容器 */
.detail-actions {
  display: flex;
  gap: 12px;
  margin-top: 30px;
  flex-wrap: wrap;
}

/* 操作按钮 */
.action-btn {
  flex: 1;
  min-width: 120px;
  height: 50px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 25px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: none !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* 主要按钮 */
.primary-btn {
  background: linear-gradient(135deg, #409eff 0%, #66b1ff 100%);
  color: #fff !important;
}

.primary-btn:hover {
  background: linear-gradient(135deg, #66b1ff 0%, #8cc6ff 100%);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(64, 158, 255, 0.4);
}

/* 成功按钮 */
.success-btn {
  background: linear-gradient(135deg, #67c23a 0%, #85ce61 100%);
  color: #fff !important;
}

.success-btn:hover {
  background: linear-gradient(135deg, #85ce61 0%, #a6e387 100%);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 16px rgba(103, 194, 58, 0.4);
}

/* 确保back-button样式正确 */
.back-button {
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  color: #409eff !important;
  border: 1px solid #c6e2ff !important;
  padding: 8px 15px !important;
  border-radius: 8px !important;
  transition: all 0.3s ease !important;
}

.back-button:hover {
  background: linear-gradient(135deg, #ecf5ff 0%, #c6e2ff 100%) !important;
  border-color: #99d1ff !important;
  color: #337ecc !important;
}

/* 响应式设计 */
@media (max-width: 768px) {
  /* 手机端页面边距 */
  .product-detail-page {
    padding: 10px;
  }
  
  /* 手机端标题大小 */
  .detail-title {
    font-size: 20px;
  }
  
  /* 手机端价格大小 */
  .detail-price {
    font-size: 28px;
  }
  
  /* 手机端图片容器高度 */
  .detail-img-wrapper {
    min-height: 250px;
    max-height: 400px;
  }
  
  /* 手机端信息行 */
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 15px;
  }
  
  /* 手机端信息标签 */
  .info-label {
    margin-bottom: 5px;
  }
  
  /* 手机端操作按钮 */
  .detail-actions {
    flex-direction: column;
  }
  
  .action-btn {
    width: 100%;
    min-width: auto;
  }
  
  /* 手机端商品信息内边距 */
  .detail-info {
    padding: 10px;
  }
  
  /* 手机端描述内容 */
  .desc-content {
    padding: 12px;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  /* 平板端页面边距 */
  .product-detail-page {
    padding: 15px;
  }
  
  /* 平板端图片容器高度 */
  .detail-img-wrapper {
    max-height: 500px;
  }
}

/* 评价系统样式 */
.ratings-card {
  margin-top: 20px;
}

.ratings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ratings-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
}

.rating-stats {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 16px;
}

.average-rating strong {
  font-size: 24px;
  color: #ff9900;
}

.rating-count {
  color: #909399;
}

.rating-form {
  margin-bottom: 30px;
  padding: 20px;
  background-color: #fafafa;
  border-radius: 8px;
}

.rating-form h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 16px;
  font-weight: 600;
}

.ratings-list h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 16px;
  font-weight: 600;
}

.ratings-list {
  margin-top: 30px;
}

.empty-ratings {
  text-align: center;
  padding: 40px 0;
}

.rating-item {
  margin-bottom: 15px;
  border-radius: 8px;
  overflow: hidden;
}

.rating-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.username {
  font-weight: 600;
  color: #303133;
}

.rating-item-content {
  color: #606266;
  line-height: 1.6;
}

.ratings-pagination {
  margin-top: 20px;
  text-align: center;
}



/* 响应式评价系统 */
@media (max-width: 768px) {
  .ratings-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .rating-form {
    padding: 15px;
  }
  
  .rating-stats {
    margin-top: 10px;
  }
}
</style>