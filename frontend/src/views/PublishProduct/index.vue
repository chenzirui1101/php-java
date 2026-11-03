<template>
  <div class="publish-product-container">
    <div class="publish-header">
      <h1 class="publish-title">{{ editId ? '编辑商品' : '发布商品' }}</h1>
      <p class="publish-subtitle">{{ editId ? '修改您的商品信息' : '轻松发布您的二手商品，让闲置物品找到新主人' }}</p>
    </div>
    
    <!-- 违禁品发布提示 -->
    <div class="alert alert-danger prohibited-alert">
      <div class="alert-icon">
        <i class="fa fa-exclamation-triangle"></i>
      </div>
      <div class="alert-content">
        <h3>违禁品发布提示</h3>
        <p>根据相关法律法规及平台规则，以下商品禁止发布：</p>
        <ul class="prohibited-list">
          <li>管制器具：枪支、弹药、军用器械、管制刀具、弓弩、电击器等；</li>
          <li>危险品：爆炸物、易燃易爆品、剧毒化学品、放射性物品；</li>
          <li>违禁药品：毒品、麻醉药品、精神药品、处方药、烟草制品；</li>
          <li>违法违规证件：身份证、护照、驾驶证、学历证明、各类公章；</li>
          <li>野生动物及制品：国家重点保护野生动物、濒危物种及其制品；</li>
          <li>色情低俗：淫秽物品、色情服务、低俗内容；</li>
          <li>赌博诈骗：赌博工具、赌博技巧、诈骗教程、钓鱼网站链接；</li>
          <li>其他法律法规禁止销售的商品及服务。</li>
        </ul>
        <p class="reminder">请自觉遵守，违规发布将直接删除并保留证据。</p>
      </div>
    </div>
    
    <div class="publish-form-wrapper">
      <form ref="formRef" class="publish-form">
        <!-- 商品标题 -->
        <div class="form-group row mb-4">
          <label for="title" class="col-sm-2 col-form-label text-right">商品标题</label>
          <div class="col-sm-10">
            <input
              type="text"
              id="title"
              v-model="formData.title"
              placeholder="请输入商品标题（如：99新小米14手机）"
              maxlength="50"
              class="form-control form-input"
            >
            <small class="form-text text-muted">{{ formData.title.length }}/50</small>
          </div>
        </div>

        <!-- 商品分类 -->
        <div class="form-group row mb-4">
          <label for="category" class="col-sm-2 col-form-label text-right">商品分类</label>
          <div class="col-sm-10">
            <select 
              id="category" 
              v-model="formData.category_id" 
              placeholder="请选择商品分类" 
              class="form-control form-select"
            >
              <option value="" disabled>请选择商品分类</option>
              <option value="1">数码产品</option>
              <option value="2">生活用品</option>
              <option value="3">学习资料</option>
              <option value="4">服饰鞋包</option>
              <option value="5">其他</option>
            </select>
          </div>
        </div>

        <!-- 商品价格 -->
        <div class="form-group row mb-4">
          <label for="price" class="col-sm-2 col-form-label text-right">商品价格</label>
          <div class="col-sm-10">
            <div class="price-input-wrapper">
              <input
                type="number"
                id="price"
                v-model="formData.price"
                :min="0.01"
                :max="99999.99"
                step="0.01"
                placeholder="请输入价格"
                class="form-control form-input-number"
              >
              <span class="price-unit">元</span>
            </div>
          </div>
        </div>

        <!-- 商品图片 -->
        <div class="form-group row mb-4">
          <label class="col-sm-2 col-form-label text-right">商品图片</label>
          <div class="col-sm-10">
            <div class="upload-section">
              <div class="cover-uploader">
                <input 
                  type="file" 
                  ref="fileInput" 
                  style="display: none;" 
                  accept="image/jpeg,image/png" 
                  @change="handleFileChange"
                >
                <div v-if="formData.cover_img" class="uploaded-img-wrapper" @click="triggerFileInput">
                  <img :src="formData.cover_img" class="cover-img" />
                  <div class="img-overlay">
                    <i class="fa fa-camera overlay-icon"></i>
                    <span class="overlay-text">更换图片</span>
                  </div>
                </div>
                <div v-else class="upload-placeholder" @click="triggerFileInput">
                  <i class="fa fa-plus upload-icon"></i>
                  <p class="upload-text">点击上传商品图片</p>
                  <p class="upload-hint">建议尺寸 200x200，支持 jpg/png，不超过 2MB</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 商品描述 -->
        <div class="form-group row mb-4">
          <label for="description" class="col-sm-2 col-form-label text-right">商品描述</label>
          <div class="col-sm-10">
            <textarea
              id="description"
              v-model="formData.description"
              rows="6"
              placeholder="请详细描述商品情况、使用感受、新旧程度等信息，让买家更了解您的商品"
              maxlength="500"
              class="form-control form-textarea"
            ></textarea>
            <small class="form-text text-muted">{{ formData.description.length }}/500</small>
          </div>
        </div>

        <!-- 提交按钮 -->
        <div class="form-group row form-actions">
          <div class="col-sm-10 offset-sm-2">
            <div class="action-buttons">
              <button type="button" @click="handleCancel" class="btn btn-secondary cancel-btn">取消</button>
              <button type="button" @click="handleSubmit" :disabled="loading" class="btn btn-primary submit-btn">
                <span v-if="loading"><i class="fa fa-spinner fa-spin"></i> {{ editId ? '保存中...' : '发布中...' }}</span>
                <span v-else>{{ editId ? '保存修改' : '发布商品' }}</span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { productApi } from '@/api/modules/product'

