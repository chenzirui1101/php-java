<template>
  <div class="moderation-page">
    <!-- 页面标题 -->
    <div class="page-header mb-4">
      <h2 class="h4 mb-0">内容审核管理</h2>
      <p class="text-muted mb-0">审核用户发布的内容，确保平台安全</p>
    </div>

    <!-- 统计卡片 -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card bg-warning text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-0">待审核</h6>
                <h3 class="mb-0">{{ stats.pending }}</h3>
              </div>
              <i class="fa fa-clock-o fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-success text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-0">今日通过</h6>
                <h3 class="mb-0">{{ stats.approved }}</h3>
              </div>
              <i class="fa fa-check-circle fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-danger text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-0">今日拒绝</h6>
                <h3 class="mb-0">{{ stats.rejected }}</h3>
              </div>
              <i class="fa fa-times-circle fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card bg-info text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="card-title mb-0">审核率</h6>
                <h3 class="mb-0">{{ stats.rate }}%</h3>
              </div>
              <i class="fa fa-chart-line fa-2x"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 筛选栏 -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-3">
            <label class="form-label">内容类型</label>
            <select v-model="filter.type" class="form-control" @change="handleFilterChange">
              <option value="all">全部类型</option>
              <option value="product">商品</option>
              <option value="topic">论坛话题</option>
              <option value="comment">评论</option>
              <option value="rating">商品评价</option>
              <option value="lostfound">失物招领</option>
              <option value="activity">活动</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">状态</label>
            <select v-model="filter.status" class="form-control" @change="handleFilterChange">
              <option value="pending">待审核</option>
              <option value="approved">已通过</option>
              <option value="rejected">已拒绝</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">搜索</label>
            <input 
              v-model="filter.keyword" 
              type="text" 
              class="form-control" 
              placeholder="搜索标题或内容..."
              @keyup.enter="handleFilterChange"
            >
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary w-100" @click="handleFilterChange">
              <i class="fa fa-search mr-1"></i> 筛选
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 内容列表 -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">内容列表</h5>
        <button class="btn btn-sm btn-outline-primary" @click="refreshList">
          <i class="fa fa-refresh mr-1"></i> 刷新
        </button>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">加载中...</span>
          </div>
          <p class="mt-2 text-muted">加载中...</p>
        </div>
        
        <div v-else-if="contentList.length === 0" class="text-center py-5">
          <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
          <p class="text-muted">暂无待审核内容</p>
        </div>
        
        <table v-else class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th width="80">ID</th>
              <th width="100">类型</th>
              <th>标题/内容</th>
              <th width="120">图片</th>
              <th width="120">作者</th>
              <th width="150">发布时间</th>
              <th width="100">状态</th>
              <th width="150">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in contentList" :key="item.id + item.type">
              <td>{{ item.id }}</td>
              <td>
                <span :class="getTypeClass(item.type)">
                  {{ getTypeLabel(item.type) }}
                </span>
              </td>
              <td>
                <div class="content-preview">
                  <strong :title="item.title">{{ truncateText(item.title) }}</strong>
                  <p class="text-muted mb-0" :title="item.content" style="max-width: 400px;">
                    {{ truncateText(item.content) }}
                  </p>
                </div>
              </td>
              <td>
                <div v-if="item.image" class="image-preview">
                  <img :src="item.image" :alt="item.title" class="img-thumbnail" style="max-width: 80px; max-height: 60px;" @click="previewImage(item.image)">
                </div>
                <span v-else class="text-muted">无图片</span>
              </td>
              <td>{{ item.author }}</td>
              <td>{{ item.created_at }}</td>
              <td>
                <span :class="getStatusClass(item.status)">
                  {{ getStatusLabel(item.status) }}
                </span>
              </td>
              <td>
                <button 
                  class="btn btn-sm btn-success mr-1" 
                  @click="handleApprove(item)"
                  title="通过"
                >
                  <i class="fa fa-check"></i>
                </button>
                <button 
                  class="btn btn-sm btn-danger mr-1" 
                  @click="handleReject(item)"
                  title="拒绝"
                >
                  <i class="fa fa-times"></i>
                </button>
                <button 
                  class="btn btn-sm btn-info" 
                  @click="viewDetail(item)"
                  title="查看详情"
                >
                  <i class="fa fa-eye"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- 分页 -->
      <div v-if="contentList.length > 0" class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-muted">
            共 {{ pagination.total }} 条记录，第 {{ pagination.page }} / {{ Math.ceil(pagination.total / pagination.pageSize) }} 页
          </span>
          <nav>
            <ul class="pagination mb-0">
              <li class="page-item" :class="{ disabled: pagination.page <= 1 }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.page - 1)">上一页</a>
              </li>
              <li 
                v-for="page in pageNumbers" 
                :key="page" 
                class="page-item"
                :class="{ active: page === pagination.page }"
              >
                <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
              </li>
              <li class="page-item" :class="{ disabled: pagination.page >= Math.ceil(pagination.total / pagination.pageSize) }">
                <a class="page-link" href="#" @click.prevent="changePage(pagination.page + 1)">下一页</a>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- 详情弹窗 -->
    <div v-if="showDetailModal" class="modal fade show" style="display: block; overflow-y: auto;" tabindex="-1">
      <div class="modal-dialog modal-lg" style="max-width: 800px;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">内容详情</h5>
            <button type="button" class="close" @click="closeDetailModal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div v-if="currentItem">
              <div class="form-group">
                <label>类型</label>
                <p class="form-control-static">{{ getTypeLabel(currentItem.type) }}</p>
              </div>
              <div class="form-group">
                <label>标题</label>
                <p class="form-control-static">{{ currentItem.title }}</p>
              </div>
              <div v-if="currentItem.image" class="form-group">
                <label>图片</label>
                <div class="mt-2">
                  <img :src="currentItem.image" :alt="currentItem.title" class="img-thumbnail" style="max-width: 100%; height: auto; max-height: 300px; object-fit: contain;" @click="previewImage(currentItem.image)">
                </div>
              </div>
              <div class="form-group">
                <label>内容</label>
                <div class="border p-3 bg-light" style="word-break: break-word;">
                  {{ currentItem.content }}
                </div>
              </div>
              <div class="form-group">
                <label>作者</label>
                <p class="form-control-static">{{ currentItem.author }}</p>
              </div>
              <div class="form-group">
                <label>发布时间</label>
                <p class="form-control-static">{{ currentItem.created_at }}</p>
              </div>
            </div>
          </div>
          <div class="modal-footer" style="position: sticky; bottom: 0; background-color: #fff; border-top: 1px solid #e9ecef;">
            <button type="button" class="btn btn-secondary" @click="closeDetailModal">关闭</button>
            <button type="button" class="btn btn-success" @click="handleApprove(currentItem)">审核通过</button>
            <button type="button" class="btn btn-danger" @click="handleReject(currentItem)">审核拒绝</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showDetailModal" class="modal-backdrop fade show"></div>

    <!-- 拒绝原因弹窗 -->
    <div v-if="showRejectModal" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">拒绝原因</h5>
            <button type="button" class="close" @click="closeRejectModal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>请选择或输入拒绝原因：</label>
              <select v-model="rejectReason" class="form-control mb-2">
                <option value="内容包含敏感信息">内容包含敏感信息</option>
                <option value="内容涉及违法违规">内容涉及违法违规</option>
                <option value="内容质量低劣">内容质量低劣</option>
                <option value="内容与平台无关">内容与平台无关</option>
                <option value="其他原因">其他原因</option>
              </select>
              <textarea 
                v-if="rejectReason === '其他原因'" 
                v-model="customRejectReason" 
                class="form-control" 
                rows="3" 
                placeholder="请输入具体原因..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeRejectModal">取消</button>
            <button type="button" class="btn btn-danger" @click="confirmReject">确认拒绝</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showRejectModal" class="modal-backdrop fade show"></div>

    <!-- 图片预览弹窗 -->
    <div v-if="showImagePreview" class="modal fade show" style="display: block;" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">图片预览</h5>
            <button type="button" class="close" @click="closeImagePreview">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <img :src="previewImageUrl" class="img-fluid" style="max-height: 600px; object-fit: contain;" alt="预览图片">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeImagePreview">关闭</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showImagePreview" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { moderationApi } from '@/api'

