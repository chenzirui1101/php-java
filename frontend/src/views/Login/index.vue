<template>
  <div class="login-container d-flex justify-content-center align-items-center min-vh-100">
    <div class="login-card card shadow-lg p-4" style="width: 450px; border-radius: 10px;">
      <h2 class="login-title text-center mb-4">校园物品展示平台</h2>
      
      <!-- 标签页 -->
      <ul class="nav nav-tabs login-tabs mb-4" id="loginTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button 
            class="nav-link" 
            :class="{ active: activeTab === 'login' }"
            @click="activeTab = 'login'"
            type="button" 
            role="tab"
          >
            登录
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button 
            class="nav-link" 
            :class="{ active: activeTab === 'register' }"
            @click="activeTab = 'register'"
            type="button" 
            role="tab"
          >
            注册
          </button>
        </li>
      </ul>
      
      <!-- 登录表单 -->
      <div v-if="activeTab === 'login'" class="tab-pane fade show active">
        <form ref="loginFormRef" class="login-form">
          <div class="mb-3">
            <label for="loginUsername" class="form-label">用户名</label>
            <input
              id="loginUsername"
              v-model="loginForm.username"
              type="text"
              placeholder="请输入用户名"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-3">
            <label for="loginPassword" class="form-label">密码</label>
            <input
              id="loginPassword"
              v-model="loginForm.password"
              type="password"
              placeholder="请输入密码"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-3">
            <label for="loginCaptcha" class="form-label">验证码</label>
            <div class="row g-2">
              <div class="col-sm-8">
                <input
                  id="loginCaptcha"
                  v-model="loginForm.captcha"
                  :placeholder="captchaPlaceholder"
                  type="text"
                  class="form-control form-control-lg"
                >
              </div>
              <div class="col-sm-4">
                <div class="captcha-box d-flex align-items-center justify-content-center" @click="refreshCaptcha">
                  <div v-if="captchaText" class="captcha-text fs-5 fw-bold text-primary">
                    {{ captchaText }}
                  </div>
                  <img v-else-if="captchaImage" :src="captchaImage" class="captcha-img" />
                  <div v-else class="captcha-loading text-muted">
                    点击获取
                  </div>
                </div>
              </div>
            </div>
            <div class="captcha-hint text-muted text-end mt-1">点击验证码框刷新</div>
          </div>

          <div class="mb-4">
            <button
              type="button"
              class="login-btn btn btn-primary btn-lg w-100"
              @click="handleLogin"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              登录
            </button>
          </div>
        </form>
      </div>
      
      <!-- 注册表单 -->
      <div v-if="activeTab === 'register'" class="tab-pane fade show active">
        <form ref="registerFormRef" class="login-form">
          <div class="mb-3">
            <label for="registerUsername" class="form-label">用户名</label>
            <input
              id="registerUsername"
              v-model="registerForm.username"
              type="text"
              placeholder="请输入用户名（3-20位）"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-3">
            <label for="registerName" class="form-label">姓名</label>
            <input
              id="registerName"
              v-model="registerForm.name"
              type="text"
              placeholder="请输入姓名"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-3">
            <label for="registerEmail" class="form-label">邮箱</label>
            <input
              id="registerEmail"
              v-model="registerForm.email"
              type="email"
              placeholder="请输入邮箱"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-3">
            <label for="registerPassword" class="form-label">密码</label>
            <input
              id="registerPassword"
              v-model="registerForm.password"
              type="password"
              placeholder="请输入密码（至少6位）"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-4">
            <label for="registerConfirmPassword" class="form-label">确认密码</label>
            <input
              id="registerConfirmPassword"
              v-model="registerForm.confirmPassword"
              type="password"
              placeholder="请确认密码"
              class="form-control form-control-lg"
            >
          </div>

          <div class="mb-4">
            <button
              type="button"
              class="login-btn btn btn-primary btn-lg w-100"
              @click="handleRegister"
              :disabled="registerLoading"
            >
              <span v-if="registerLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              注册
            </button>
          </div>
        </form>
      </div>
      
      <!-- 用户协议及免责声明 -->
      <div class="user-agreement bg-light p-3 rounded mt-4">
        <h4 class="fs-6 fw-bold mb-2">用户协议及免责声明</h4>
        <p class="mb-2">本站为个人毕业设计作品，仅用于技术展示与非商业运营。</p>
        <h5 class="fs-6 fw-semibold mt-2 mb-1">一、用户行为规范</h5>
        <p class="mb-2">用户不得利用本平台发布任何违反中华人民共和国法律法规的信息，包括但不限于：管制物品、枪支弹药、毒品、诈骗信息、赌博网站导流、色情低俗内容、侵犯他人隐私信息等。</p>
        <h5 class="fs-6 fw-semibold mt-2 mb-1">二、责任承担</h5>
        <p class="mb-2">用户在本平台发布的任何信息，其真实性、合法性均由用户自行承担全部责任。用户之间因浏览信息、沟通交易产生的任何纠纷（包括但不限于欺诈、不发货、货不对板、金钱损失等），均由交易双方自行解决，本站不承担任何保证责任及连带责任。</p>
        <h5 class="fs-6 fw-semibold mt-2 mb-1">三、信息记录</h5>
        <p class="mb-2">用户同意，本站有权记录并保留用户的发布信息、IP地址及操作时间，并在接到有权机关正式协查通知时提供上述数据。</p>
        <h5 class="fs-6 fw-semibold mt-2 mb-1">四、协议接受</h5>
        <p class="mb-0">用户点击“注册”或“发布”按钮即视为已阅读并同意本协议全部条款。</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';

