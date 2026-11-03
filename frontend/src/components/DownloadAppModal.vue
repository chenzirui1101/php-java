<template>
  <div class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);" @click="closeModal">
    <div class="modal-dialog modal-dialog-centered" @click.stop>
      <div class="modal-content rounded-lg shadow-lg" style="border: none;">
        <!-- 模态框头部 -->
        <div class="modal-header bg-gradient-to-r from-pink-500 to-orange-500 text-white">
          <h5 class="modal-title font-weight-bold">下载潮青行App</h5>
          <button type="button" class="close text-white" @click="closeModal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <!-- 模态框内容 -->
        <div class="modal-body p-6">
          <div class="text-center">
            <!-- 二维码占位 -->
            <div class="mb-4">
              <img 
                src="/qrcode.png?v=2" 
                alt="App下载二维码" 
                class="img-fluid rounded shadow-md"
                style="max-width: 200px; margin: 0 auto;"
              >
            </div>
            <!-- 提示信息 -->
            <p class="text-muted mb-4">扫描上方二维码下载潮青行App</p>
            <!-- 下载按钮 -->
            <div class="d-flex justify-content-center gap-3">
              <a href="/潮青行1.0.2.apk" download class="btn btn-primary py-3 px-5 d-inline-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #FF6B9D, #FFD93D); border: none; color: white; text-decoration: none;">
                <i class="fa fa-android mr-2"></i> Android
              </a>
              <button class="btn btn-secondary py-3 px-5">
                <i class="fa fa-apple mr-2"></i> iOS
              </button>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'

const emit = defineEmits(['close'])

const closeModal = () => {
  emit('close')
}
</script>

<style scoped>
/* 自定义模态框样式 */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  overflow: hidden;
  outline: 0;
}

.modal-dialog {
  position: relative;
  width: auto;
  margin: 0.5rem;
  pointer-events: none;
}

.modal-content {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  pointer-events: auto;
  background-color: #fff;
  background-clip: padding-box;
  border: 1px solid rgba(0, 0, 0, 0.2);
  border-radius: 0.3rem;
  outline: 0;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1rem 1rem;
  border-bottom: 1px solid #dee2e6;
  border-top-left-radius: calc(0.3rem - 1px);
  border-top-right-radius: calc(0.3rem - 1px);
}

.modal-title {
  margin-bottom: 0;
  line-height: 1.5;
}

.modal-body {
  position: relative;
  flex: 1 1 auto;
  padding: 1rem;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 1rem;
  border-top: 1px solid #dee2e6;
  border-bottom-right-radius: calc(0.3rem - 1px);
  border-bottom-left-radius: calc(0.3rem - 1px);
}

/* 响应式调整 */
@media (min-width: 576px) {
  .modal-dialog {
    max-width: 500px;
    margin: 1.75rem auto;
  }
}
</style>