// 统计数据
const stats = reactive({
  pending: 0,
  approved: 12,
  rejected: 3,
  rate: 98
})

// 筛选条件
const filter = reactive({
  type: 'all',
  status: 'pending',
  keyword: ''
})

// 列表数据
const loading = ref(false)
const contentList = ref([])
const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

// 弹窗控制
const showDetailModal = ref(false)
const showRejectModal = ref(false)
const showImagePreview = ref(false)
const currentItem = ref(null)
const rejectReason = ref('内容包含敏感信息')
const customRejectReason = ref('')
const previewImageUrl = ref('')

// 类型标签
const typeLabels = {
  product: '商品',
  topic: '话题',
  comment: '评论',
  rating: '商品评价',
  lostfound: '失物招领',
  activity: '活动'
}

// 状态标签
const statusLabels = {
  pending: '待审核',
  approved: '已通过',
  rejected: '已拒绝'
}

// 计算页码
const pageNumbers = computed(() => {
  const total = Math.ceil(pagination.total / pagination.pageSize)
  const current = pagination.page
  const pages = []
  
  for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
    pages.push(i)
  }
  return pages
})

// 获取列表
const fetchList = async () => {
  loading.value = true
  try {
    const res = await moderationApi.getPendingList({
      type: filter.type,
      page: pagination.page,
      pageSize: pagination.pageSize
    })
    
    if (res.code === 200) {
      contentList.value = res.data.list || []
      pagination.total = res.data.total || 0
      stats.pending = pagination.total
    }
  } catch (error) {
    console.error('获取列表失败:', error)
    alert('获取列表失败')
  } finally {
    loading.value = false
  }
}

