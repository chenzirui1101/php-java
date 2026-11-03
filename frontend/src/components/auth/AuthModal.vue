<template>
  <!-- 登录注册模态框 - 仅在不直接显示用户协议时显示 -->
  <div v-if="!showTermsDirectly" class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content rounded shadow-lg">
        <!-- 头部 -->
        <div class="modal-header border-bottom">
          <h5 class="modal-title w-100 text-center font-weight-bold">潮青行</h5>
          <button type="button" class="close" @click="emit('close')">
            <span>&times;</span>
          </button>
        </div>

        <!-- 登录/注册切换 -->
        <div class="border-bottom py-2">
          <div class="container">
            <div class="btn-group w-100" role="group">
              <button
                type="button"
                class="btn flex-1 py-2"
                :class="activeTab === 'login' ? 'btn-orange' : 'btn-outline-secondary'"
                @click="activeTab = 'login'"
              >
                <i class="fa fa-sign-in mr-1"></i>登录
              </button>
              <button
                type="button"
                class="btn flex-1 py-2"
                :class="activeTab === 'register' ? 'btn-orange' : 'btn-outline-secondary'"
                @click="activeTab = 'register'"
              >
                <i class="fa fa-user-plus mr-1"></i>注册
              </button>
            </div>
          </div>
        </div>

        <!-- 内容区域 -->
        <div class="modal-body px-4 py-4" style="max-height: 70vh; overflow-y: auto;">
          <!-- 登录表单 -->
          <form v-if="activeTab === 'login'" @submit.prevent="handleLogin">
            <div class="form-group mb-3">
              <label for="loginUsername" class="form-label font-medium text-sm mb-1">用户名或学号</label>
              <input
                id="loginUsername"
                v-model="loginForm.username"
                type="text"
                class="form-control rounded-lg"
                placeholder="请输入用户名或学号"
                required
              />
            </div>

            <div class="form-group mb-3">
              <label for="loginPassword" class="form-label font-medium text-sm mb-1">密码</label>
              <input
                id="loginPassword"
                v-model="loginForm.password"
                type="password"
                class="form-control rounded-lg"
                placeholder="请输入密码"
                required
              />
            </div>

            <!-- hCaptcha 人机验证 -->
            <div class="form-group mb-3">
              <label class="form-label font-medium text-sm mb-1">人机验证</label>
              <div class="h-captcha" data-sitekey="b3704426-06e0-4258-9a02-e0ee59be966a" style="transform: scale(0.9); transform-origin: left;"></div>
              <div v-if="hcaptchaVerified" class="text-success text-sm mt-1">
                <i class="fa fa-check-circle mr-1"></i>验证通过
              </div>
            </div>

            <div class="form-group mb-3">
              <div class="form-check">
                <input
                  id="loginAgreeTerms"
                  v-model="loginForm.agreeTerms"
                  type="checkbox"
                  class="form-check-input"
                  required
                />
                <label class="form-check-label text-sm" for="loginAgreeTerms">
                  我已阅读并同意 <button type="button" class="text-orange bg-transparent border-0 p-0" @click="showTermsModal = true">《用户协议》</button>
                </label>
              </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
              <button
                type="submit"
                class="btn btn-orange py-3 font-medium"
                :disabled="loginLoading || !loginForm.agreeTerms || !hcaptchaVerified"
                style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); border: none; border-radius: 25px; min-width: 200px; box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3); transition: all 0.3s ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 157, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 157, 0.3)';"
              >
                <span v-if="loginLoading"><i class="fa fa-spinner fa-spin mr-1"></i>登录中...</span>
                <span v-else><i class="fa fa-sign-in mr-1"></i>立即登录</span>
              </button>
            </div>
          </form>

          <!-- 注册表单 -->
          <form v-else-if="activeTab === 'register'" @submit.prevent="handleRegister">
            <!-- 注册方式切换 -->
            <div class="mb-3">
              <div class="btn-group w-100" role="group">
                <button
                  type="button"
                  class="btn flex-1 py-2"
                  :class="registerMode === 'normal' ? 'btn-orange' : 'btn-outline-secondary'"
                  @click="registerMode = 'normal'"
                >
                  <i class="fa fa-user mr-1"></i>普通注册
                </button>
                <button
                  type="button"
                  class="btn flex-1 py-2"
                  :class="registerMode === 'student' ? 'btn-orange' : 'btn-outline-secondary'"
                  @click="registerMode = 'student'"
                >
                  <i class="fa fa-graduation-cap mr-1"></i>学号注册
                </button>
              </div>
            </div>

            <div class="form-group mb-3">
              <label for="registerUsername" class="form-label font-medium text-sm mb-1">用户名</label>
              <input
                id="registerUsername"
                v-model="registerForm.username"
                type="text"
                class="form-control rounded-lg"
                placeholder="请输入用户名（3-20位）"
                required
              />
            </div>

            <div class="form-group mb-3">
              <label for="registerName" class="form-label font-medium text-sm mb-1">姓名</label>
              <input
                id="registerName"
                v-model="registerForm.name"
                type="text"
                class="form-control rounded-lg"
                placeholder="请输入姓名"
                required
              />
            </div>

            <div class="form-group mb-3">
              <label for="registerStudentId" v-if="registerMode === 'student'" class="form-label font-medium text-sm mb-1">学号</label>
              <label for="registerEmail" v-else class="form-label font-medium text-sm mb-1">邮箱</label>
              <input
                id="registerStudentId"
                v-if="registerMode === 'student'"
                v-model="registerForm.studentId"
                type="text"
                class="form-control rounded-lg"
                placeholder="请输入学号"
                required
              />
              <input
                id="registerEmail"
                v-else
                v-model="registerForm.email"
                type="email"
                class="form-control rounded-lg"
                placeholder="请输入邮箱"
                required
              />
            </div>

            <div class="form-group mb-3">
              <label for="registerPassword" class="form-label font-medium text-sm mb-1">密码</label>
              <input
                id="registerPassword"
                v-model="registerForm.password"
                type="password"
                class="form-control rounded-lg"
                placeholder="请输入密码（至少6位）"
                required
                minlength="6"
              />
            </div>

            <div class="form-group mb-3">
              <label for="registerConfirmPassword" class="form-label font-medium text-sm mb-1">确认密码</label>
              <input
                id="registerConfirmPassword"
                v-model="registerForm.confirmPassword"
                type="password"
                class="form-control rounded-lg"
                placeholder="请确认密码"
                required
                minlength="6"
              />
              <div v-if="registerForm.password !== registerForm.confirmPassword && registerForm.confirmPassword" class="text-danger mt-1 text-sm">
                <i class="fa fa-exclamation-circle mr-1"></i>两次输入的密码不一致
              </div>
            </div>

            <div class="form-group mb-3">
              <div class="form-check">
                <input
                  id="registerAgreeTerms"
                  v-model="registerForm.agreeTerms"
                  type="checkbox"
                  class="form-check-input"
                  required
                />
                <label class="form-check-label text-sm" for="registerAgreeTerms">
                  我已阅读并同意 <button type="button" class="text-orange bg-transparent border-0 p-0" @click="showTermsModal = true">《用户协议》</button>
                </label>
              </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
              <button
                type="submit"
                class="btn btn-orange py-3 font-medium"
                :disabled="registerLoading || !registerForm.agreeTerms || registerForm.password !== registerForm.confirmPassword"
                style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); border: none; border-radius: 25px; min-width: 200px; box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3); transition: all 0.3 ease;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 107, 157, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 107, 157, 0.3)';"
              >
                <span v-if="registerLoading"><i class="fa fa-spinner fa-spin mr-1"></i>注册中...</span>
                <span v-else><i class="fa fa-user-plus mr-1"></i>立即注册</span>
              </button>
            </div>
          </form>
        </div>

        <!-- 底部 -->
        <div class="modal-footer border-top py-3 text-center bg-light">
          <p class="text-sm text-gray-500 w-100 mb-0">© 2026 潮青行</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 用户协议模态框 -->
  <div v-if="showTermsModal || showTermsDirectly" class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5); z-index: 2000;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content rounded shadow-lg">
        <div class="modal-header border-bottom">
          <h5 class="modal-title">用户协议及免责声明</h5>
          <button type="button" class="close" @click="handleTermsClose">
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
            <p>用户点击"注册"或"登录"按钮即视为已阅读并同意本协议全部条款。</p>
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-orange" @click="handleTermsClose">同意</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/modules/user'
import request from '@/utils/request'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  showTermsDirectly: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'success'])
const router = useRouter()
const userStore = useUserStore()

