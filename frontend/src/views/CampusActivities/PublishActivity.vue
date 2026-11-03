<template>
  <div class="container mx-auto px-4 py-8">
    <!-- 页面标题 -->
    <div class="d-flex justify-content-between align-items-center mb-6">
      <h1 class="text-3xl font-bold text-orange-gradient">
        <i class="fa fa-plus-circle mr-2 text-orange-gradient"></i> 发布活动
      </h1>
      <button 
        class="btn btn-outline-primary"
        @click="router.push('/campus-activities')"
      >
        <i class="fa fa-arrow-left mr-1"></i> 返回
      </button>
    </div>
    
    <!-- 发布活动表单 -->
    <div class="card shadow-sm p-8" style="padding-bottom: 50px;">
      <form @submit.prevent="handleSubmit" class="w-100" style="padding-bottom: 50px;">
        <!-- 活动海报上传 -->
        <div class="mb-6 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label class="form-label font-bold mb-2">活动海报</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <div class="border border-dashed border-gray-300 rounded-lg p-6 text-center w-100" style="max-width: 90%;">
              <input 
                type="file" 
                ref="fileInput" 
                accept="image/*" 
                class="d-none"
                @change="handleFileUpload"
              >
              <button 
                type="button" 
                class="btn btn-outline-primary mb-4" 
                style="margin-top: 15px;"
                @click="$refs.fileInput.click()"
              >
                <i class="fa fa-upload mr-1"></i> 选择图片
              </button>
              <div v-if="posterUrl" class="mt-3 mb-3 overflow-hidden" style="max-width: 100%; max-height: 256px;">
                <img :src="posterUrl" alt="活动海报" class="w-full h-auto object-contain rounded">
              </div>
              <p class="text-sm text-gray-500 mt-2">请上传活动海报图片，建议尺寸为 600x400</p>
            </div>
          </div>
        </div>
        
        <!-- 活动标题 -->
        <div class="mb-6 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label for="title" class="form-label font-bold mb-2">活动标题</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <input 
              type="text" 
              id="title" 
              class="form-control w-100" 
              v-model="formData.title"
              placeholder="请输入活动标题"
              required
              style="max-width: 90%;"
            >
          </div>
        </div>
        
        <!-- 活动开始时间 -->
        <div class="mb-6 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label for="startTime" class="form-label font-bold mb-2">开始时间</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <input 
              type="datetime-local" 
              id="startTime" 
              class="form-control w-100" 
              v-model="formData.start_time"
              required
              style="max-width: 90%;"
            >
          </div>
        </div>
        
        <!-- 活动结束时间 -->
        <div class="mb-6 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label for="endTime" class="form-label font-bold mb-2">结束时间</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <input 
              type="datetime-local" 
              id="endTime" 
              class="form-control w-100" 
              v-model="formData.end_time"
              required
              style="max-width: 90%;"
            >
          </div>
        </div>
        

        
        <!-- 活动描述 -->
        <div class="mb-6 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label for="description" class="form-label font-bold mb-2">活动描述</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <textarea 
              id="description" 
              class="form-control w-100" 
              v-model="formData.description"
              placeholder="请输入活动描述"
              rows="4"
              style="max-width: 90%;"
            ></textarea>
          </div>
        </div>
        
        <!-- 活动分类 -->
        <div class="mb-8 w-100 d-flex flex-column items-center">
          <div class="w-100 d-flex justify-content-center">
            <div style="width: 90%; text-align: left;">
              <label for="category" class="form-label font-bold mb-2">活动分类</label>
            </div>
          </div>
          <div class="w-100 d-flex justify-content-center">
            <select 
              id="category" 
              class="form-control w-100" 
              v-model="formData.category"
              required
              style="max-width: 90%;"
            >
              <option value="">请选择分类</option>
              <option value="1">学术活动</option>
              <option value="2">文体活动</option>
              <option value="3">志愿服务</option>
              <option value="4">社团活动</option>
              <option value="5">其他活动</option>
            </select>
          </div>
        </div>
        
        <!-- 提交按钮 -->
        <div class="d-flex justify-content-center align-items-center my-6" style="margin-top: 30px;">
          <button 
            type="submit" 
            class="btn btn-primary px-10 py-3"
            :disabled="loading"
          >
            <span v-if="loading">
              <i class="fa fa-spinner fa-spin mr-2"></i> 提交中...
            </span>
            <span v-else>
              <i class="fa fa-paper-plane mr-2"></i> 提交审核
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { activityApi } from '@/api'

const router = useRouter()
const loading = ref(false)
const posterUrl = ref('')
const fileInput = ref(null)

const formData = reactive({
  title: '',
  start_time: '',
  end_time: '',
  description: '',
  category: ''
})

// 处理文件上传
const handleFileUpload = (e) => {
  const file = e.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      posterUrl.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// 处理表单提交
const handleSubmit = async () => {
  try {
    loading.value = true
    
    // 表单验证
    if (!formData.title || !formData.start_time || !formData.end_time || !formData.category) {
      alert('请填写所有必填字段')
      return
    }
    
    // 验证结束时间是否在开始时间之后
    if (new Date(formData.end_time) <= new Date(formData.start_time)) {
      alert('结束时间必须在开始时间之后')
      return
    }
    
    // 准备提交数据
    const submitData = {
      title: formData.title,
      poster: posterUrl.value,
      start_time: formData.start_time,
      end_time: formData.end_time,
      description: formData.description,
      category_id: formData.category
    }
    
    // 提交到后端
    const response = await activityApi.publishActivity(submitData)
    
    // 显示成功提示
    alert(response.data.msg || '发布成功，请等待管理员审核结果')
    
    // 重置表单
    formData.title = ''
    formData.start_time = ''
    formData.end_time = ''
    formData.description = ''
    formData.category = ''
    posterUrl.value = ''
    
    // 跳转到活动列表页
    router.push('/campus-activities')
  } catch (error) {
    console.error('提交失败:', error)
    // 显示具体的错误信息
    if (error.response && error.response.data && error.response.data.errors) {
      const errors = error.response.data.errors
      let errorMessage = '提交失败：\n'
      for (const field in errors) {
        errorMessage += errors[field].join('\n') + '\n'
      }
      alert(errorMessage)
    } else {
      alert('提交失败，请稍后重试')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* 自定义样式 */
.container {
  max-width: 800px;
}

.text-text-primary {
  color: var(--color-primary);
}

/* 响应式调整 */
@media (max-width: 768px) {
  h1 {
    font-size: 2rem;
  }
}

/* 按钮样式 */
.btn-primary {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  border: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
  background: linear-gradient(135deg, #FF528B, #FFC107);
}

.btn-outline-primary {
  border-color: #FF6B9D;
  color: #FF6B9D;
  transition: all 0.3s ease;
}

.btn-outline-primary:hover {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  border-color: #FF6B9D;
  color: white;
}

/* 渐变橙颜色 */
.text-orange-gradient {
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  display: inline-block;
}
</style>