// 刷新列表
const refreshList = () => {
  pagination.page = 1
  fetchList()
}

// 筛选变化
const handleFilterChange = () => {
  pagination.page = 1
  fetchList()
}

// 翻页
const changePage = (page) => {
  if (page < 1 || page > Math.ceil(pagination.total / pagination.pageSize)) return
  pagination.page = page
  fetchList()
}

// 获取类型样式
const getTypeClass = (type) => {
  const classes = {
    product: 'badge badge-primary',
    topic: 'badge badge-success',
    comment: 'badge badge-info',
    lostfound: 'badge badge-warning',
    activity: 'badge badge-danger'
  }
  return classes[type] || 'badge badge-secondary'
}

// 获取类型标签
const getTypeLabel = (type) => {
  return typeLabels[type] || type
}

// 获取状态样式
const getStatusClass = (status) => {
  const classes = {
    pending: 'badge badge-warning',
    approved: 'badge badge-success',
    rejected: 'badge badge-danger'
  }
  return classes[status] || 'badge badge-secondary'
}

// 获取状态标签
const getStatusLabel = (status) => {
  return statusLabels[status] || status
}

// 查看详情
const viewDetail = (item) => {
  currentItem.value = item
  showDetailModal.value = true
}

// 关闭详情弹窗
const closeDetailModal = () => {
  showDetailModal.value = false
  currentItem.value = null
}

// 审核通过
const handleApprove = async (item) => {
  if (!confirm('确定要通过这条内容吗？')) return
  
  try {
    const res = await moderationApi.approve(item.type, item.id)
    if (res.code === 200) {
      alert('审核通过成功')
      stats.approved++
      fetchList()
      closeDetailModal()
    } else {
      alert(res.msg || '操作失败')
    }
  } catch (error) {
    console.error('审核通过失败:', error)
    alert('审核通过失败')
  }
}

// 打开拒绝弹窗
const handleReject = (item) => {
  currentItem.value = item
  showRejectModal.value = true
}

// 关闭拒绝弹窗
const closeRejectModal = () => {
  showRejectModal.value = false
  currentItem.value = null
  rejectReason.value = '内容包含敏感信息'
  customRejectReason.value = ''
}

// 确认拒绝
const confirmReject = async () => {
  const reason = rejectReason.value === '其他原因' 
    ? customRejectReason.value 
    : rejectReason.value
    
  if (!reason.trim()) {
    alert('请输入拒绝原因')
    return
  }
  
  try {
    const res = await moderationApi.reject(currentItem.value.type, currentItem.value.id, reason)
    if (res.code === 200) {
      alert('审核拒绝成功')
      stats.rejected++
      fetchList()
      closeRejectModal()
      closeDetailModal()
    } else {
      alert(res.msg || '操作失败')
    }
  } catch (error) {
    console.error('审核拒绝失败:', error)
    alert('审核拒绝失败')
  }
}

// 预览图片
const previewImage = (url) => {
  previewImageUrl.value = url
  showImagePreview.value = true
}

// 关闭图片预览
const closeImagePreview = () => {
  showImagePreview.value = false
  previewImageUrl.value = ''
}

// 文本截断函数
const truncateText = (text) => {
  if (!text) return ''
  return text.length > 20 ? text.substring(0, 20) + '...' : text
}

// 初始化
onMounted(() => {
  fetchList()
})
</script>

<style scoped>
.moderation-page {
  padding: 20px;
}

.page-header {
  border-bottom: 1px solid #e9ecef;
  padding-bottom: 15px;
}

.content-preview {
  max-width: 400px;
}

.content-preview p {
  font-size: 0.875rem;
  margin-top: 4px;
}

.badge {
  padding: 0.5em 0.75em;
  font-size: 0.75rem;
}

.card {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
  font-weight: 600;
  border-top: none;
}

.modal-dialog {
  margin-top: 100px;
}

.image-preview {
  cursor: pointer;
}

.image-preview img {
  transition: transform 0.3s ease;
}

.image-preview img:hover {
  transform: scale(1.05);
}
</style>