import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/modules/user';
import request from '@/utils/request';

const router = useRouter();
const userStore = useUserStore();

const captchaImage = ref('');
const captchaText = ref('');
const captchaKey = ref('');
const loading = ref(false);
const registerLoading = ref(false);
const loginFormRef = ref(null);
const registerFormRef = ref(null);
const activeTab = ref('login');

const loginForm = reactive({
  username: 'admin',
  password: '123456',
  captcha: ''
});

const registerForm = reactive({
  username: '',
  name: '',
  email: '',
  password: '',
  confirmPassword: ''
});

// 动态验证码占位符
const captchaPlaceholder = computed(() => {
  return captchaText.value ? '请输入计算结果' : '请输入验证码';
});

// 表单验证
const validateLoginForm = () => {
  if (!loginForm.username.trim()) {
    alert('请输入用户名');
    return false;
  }
  if (!loginForm.password) {
    alert('请输入密码');
    return false;
  }
  if (!loginForm.captcha.trim()) {
    alert('请输入验证码');
    return false;
  }
  return true;
};

const validateRegisterForm = () => {
  if (!registerForm.username.trim()) {
    alert('请输入用户名');
    return false;
  }
  if (registerForm.username.length < 3 || registerForm.username.length > 20) {
    alert('用户名长度在3-20位之间');
    return false;
  }
  if (!registerForm.name.trim()) {
    alert('请输入姓名');
    return false;
  }
  if (!registerForm.email.trim()) {
    alert('请输入邮箱');
    return false;
  }
  // 简单邮箱验证
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(registerForm.email)) {
    alert('请输入正确的邮箱格式');
    return false;
  }
  if (!registerForm.password) {
    alert('请输入密码');
    return false;
  }
  if (registerForm.password.length < 6) {
    alert('密码长度至少6位');
    return false;
  }
  if (registerForm.password !== registerForm.confirmPassword) {
    alert('两次输入的密码不一致');
    return false;
  }
  return true;
};

