<template>
  <div class="container mx-auto px-4 py-8">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow">
          <div class="card-header bg-white">
            <h3 class="mb-0">发布失物招领信息</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="handleSubmit">
              <div class="form-group mb-4">
                <label class="form-label">类型 <span class="text-danger">*</span></label>
                <select 
                  class="form-control" 
                  v-model="form.type"
                  required
                >
                  <option value="">请选择类型</option>
                  <option value="lost">寻物启事</option>
                  <option value="found">失物招领</option>
                </select>
              </div>
              
              <div class="form-group mb-4">
                <label class="form-label">标题 <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.title"
                  placeholder="请输入标题"
                  required
                  maxlength="255"
                >
              </div>
              
              <div class="form-group mb-4">
                <label class="form-label">地点 <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.location"
                  placeholder="请输入丢失或拾到地点"
                  required
                  maxlength="255"
                >
              </div>
              
              <div class="form-group mb-4">
                <label class="form-label">状态 <span class="text-danger">*</span></label>
                <select 
                  class="form-control" 
                  v-model="form.status"
                  required
                >
                  <option value="">请选择状态</option>
                  <option value="looking">寻找中</option>
                  <option value="found">已找到</option>
                </select>
              </div>
              
              <div class="form-group mb-4">
                <label class="form-label">详细描述 <span class="text-danger">*</span></label>
                <textarea 
                  class="form-control" 
                  v-model="form.description"
                  placeholder="请详细描述物品特征、丢失/拾到时间等信息"
                  rows="5"
                  required
                ></textarea>
              </div>
              
              <div class="form-group mb-4">
                <label class="form-label">图片</label>
                <div class="custom-file">
                  <input 
                    type="file" 
                    class="custom-file-input" 
                    id="coverImg"
                    accept="image/*"
                    @change="handleImageChange"
                  >
                  <label class="custom-file-label" for="coverImg">
                    {{ selectedFileName || '选择图片' }}
                  </label>
                </div>
                <small class="form-text text-muted">支持上传JPG、PNG格式图片，大小不超过2MB</small>
                <div v-if="imagePreview" class="mt-3">
                  <img :src="imagePreview" alt="预览" class="img-thumbnail" style="max-width: 300px;">
                </div>
              </div>
              
              <div class="form-group mb-4">
                <div class="form-check">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    id="agreeTerms"
                    v-model="form.agreeTerms"
                    required
                  >
                  <label class="form-check-label" for="agreeTerms">
                    我已阅读并同意 <button type="button" class="text-primary bg-transparent border-0 p-0" @click="showTermsModal = true">《用户协议》</button>
                  </label>
                </div>
              </div>
              
              <div class="d-flex justify-content-between">
                <button 
                  type="button" 
                  class="btn btn-secondary"
                  @click="handleCancel"
                >
                  取消
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary"
                  :disabled="submitting || !form.agreeTerms"
                >
                  <span v-if="submitting"><i class="fa fa-spinner fa-spin mr-1"></i>提交中...</span>
                  <span v-else>提交</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 用户协议模态框 -->
    <div v-if="showTermsModal" class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5); z-index: 2000;">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content rounded shadow-lg">
          <div class="modal-header border-bottom">
            <h5 class="modal-title">用户协议及免责声明</h5>
            <button type="button" class="close" @click="showTermsModal = false">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body p-5">
            <div class="space-y-3 text-gray-700">
              <p>本站为个人毕业设计作品，仅用于技术展示与非商业运营。</p>
              
              <h5 class="font-weight-bold">一、用户行为规范</h5>
              <p>用户不得利用本平台发布任何违反中华人民共和国法律法规的信息，包括但不限于：管制物品、枪支弹药、毒品、诈骗信息、赌博网站导流、色情低俗内容、侵犯他人隐私信息等。</p>
              
              <h5 class="font-weight-bold">二、责任承担</h5>
              <p>用户在本平台发布的任何信息，其真实性、合法性均由用户自行承担全部责任。用户之间因浏览信息、沟通交易产生的任何纠纷（包括但不限于欺诈、不发货、货不对板、金钱损失等），均由交易双方自行解决，本站不承担任何保证责任及连带责任。</p>
              
              <h5 class="font-weight-bold">三、信息记录</h5>
              <p>用户同意，本站有权记录并保留用户的发布信息、IP地址及操作时间，并在接到有权机关正式协查通知时提供上述数据。</p>
              
              <h5 class="font-weight-bold">四、协议接受</h5>
              <p>用户点击"提交"按钮即视为已阅读并同意本协议全部条款。</p>
            </div>
          </div>
          <div class="modal-footer border-top">
            <button 
              type="button" 
              class="btn btn-primary"
              @click="showTermsModal = false"
            >
              同意
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import request from '@/utils/request'

