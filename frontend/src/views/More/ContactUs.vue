<template>
  <div class="contact-container">
    <h1 class="page-title">联系我们</h1>
    <div class="contact-card">
      <div class="contact-content">
        <!-- 左侧联系方式 -->
        <div class="contact-left">
          <h2 class="section-title">联系方式</h2>
          <div class="qrcode-card">
            <div class="qrcode-wrapper">
              <img src="@/assets/images/QQ.jpg" alt="QQ群二维码" class="qrcode-img">
            </div>
            <p class="qun-number">Q群：1026052297</p>
            <p class="qun-tip">扫码加入，获取最新校园资讯</p>
          </div>
        </div>

        <!-- 右侧留言反馈 -->
        <div class="contact-right">
          <h2 class="section-title">留言反馈</h2>
          <form class="feedback-form">
            <div class="form-row">
              <div class="form-group">
                <label for="name" class="form-label">姓名</label>
                <input
                  type="text"
                  class="form-input"
                  id="name"
                  placeholder="请输入您的姓名"
                  v-model="formData.name"
                >
              </div>
              <div class="form-group">
                <label for="email" class="form-label">邮箱</label>
                <input
                  type="email"
                  class="form-input"
                  id="email"
                  placeholder="请输入您的邮箱"
                  v-model="formData.email"
                >
              </div>
            </div>
            <div class="form-group full-width">
              <label for="message" class="form-label">留言内容</label>
              <textarea
                rows="5"
                class="form-textarea"
                id="message"
                placeholder="请输入您的留言内容，我们会尽快回复~"
                v-model="formData.message"
              ></textarea>
            </div>
            <button
              type="button"
              class="submit-btn"
              @click="submitMessage"
            >
              提交留言
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// 表单数据双向绑定
const formData = ref({
  name: '',
  email: '',
  message: ''
});

const submitMessage = () => {
  // 基础表单校验
  if (!formData.value.name.trim()) {
    alert('请输入您的姓名');
    return;
  }
  if (!formData.value.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    alert('请输入正确的邮箱地址');
    return;
  }
  if (!formData.value.message.trim()) {
    alert('请输入留言内容');
    return;
  }

  // 这里可以添加API提交逻辑，现在保留提示
  alert('提交成功，我们会尽快通过邮箱联系您~');
  // 提交后清空表单
  formData.value = { name: '', email: '', message: '' };
};
</script>

<style scoped>
/* 全局容器 */
.contact-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
  font-family: 'PingFang SC', 'Microsoft YaHei', sans-serif;
  background-color: #fafafa;
  min-height: calc(100vh - 80px);
}

/* 页面标题 */
.page-title {
  font-size: 36px;
  font-weight: 700;
  margin-bottom: 32px;
  text-align: center;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* 主卡片 */
.contact-card {
  background: #ffffff;
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
}

.contact-card:hover {
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

/* 内容布局 */
.contact-content {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 48px;
  padding: 48px;
}

/* 左侧区域 */
.contact-left {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* 分区标题 */
.section-title {
  font-size: 28px;
  font-weight: 600;
  margin-bottom: 24px;
  align-self: flex-start;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

/* 二维码卡片 */
.qrcode-card {
  width: 280px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  padding: 24px;
  text-align: center;
  transition: all 0.3s ease;
}

.qrcode-card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  transform: translateY(-4px);
}

.qrcode-wrapper {
  width: 100%;
  padding-bottom: 100%;
  position: relative;
  margin-bottom: 16px;
  border-radius: 12px;
  overflow: hidden;
  background: linear-gradient(135deg, #f0f9ff, #fdf2f8);
}

.qrcode-img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 12px;
}

.qun-number {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0 0 8px 0;
}

.qun-tip {
  font-size: 14px;
  color: #999;
  margin: 0;
}

/* 右侧表单区域 */
.contact-right {
  display: flex;
  flex-direction: column;
}

.feedback-form {
  width: 100%;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 16px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}

.form-input,
.form-textarea {
  padding: 12px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  font-size: 15px;
  color: #333;
  background: #fafafa;
  transition: all 0.3s ease;
  outline: none;
}

.form-input:focus,
.form-textarea:focus {
  border-color: #FF6B9D;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
}

/* 提交按钮 */
.submit-btn {
  width: 100%;
  margin-top: 12px;
  padding: 14px 24px;
  border: none;
  border-radius: 30px;
  font-size: 16px;
  font-weight: 600;
  color: #fff;
  background: linear-gradient(135deg, #FF6B9D, #FFD93D);
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(255, 107, 157, 0.3);
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(255, 107, 157, 0.4);
}

.submit-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(255, 107, 157, 0.3);
}

/* 响应式适配 */
@media (max-width: 992px) {
  .contact-content {
    grid-template-columns: 1fr;
    gap: 40px;
    padding: 32px 24px;
  }

  .contact-left {
    order: 2;
  }

  .contact-right {
    order: 1;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .page-title {
    font-size: 28px;
  }

  .section-title {
    font-size: 24px;
  }

  .qrcode-card {
    width: 100%;
    max-width: 280px;
  }
}
</style>