const router = useRouter()
const route = useRoute()

// 定义 props
const props = defineProps({
  editId: {
    type: Number,
    default: null
  }
})

// 定义事件 - 修复：使用 defineEmits
const emit = defineEmits(['submit', 'cancel'])

// 表单引用
const formRef = ref(null)
// 触发文件选择
const fileInput = ref(null)

// 加载状态
const loading = ref(false)

// 上传配置
import request from '@/utils/request'
const uploadUrl = '/api/upload/image';
const uploadHeaders = {
  Authorization: `Bearer ${localStorage.getItem('token')}`
};

// 表单数据
const formData = reactive({
  title: '',
  category_id: '',
  price: null,
  cover_img: '',
  description: ''
});

// 表单验证规则
const formRules = reactive({
  title: [
    { required: true, message: '请输入商品标题', trigger: 'blur' },
    { min: 2, max: 50, message: '标题长度在2-50个字符', trigger: 'blur' }
  ],
  category_id: [
    { required: true, message: '请选择商品分类', trigger: 'change' }
  ],
  price: [
    { required: true, message: '请输入商品价格', trigger: 'blur' },
    { type: 'number', min: 0.01, message: '价格必须大于0', trigger: 'blur' }
  ],
  cover_img: [
    { required: true, message: '请上传商品图片', trigger: 'change' }
  ],
  description: [
    { required: true, message: '请输入商品描述', trigger: 'blur' },
    { min: 10, max: 500, message: '描述在10-500个字符', trigger: 'blur' }
  ]
})

// 触发文件选择
const triggerFileInput = () => {
  fileInput.value.click()
}

// 处理文件选择
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    handleBeforeUpload(file)
  }
}

// 图片上传
const uploadImage = async (file) => {
  const uploadFormData = new FormData()
  uploadFormData.append('file', file)

  try {
    const response = await fetch(uploadUrl, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      body: uploadFormData
    })
    const res = await response.json()
    // 后端返回 { code: 200, data: { url: '图片地址' } }
    if (res.code === 200) {
      formData.cover_img = res.data.url
      alert('图片上传成功')
    } else {
      alert(res.msg || '上传失败')
    }
  } catch (error) {
    console.error('图片上传失败:', error)
    alert('图片上传失败，请重试')
  }
}

// 图片上传前的验证
const handleBeforeUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    alert('只能上传图片文件')
    return false
  }
  if (!isLt2M) {
    alert('图片大小不能超过 2MB')
    return false
  }
  
  // 验证通过，上传图片
  uploadImage(file)
  return true
}