const activeTab = ref('login')
const registerMode = ref('normal')
const showTermsModal = ref(false)
const loginLoading = ref(false)
const registerLoading = ref(false)
const hcaptchaVerified = ref(false)
const hcaptchaToken = ref('')

// Load hCaptcha script
onMounted(() => {
  if (!document.querySelector('script[src*="hcaptcha.com/1/api.js"]')) {
    const script = document.createElement('script')
    script.src = 'https://js.hcaptcha.com/1/api.js?render=explicit'
    script.async = true
    script.defer = true
    script.onload = () => {
      nextTick(() => renderHCaptcha())
    }
    document.head.appendChild(script)
  } else {
    nextTick(() => renderHCaptcha())
  }
})

watch(() => [activeTab.value], () => {
  hcaptchaVerified.value = false
  hcaptchaToken.value = ''
  nextTick(() => renderHCaptcha())
})

function renderHCaptcha() {
  const container = document.querySelector('.h-captcha')
  if (!container || typeof window.hcaptcha === 'undefined') return
  
  // Clear previous widget
  container.innerHTML = ''
  
  try {
    window.hcaptcha.render(container, {
      sitekey: 'b3704426-06e0-4258-9a02-e0ee59be966a',
      callback: (token) => {
        hcaptchaToken.value = token
        hcaptchaVerified.value = true
      },
      'expired-callback': () => {
        hcaptchaVerified.value = false
        hcaptchaToken.value = ''
      }
    })
  } catch (e) {
    console.warn('hCaptcha render error:', e)
  }
}

