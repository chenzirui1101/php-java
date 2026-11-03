<template>
  <form ref="formRef" class="publish-form">
    <div class="form-group">
      <label for="title" class="form-label">商品标题</label>
      <input
        type="text"
        id="title"
        v-model="formData.title"
        class="form-control"
        placeholder="请输入商品标题（如：99新小米14手机）"
        maxlength="50"
        @blur="validateField('title')"
      >
      <div class="text-muted text-right mt-1">{{ formData.title.length }}/50</div>
      <div v-if="errors.title" class="text-danger small mt-1">{{ errors.title }}</div>
    </div>

    <div class="form-group">
      <label for="categoryId" class="form-label">商品分类</label>
      <select
        id="categoryId"
        v-model="formData.categoryId"
        class="form-control"
        @change="validateField('categoryId')"
      >
        <option value="" disabled>请选择商品分类</option>
        <option
          v-for="item in categoryList"
          :key="item.id"
          :value="item.id"
        >{{ item.name }}</option>
      </select>
      <div v-if="errors.categoryId" class="text-danger small mt-1">{{ errors.categoryId }}</div>
    </div>

    <div class="form-group">
      <label for="price" class="form-label">商品价格</label>
      <div class="input-group">
        <span class="input-group-text">¥</span>
        <input
          type="number"
          id="price"
          v-model="formData.price"
          class="form-control"
          placeholder="请输入商品价格（元）"
          min="0.01"
          step="0.01"
          @blur="validateField('price')"
        >
      </div>
      <div v-if="errors.price" class="text-danger small mt-1">{{ errors.price }}</div>
    </div>

    <div class="form-group">
      <label for="coverImg" class="form-label">商品图片</label>
      <div
        class="avatar-uploader"
        @click="fileInputRef?.click()"
      >
        <img
          v-if="formData.coverImg"
          :src="formData.coverImg"
          class="avatar"
        >
        <div v-else class="avatar-uploader-icon">
          <i class="fa fa-plus"></i>
        </div>
      </div>
      <input
        ref="fileInputRef"
        type="file"
        accept="image/jpeg, image/png"
        class="d-none"
        @change="handleFileChange"
      >
      <div v-if="errors.coverImg" class="text-danger small mt-1">{{ errors.coverImg }}</div>
    </div>

    <div class="form-group">
      <label for="description" class="form-label">商品描述</label>
      <textarea
        id="description"
        v-model="formData.description"
        class="form-control"
        placeholder="请详细描述商品信息（新旧程度、功能、配件等）"
        rows="6"
        maxlength="500"
        @blur="validateField('description')"
      ></textarea>
      <div class="text-muted text-right mt-1">{{ formData.description.length }}/500</div>
      <div v-if="errors.description" class="text-danger small mt-1">{{ errors.description }}</div>
    </div>

    <div class="form-group text-center">
      <button
        type="button"
        class="btn btn-primary me-2"
        @click="handleSubmit"
        :disabled="loading"
      >
        <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
        {{ isEdit ? '更新商品' : '发布商品' }}
      </button>
      <button
        type="button"
        class="btn btn-secondary"
        @click="handleReset"
      >
        重置
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'

// 接收参数
const props = defineProps({
  // 编辑模式：传入商品数据
  editData: {
    type: Object,
    default: null
  }
})

// 自定义事件
const emit = defineEmits(['submit'])

// 加载状态
const loading = ref(false)
// 表单Ref
const formRef = ref(null)
// 文件输入Ref
const fileInputRef = ref(null)
// 是否编辑模式
const isEdit = ref(!!props.editData)

// 分类列表（模拟，后续对接后端）
const categoryList = ref([
  { id: 1, name: '数码产品' },
  { id: 2, name: '生活用品' },
  { id: 3, name: '学习资料' },
  { id: 4, name: '服饰鞋包' },
  { id: 5, name: '其他' }
])

// 表单数据
const formData = reactive({
  title: '',
  categoryId: '',
  price: '',
  coverImg: '',
  description: '',
  id: '' // 编辑时的商品ID
})

// 错误信息
const errors = reactive({
  title: '',
  categoryId: '',
  price: '',
  coverImg: '',
  description: ''
})

// 初始化编辑数据
if (isEdit.value) {
  Object.assign(formData, {
    ...props.editData,
    categoryId: props.editData.categoryId || ''
  })
}

// 表单校验
const validateForm = () => {
  let isValid = true
  
  // 重置错误信息
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  // 标题校验
  if (!formData.title.trim()) {
    errors.title = '请输入商品标题'
    isValid = false
  }
  
  // 分类校验
  if (!formData.categoryId) {
    errors.categoryId = '请选择商品分类'
    isValid = false
  }
  
  // 价格校验
  if (!formData.price || Number(formData.price) < 0.01) {
    errors.price = '请输入有效商品价格'
    isValid = false
  }
  
  // 图片校验
  if (!formData.coverImg) {
    errors.coverImg = '请上传商品图片'
    isValid = false
  }
  
  // 描述校验
  if (!formData.description.trim()) {
    errors.description = '请输入商品描述'
    isValid = false
  }
  
  return isValid
}

// 单个字段校验
const validateField = (field) => {
  errors[field] = ''
  
  switch (field) {
    case 'title':
      if (!formData.title.trim()) {
        errors.title = '请输入商品标题'
      }
      break
    case 'categoryId':
      if (!formData.categoryId) {
        errors.categoryId = '请选择商品分类'
      }
      break
    case 'price':
      if (!formData.price || Number(formData.price) < 0.01) {
        errors.price = '请输入有效商品价格'
      }
      break
    case 'description':
      if (!formData.description.trim()) {
        errors.description = '请输入商品描述'
      }
      break
  }
}

// 图片上传处理
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // 验证文件类型
  const isJPG = file.type === 'image/jpeg' || file.type === 'image/png'
  if (!isJPG) {
    alert('上传图片只能是 JPG/PNG 格式！')
    return
  }
  
  // 验证文件大小
  const isLt2M = file.size / 1024 / 1024 < 2
  if (!isLt2M) {
    alert('上传图片大小不能超过 2MB！')
    return
  }
  
  // 预览图片
  const reader = new FileReader()
  reader.onload = (e) => {
    formData.coverImg = e.target.result
    validateField('coverImg')
  }
  reader.readAsDataURL(file)
  
  // 清空文件输入，以便下次选择同一文件时能触发change事件
  event.target.value = ''
}

// 提交表单
const handleSubmit = async () => {
  try {
    if (!validateForm()) {
      return
    }
    
    loading.value = true

    // 模拟接口请求（1秒后返回）
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    alert(isEdit.value ? '商品更新成功' : '商品发布成功')
    emit('submit', formData) // 通知父组件
  } catch (err) {
    alert('表单校验失败，请检查必填项')
  } finally {
    loading.value = false
  }
}

// 重置表单
const handleReset = () => {
  // 重置表单数据
  formData.title = ''
  formData.categoryId = ''
  formData.price = ''
  formData.coverImg = ''
  formData.description = ''
  
  // 重置错误信息
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}
</script>

<style scoped>
.publish-form {
  max-width: 800px;
  margin: 0 auto;
}
.avatar-uploader {
  border: 1px dashed #d9d9d9;
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 180px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
}
.avatar-uploader:hover {
  border-color: #0d6efd;
  background-color: #e9ecef;
}
.avatar-uploader-icon {
  font-size: 28px;
  color: #8c8c8c;
  width: 180px;
  height: 180px;
  text-align: center;
  line-height: 180px;
}
.avatar {
  width: 180px;
  height: 180px;
  display: block;
  object-fit: cover;
}
</style>