// 图片上传成功
const handleUploadSuccess = (response) => {
  // 后端返回 { code: 200, data: { url: '图片地址' } }
  if (response.code === 200) {
    formData.cover_img = response.data.url
    alert('图片上传成功')
  } else {
    alert(response.msg || '上传失败')
  }
}

// 图片上传失败
const handleUploadError = () => {
  alert('图片上传失败，请重试')
}

// 提交表单
const handleSubmit = async () => {
  // 自定义表单验证
  if (!formData.title.trim()) {
    alert('请输入商品标题')
    return
  }
  if (formData.title.length < 2 || formData.title.length > 50) {
    alert('标题长度在2-50个字符')
    return
  }
  if (!formData.category_id) {
    alert('请选择商品分类')
    return
  }
  if (!formData.price || formData.price < 0.01) {
    alert('请输入有效的商品价格')
    return
  }
  if (!formData.cover_img) {
    alert('请上传商品图片')
    return
  }
  if (!formData.description.trim()) {
    alert('请输入商品描述')
    return
  }
  if (formData.description.length < 10 || formData.description.length > 500) {
    alert('描述在10-500个字符')
    return
  }
  
  try {
    loading.value = true
    
    // 字段映射：前端字段名 → 后端期望的字段名
    const submitData = {
      name: formData.title,
      category_id: parseInt(formData.category_id),
      price: parseFloat(formData.price),
      image: formData.cover_img,
      description: formData.description
    }
    
    let res
    if (editId.value) {
      // 编辑商品
      res = await productApi.updateProduct(editId.value, submitData)
    } else {
      // 发布新商品
      res = await productApi.publishProduct(submitData)
    }
    
    if (res.code === 200) {
        alert(editId.value ? '商品更新成功' : '商品发布成功')
        emit('submit', res.data)
        // 跳转到个人中心
        setTimeout(() => {
          router.push('/profile')
        }, 1000)
      } else {
        alert(res.msg || '操作失败')
      }
  } catch (error) {
    console.error('提交失败:', error)
    alert('提交失败，请重试')
  } finally {
    loading.value = false
  }
}

// 取消
const handleCancel = () => {
  emit('cancel')
}

// 如果是编辑模式，加载商品数据
const loadProductData = async (id) => {
  try {
    const res = await productApi.getProductDetail(id)
    if (res.code === 200) {
      // 转换字段名：驼峰命名转为下划线命名
      const data = res.data
      formData.title = data.title || ''
      formData.category_id = data.categoryId || ''
      formData.price = data.price ? parseFloat(data.price) : null
      formData.cover_img = data.coverImg || ''
      formData.description = data.description || ''
    }
  } catch (error) {
    console.error('加载商品数据失败:', error)
    alert('加载商品数据失败')
  }
}

// 编辑模式 ID
const editId = ref(null)

// 如果是编辑模式，加载数据
onMounted(() => {
  const id = route.query.id || props.editId
  if (id) {
    editId.value = Number(id)
    loadProductData(editId.value)
    // 更新页面标题
    document.title = '编辑商品'
  }
})
</script>

<style scoped>
/* 全局样式重置 */
.publish-product-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 30px 20px;
  background-color: #f5f7fa;
  min-height: 100vh;
}

/* 头部样式 */
.publish-header {
  text-align: center;
  margin-bottom: 30px;
  padding: 20px 0;
}

.publish-title {
  font-size: 28px;
  font-weight: 700;
  color: #333;
  margin-bottom: 10px;
}

.publish-subtitle {
  font-size: 16px;
  color: #666;
  margin: 0;
}

/* 违禁品提示样式 */
.prohibited-alert {
  display: flex;
  background-color: #fff2f0;
  border: 1px solid #ffccc7;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 30px;
  box-shadow: 0 2px 8px rgba(255, 102, 102, 0.1);
}

.alert-icon {
  margin-right: 16px;
  color: #f56c6c;
  font-size: 24px;
  display: flex;
  align-items: flex-start;
  padding-top: 4px;
}

.alert-content {
  flex: 1;
}

.alert-content h3 {
  margin: 0 0 12px 0;
  font-size: 18px;
  font-weight: 600;
  color: #f56c6c;
}