const router = useRouter()

const submitting = ref(false)
const showTermsModal = ref(false)
const selectedFileName = ref('')
const imagePreview = ref('')
const selectedFile = ref(null)

const form = reactive({
  type: '',
  title: '',
  location: '',
  status: '',
  description: '',
  cover_img: '',
  agreeTerms: false
})

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    // 验证文件类型和大小
    const isImage = file.type.startsWith('image/')
    if (!isImage) {
      alert('请上传图片文件')
      return
    }
    const isLt2M = file.size / 1024 / 1024 < 2
    if (!isLt2M) {
      alert('上传图片大小不能超过 2MB!')
      return
    }
    
    selectedFile.value = file
    selectedFileName.value = file.name
    
    // 本地预览
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const uploadImage = async () => {
  if (!selectedFile.value) return null
  
  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)
    
    const res = await request({
      url: '/api/upload/image',
      method: 'post',
      data: formData,
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (res.code === 200) {
      return res.data.url
    } else {
      throw new Error(res.msg || '上传失败')
    }
  } catch (error) {
    console.error('上传图片失败:', error)
    throw error
  }
}

const handleSubmit = async () => {
  if (!form.agreeTerms) return
  
  try {
    submitting.value = true
    
    // 上传图片
    let imageUrl = null
    if (selectedFile.value) {
      imageUrl = await uploadImage()
    }
    
    // 提交表单
    const res = await request({
      url: '/api/lostfound',
      method: 'post',
      data: {
        type: form.type,
        title: form.title,
        location: form.location,
        status: form.status,
        description: form.description,
        cover_img: imageUrl
      }
    })
    
    if (res.code === 200) {
      alert('发布成功，等待管理员审核')
      router.push('/lostfound')
    } else {
      alert(res.msg || '发布失败')
    }
  } catch (error) {
    console.error('发布失败:', error)
    if (error.response?.data?.msg) {
      alert(error.response.data.msg)
    } else if (error.message) {
      alert(error.message)
    } else {
      alert('发布失败，请稍后重试')
    }
  } finally {
    submitting.value = false
  }
}

const handleCancel = () => {
  router.push('/lostfound')
}
</script>

<style scoped>
.card {
  border: none;
  border-radius: 8px;
}

.card-header {
  border-bottom: 1px solid #e9ecef;
  padding: 1.5rem;
}

.card-body {
  padding: 2rem;
}

.form-label {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.form-control {
  border-radius: 4px;
  padding: 0.75rem;
  border: 1px solid #ced4da;
}

.form-control:focus {
  border-color: #FF8A5C;
  box-shadow: 0 0 0 0.2rem rgba(255, 138, 92, 0.25);
}

.btn-primary {
  background-color: #FF8A5C;
  border-color: #FF8A5C;
}

.btn-primary:hover {
  background-color: #ff7a4c;
  border-color: #ff7a4c;
}

.btn-primary:disabled {
  background-color: #ffb08c;
  border-color: #ffb08c;
}

.custom-file-input:focus ~ .custom-file-label {
  border-color: #FF8A5C;
  box-shadow: 0 0 0 0.2rem rgba(255, 138, 92, 0.25);
}

.custom-file-label::after {
  background-color: #FF8A5C;
  color: white;
}
</style>