watch(() => props.showTermsDirectly, (newValue) => {
  if (newValue) showTermsModal.value = true
}, { immediate: true })

const handleTermsClose = () => {
  showTermsModal.value = false
  if (props.showTermsDirectly) emit('close')
}

const loginForm = reactive({
  username: '',
  password: '',
  agreeTerms: false
})

const registerForm = reactive({
  username: '',
  name: '',
  email: '',
  studentId: '',
  password: '',
  confirmPassword: '',
  agreeTerms: false
})

const handleLogin = async () => {
  if (!loginForm.agreeTerms) return
  if (!hcaptchaVerified.value || !hcaptchaToken.value) {
    alert('请完成人机验证')
    return
  }

  try {
    loginLoading.value = true

    const loginData = {
      username: loginForm.username,
      password: loginForm.password,
      hcaptcha_token: hcaptchaToken.value
    }

    const res = await userStore.login(loginData)
    emit('success')
    emit('close')
  } catch (err) {
    console.error('登录失败:', err)
    let errorMsg = '登录失败，请重试'

    if (err.response?.data?.msg) {
      errorMsg = err.response.data.msg
    } else if (err.msg) {
      errorMsg = err.msg
    } else if (err.message) {
      errorMsg = err.message
    }

    alert(errorMsg)

    // Reset hCaptcha on failure
    hcaptchaVerified.value = false
    hcaptchaToken.value = ''
    nextTick(() => renderHCaptcha())
  } finally {
    loginLoading.value = false
  }
}

const handleRegister = async () => {
  if (!registerForm.agreeTerms) return
  if (registerForm.password !== registerForm.confirmPassword) return

  try {
    registerLoading.value = true

    const registerData = {
      username: registerForm.username,
      name: registerForm.name,
      password: registerForm.password
    }

    if (registerMode.value === 'student') {
      registerData.studentId = registerForm.studentId
    } else {
      registerData.email = registerForm.email
    }

    const res = await request({
      url: '/api/auth/register',
      method: 'post',
      data: registerData
    })

    if (res.code === 200) {
      activeTab.value = 'login'
      loginForm.username = registerForm.username
      loginForm.password = registerForm.password
      alert('注册成功，请登录')
      nextTick(() => renderHCaptcha())
    }
  } catch (err) {
    console.error('注册失败:', err)
    if (err.response?.data?.msg) alert(err.response.data.msg)
    else if (err.msg) alert(err.msg)
    else alert('注册失败，请重试')
  } finally {
    registerLoading.value = false
  }
}
</script>