.alert-content p {
  margin: 8px 0;
  font-size: 14px;
  line-height: 1.6;
  color: #606266;
}

.prohibited-list {
  margin: 12px 0;
  padding-left: 20px;
}

.prohibited-list li {
  margin: 6px 0;
  font-size: 14px;
  line-height: 1.6;
  color: #606266;
}

.reminder {
  margin-top: 12px;
  font-weight: 500;
  color: #f56c6c !important;
}

/* 表单容器 */
.publish-form-wrapper {
  background: #ffffff;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

/* 表单样式 */
.publish-form {
  width: 100%;
}

/* 表单元素通用样式 */
.form-input,
.form-select,
.form-textarea {
  width: 100%;
  border-radius: 8px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
  transition: all 0.3s ease !important;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2) !important;
  border-color: #409eff !important;
}

/* 价格输入样式 */
.price-input-wrapper {
  display: flex;
  align-items: center;
}

.form-input-number {
  width: 250px;
  border-radius: 8px !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}

.price-unit {
  margin-left: 16px;
  font-size: 18px;
  color: #909399;
  font-weight: 500;
}

/* 图片上传样式 */
.upload-section {
  margin-top: 8px;
}

.cover-uploader {
  border: none !important;
  border-radius: 12px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  width: 200px;
  height: 200px;
  transition: all 0.3s ease;
  background: #fafafa;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  border: 2px dashed #d9d9d9;
  border-radius: 12px;
  background: #fafafa;
  transition: all 0.3s ease;
}

.cover-uploader:hover .upload-placeholder {
  border-color: #409eff;
  background-color: #ecf5ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(64, 158, 255, 0.15);
}

.upload-icon {
  font-size: 40px;
  color: #8c939d;
  margin-bottom: 12px;
}

.upload-text {
  font-size: 16px;
  color: #606266;
  margin: 0 0 8px 0;
  font-weight: 500;
}

.upload-hint {
  font-size: 12px;
  color: #909399;
  margin: 0;
  text-align: center;
  line-height: 1.4;
}

/* 已上传图片样式 */
.uploaded-img-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.cover-img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  border-radius: 12px;
}

.img-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
  color: white;
}

.uploaded-img-wrapper:hover .img-overlay {
  opacity: 1;
}

.overlay-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.overlay-text {
  font-size: 14px;
  font-weight: 500;
}

/* 表单操作按钮 */
.form-actions {
  margin-top: 40px;
  display: flex;
  justify-content: center;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
}

.action-buttons {
  display: flex;
  gap: 16px;
}

.cancel-btn,
.submit-btn {
  min-width: 120px;
  height: 48px;
  border-radius: 24px !important;
  font-size: 16px !important;
  font-weight: 500 !important;
  transition: all 0.3s ease !important;
}

.submit-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  border: none !important;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
}

.submit-btn:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4) !important;
}

.cancel-btn {
  background-color: #f0f2f5 !important;
  color: #606266 !important;
  border: none !important;
}

.cancel-btn:hover {
  background-color: #e4e7ed !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .publish-product-container {
    padding: 20px 16px;
  }

  .publish-title {
    font-size: 24px;
  }

  .publish-subtitle {
    font-size: 14px;
  }

  .prohibited-alert {
    flex-direction: column;
    padding: 16px;
  }

  .alert-icon {
    margin-right: 0;
    margin-bottom: 12px;
  }

  .publish-form-wrapper {
    padding: 20px;
  }

  .publish-form {
    /* Bootstrap uses grid system for form labels, so label-width is not needed */
  }

  .cover-uploader {
    width: 160px;
    height: 160px;
  }

  .form-input-number {
    width: 200px;
  }

  .action-buttons {
    flex-direction: column;
    width: 100%;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .publish-form {
    /* Bootstrap uses grid system for form labels, so Element Plus properties are not needed */
  }

  .cover-uploader {
    width: 140px;
    height: 140px;
  }

  .price-input-wrapper {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .form-input-number {
    width: 100%;
  }

  .price-unit {
    margin-left: 0;
  }
}
</style>