// 获取验证码
const refreshCaptcha = async () => {
  try {
    const res = await request({
      url: '/api/auth/captcha',
      method: 'get',
      params: { t: Date.now() }
    });
    
    if (res.code === 200) {
      captchaKey.value = res.key;
      
      // 处理不同类型的验证码
      if (res.captcha_text) {
        // 文本验证码
        captchaText.value = res.captcha_text;
        captchaImage.value = '';
        alert(captchaText.value);
      } else if (res.image) {
        // 图片验证码
        captchaImage.value = res.image;
        captchaText.value = '';
      }
      
      loginForm.captcha = '';
    }
  } catch (error) {
    console.error('获取验证码失败:', error);
    alert('获取验证码失败');
  }
};

// 登录
const handleLogin = async () => {
  try {
    if (!validateLoginForm()) {
      return;
    }
    
    if (!captchaKey.value) {
      alert('请先获取验证码');
      refreshCaptcha();
      return;
    }
    
    loading.value = true;
    
    const loginData = {
      username: loginForm.username,
      password: loginForm.password,
      captcha: loginForm.captcha,
      captcha_key: captchaKey.value
    };
    
    const res = await userStore.login(loginData);
    
    alert('登录成功！');
    setTimeout(() => router.push('/'), 500);
  } catch (err) {
    console.error('登录失败:', err);
    
    if (err.response?.data?.msg) {
      alert(err.response.data.msg);
    } else if (err.msg) {
      alert(err.msg);
    } else {
      alert('登录失败，请重试');
    }
    
    loginForm.captcha = '';
    refreshCaptcha();
  } finally {
    loading.value = false;
  }
};

// 注册
const handleRegister = async () => {
  try {
    if (!validateRegisterForm()) {
      return;
    }
    
    registerLoading.value = true;
    
    const registerData = {
      username: registerForm.username,
      name: registerForm.name,
      email: registerForm.email,
      password: registerForm.password
    };
    
    const res = await request({
      url: '/api/auth/register',
      method: 'post',
      data: registerData
    });
    
    if (res.code === 200) {
      // 注册成功后自动登录
      const loginData = {
        username: registerForm.username,
        password: registerForm.password,
        captcha: '123', // 注册后不需要验证码
        captcha_key: 'test' // 注册后不需要验证码
      };
      
      await userStore.login(loginData);
      
      alert('注册成功！');
      setTimeout(() => router.push('/'), 500);
    }
  } catch (err) {
    console.error('注册失败:', err);
    
    if (err.response?.data?.msg) {
      alert(err.response.data.msg);
    } else if (err.msg) {
      alert(err.msg);
    } else {
      alert('注册失败，请重试');
    }
  } finally {
    registerLoading.value = false;
  }
};

onMounted(() => {
  refreshCaptcha();
});
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-title {
  color: #333;
}

/* 标签页样式 */
.login-tabs .nav-link {
  color: #6c757d;
  font-weight: 500;
}

.login-tabs .nav-link.active {
  color: #409eff;
  border-bottom-color: #409eff;
  font-weight: 600;
}

/* 验证码样式 */
.captcha-box {
  width: 100%;
  height: 44px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  cursor: pointer;
  overflow: hidden;
  background-color: #f8f9fa;
  transition: all 0.3s;
}

.captcha-box:hover {
  border-color: #409eff;
  background-color: #e9ecef;
}

.captcha-text {
  font-weight: bold;
  color: #409eff;
}

.captcha-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.captcha-loading {
  color: #6c757d;
  font-size: 12px;
}

.captcha-hint {
  font-size: 12px;
  color: #6c757d;
}

/* 登录按钮样式 */
.login-btn {
  height: 48px;
  font-size: 16px;
  font-weight: 500;
  border-radius: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s ease;
}

.login-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.login-btn:active {
  transform: translateY(0);
}

/* 用户协议样式 */
.user-agreement {
  font-size: 12px;
  line-height: 1.5;
  color: #6c757d;
}

.user-agreement h4 {
  font-size: 14px;
  font-weight: 600;
  color: #343a40;
}

.user-agreement h5 {
  font-size: 13px;
  font-weight: 500;
  color: #495057;
}

.user-agreement p {
  text-align: justify;
}
</style>