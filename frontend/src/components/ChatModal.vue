<template>
  <div 
    v-if="visible" 
    class="modal fade show" 
    id="chatModal" 
    tabindex="-1" 
    role="dialog" 
    aria-labelledby="chatModalLabel" 
    style="display: block; background-color: rgba(0, 0, 0, 0.5);"
    @click="handleBackdropClick"
  >
    <div class="modal-dialog modal-xl" role="document" @click.stop>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="chatModalLabel">
            <i class="fa fa-envelope-o mr-2"></i> 消息中心
          </h5>
          <button type="button" class="close" @click="$emit('close')" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0">
          <div class="row h-100">
            <!-- 消息列表 -->
            <div class="col-md-4 border-right h-100">
              <div class="p-4 border-bottom">
                <h6 class="font-bold">消息列表</h6>
                <div v-if="loading.conversations" class="mt-2">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="sr-only">加载中...</span>
                  </div>
                  <span class="ml-2 text-muted">加载会话中...</span>
                </div>
              </div>
              <div class="messages-list overflow-y-auto" style="max-height: 500px;">
                <div v-if="conversations.length === 0 && !loading.conversations" class="p-4 text-center text-muted">
                  <i class="fa fa-envelope-open-o" style="font-size: 2rem; margin-bottom: 10px;"></i>
                  <p>暂无消息</p>
                </div>
                <div 
                  class="message-item p-4 border-bottom cursor-pointer hover:bg-gray-100 transition-colors"
                  v-for="conversation in conversations" 
                  :key="conversation.id"
                  :class="{ 'active': selectedConversationId === conversation.id }"
                  @click="selectedConversationId = conversation.id"
                >
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle overflow-hidden mr-3" style="width: 40px; height: 40px;">
                      <img 
                        :src="(conversation.other_user?.avatar || 'https://picsum.photos/40/40')" 
                        alt="用户头像" 
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex justify-content-between align-items-center">
                        <h6 class="font-bold mb-0">{{ conversation.other_user?.username || '未知用户' }}</h6>
                        <small class="text-muted">{{ new Date(conversation.updated_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</small>
                      </div>
                      <p class="text-sm text-muted mb-0">{{ conversation.last_message || '暂无消息' }}</p>
                    </div>
                    <div v-if="conversation.unread_count > 0" class="bg-primary text-white rounded-full w-6 h-6 d-flex align-items-center justify-center text-xs font-bold">
                      {{ conversation.unread_count }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- 聊天内容 -->
            <div class="col-md-8 h-100">
              <div v-if="selectedConversationId" class="h-100 d-flex flex-column">
                <!-- 聊天头部 -->
                <div class="p-4 border-bottom">
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle overflow-hidden mr-3" style="width: 40px; height: 40px;">
                      <img 
                        :src="currentChatUser?.avatar || 'https://picsum.photos/40/40'" 
                        alt="用户头像" 
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div>
                      <h6 class="font-bold mb-0">{{ currentChatUser?.name }}</h6>
                      <p class="text-sm text-muted mb-0">在线</p>
                    </div>
                  </div>
                </div>
                
                <!-- 聊天消息 -->
                <div class="flex-grow-1 p-4 overflow-y-auto messages-content" style="max-height: 400px; background-color: #f8f9fa;">
                  <div v-if="loading.messages" class="text-center p-4">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                      <span class="sr-only">加载中...</span>
                    </div>
                    <span class="ml-2 text-muted">加载消息中...</span>
                  </div>
                  <div v-else-if="chatMessages.length === 0" class="p-4 text-center text-muted">
                    <i class="fa fa-comments-o" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>开始聊天吧</p>
                  </div>
                  <div 
                    class="message mb-4" 
                    v-for="message in chatMessages" 
                    :key="message.id || message.tempId"
                    :class="{ 'sent': message.senderId === 1, 'received': message.senderId !== 1 }"
                  >
                    <div class="d-flex" :class="message.senderId === 1 ? 'justify-content-end' : 'justify-content-start'">
                      <div 
                        class="message-bubble" 
                        :class="message.senderId === 1 ? 'bg-primary text-white' : 'bg-white shadow-sm'"
                        :style="message.isSending ? { opacity: 0.6, maxWidth: '70%', padding: '10px 15px', borderRadius: '15px' } : { maxWidth: '70%', padding: '10px 15px', borderRadius: '15px' }"
                      >
                        <p class="mb-1">{{ message.content }}</p>
                        <small class="text-xs opacity-75">{{ message.time }}</small>
                        <span v-if="message.isSending" class="ml-2">
                          <i class="fa fa-circle-o-notch fa-spin fa-xs"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                  <div v-if="error" class="p-3 mb-4 bg-danger text-white rounded">
                    <i class="fa fa-exclamation-circle mr-2"></i>{{ error }}
                  </div>
                </div>
                
                <!-- 聊天输入 -->
                <div class="p-4 border-top">
                  <div class="input-group">
                    <input 
                      type="text" 
                      class="form-control" 
                      placeholder="输入消息..." 
                      v-model="messageInput"
                      @keyup.enter="sendMessage"
                      :disabled="loading.sending"
                    >
                    <div class="input-group-append">
                      <button 
                        class="btn btn-primary" 
                        @click="sendMessage"
                        :disabled="loading.sending || !messageInput.trim()"
                      >
                        <i v-if="!loading.sending" class="fa fa-paper-plane"></i>
                        <i v-else class="fa fa-circle-o-notch fa-spin"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="h-100 d-flex align-items-center justify-center">
                <div class="text-center">
                  <div class="text-primary mb-3" style="font-size: 3rem;">
                    <i class="fa fa-envelope-o"></i>
                  </div>
                  <h4 class="mb-2">选择一个聊天对象</h4>
                  <p class="text-muted">点击左侧消息列表中的用户开始聊天</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import { chatApi } from '@/api/modules/chat'

// 定义组件属性
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  }
})

// 定义事件
const emit = defineEmits(['close'])

// 点击模态框外部关闭
const handleBackdropClick = (e) => {
  if (e.target.id === 'chatModal') {
    emit('close')
  }
}

// 监听Esc键关闭
const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

// 消息相关
const conversations = ref([])
const selectedConversationId = ref(null)
const currentConversation = ref(null)
const chatMessages = ref([])
const messageInput = ref('')
const loading = ref({
  conversations: false,
  messages: false,
  sending: false
})
const error = ref('')

// 计算当前会话的聊天对象信息
const currentChatUser = computed(() => {
  if (!currentConversation.value) return null
  
  return {
    id: currentConversation.value.other_user.id,
    name: currentConversation.value.other_user.username,
    avatar: currentConversation.value.other_user.avatar || 'https://picsum.photos/40/40'
  }
})

// 获取会话列表
const getConversations = async () => {
  if (!props.visible) return
  
  try {
    loading.value.conversations = true
    error.value = ''
    const res = await chatApi.getConversations()
    if (res.code === 200) {
      conversations.value = res.data
    } else {
      error.value = res.msg || '获取会话列表失败'
      console.error('获取会话列表失败:', res)
    }
  } catch (err) {
    error.value = '获取会话列表失败，请稍后重试'
    console.error('获取会话列表失败:', err)
  } finally {
    loading.value.conversations = false
  }
}

// 获取会话消息
const getMessages = async (conversationId) => {
  if (!conversationId) return
  
  try {
    loading.value.messages = true
    error.value = ''
    const res = await chatApi.getMessages(conversationId)
    if (res.code === 200) {
      chatMessages.value = res.data.map(msg => ({
        id: msg.id,
        senderId: msg.sender_id,
        content: msg.content,
        time: new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      }))
      
      // 滚动到底部
      setTimeout(() => {
        const messagesContent = document.querySelector('.messages-content')
        if (messagesContent) {
          messagesContent.scrollTop = messagesContent.scrollHeight
        }
      }, 100)
    } else {
      error.value = res.msg || '获取消息失败'
      console.error('获取消息失败:', res)
    }
  } catch (err) {
    error.value = '获取消息失败，请稍后重试'
    console.error('获取消息失败:', err)
  } finally {
    loading.value.messages = false
  }
}

// 发送消息
const sendMessage = async () => {
  if (!messageInput.value.trim() || !selectedConversationId.value) return
  
  try {
    loading.value.sending = true
    error.value = ''
    
    // 本地先添加消息
    const tempMessage = {
      tempId: Date.now(),
      senderId: 1,
      content: messageInput.value.trim(),
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      isSending: true
    }
    chatMessages.value.push(tempMessage)
    messageInput.value = ''
    
    // 滚动到底部
    setTimeout(() => {
      const messagesContent = document.querySelector('.messages-content')
      if (messagesContent) {
        messagesContent.scrollTop = messagesContent.scrollHeight
      }
    }, 100)
    
    // 调用API发送消息
    const res = await chatApi.sendMessage(selectedConversationId.value, tempMessage.content)
    if (res.code === 200) {
      // 替换临时消息为真实消息
      const index = chatMessages.value.findIndex(msg => msg.id === tempMessage.id)
      if (index !== -1) {
        chatMessages.value[index] = {
          id: res.data.id,
          senderId: res.data.sender_id,
          content: res.data.content,
          time: new Date(res.data.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        }
      }
    } else {
      error.value = res.msg || '发送消息失败'
      console.error('发送消息失败:', res)
      // 移除临时消息
      const index = chatMessages.value.findIndex(msg => msg.id === tempMessage.id)
      if (index !== -1) {
        chatMessages.value.splice(index, 1)
      }
      // 恢复输入内容
      messageInput.value = tempMessage.content
    }
  } catch (err) {
    error.value = '发送消息失败，请稍后重试'
    console.error('发送消息失败:', err)
    // 移除临时消息
    const index = chatMessages.value.findIndex(msg => msg.isSending)
    if (index !== -1) {
      chatMessages.value.splice(index, 1)
    }
  } finally {
    loading.value.sending = false
  }
}

// 监听选中的会话变化
watch(selectedConversationId, (newVal) => {
  if (newVal) {
    currentConversation.value = conversations.value.find(conv => conv.id === newVal)
    getMessages(newVal)
  } else {
    currentConversation.value = null
    chatMessages.value = []
  }
})

// 监听模态框显示状态，获取会话列表
watch(() => props.visible, (newVal) => {
  if (newVal) {
    getConversations()
  }
})

// 组件挂载时获取会话列表
onMounted(() => {
  getConversations()
})
</script>

<style scoped>
/* 消息列表样式 */
.messages-list {
  overflow-y: auto;
}

.message-item {
  cursor: pointer;
  transition: all 0.3s ease;
}

.message-item:hover {
  background-color: #f8f9fa;
}

.message-item.active {
  background-color: #fff3cd;
  border-left: 4px solid #FF8A5C;
}

/* 聊天内容样式 */
.messages-content {
  overflow-y: auto;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.message {
  margin-bottom: 16px;
}

.message.sent .message-bubble {
  background-color: #FF8A5C;
  color: white;
  border-bottom-right-radius: 4px;
}

.message.received .message-bubble {
  background-color: white;
  color: #333;
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 消息输入样式 */
.input-group {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border-radius: 25px;
  overflow: hidden;
}

.input-group .form-control {
  border: none;
  border-radius: 25px 0 0 25px;
  padding: 10px 20px;
}

.input-group .form-control:focus {
  box-shadow: none;
}

.input-group-append .btn {
  border: none;
  border-radius: 0 25px 25px 0;
  background-color: #FF8A5C;
  color: white;
  padding: 10px 20px;
  transition: all 0.3s ease;
}

.input-group-append .btn:hover {
  background-color: #FF6B5C;
  transform: scale(1.05);
}

/* 滚动条样式 */
.messages-list::-webkit-scrollbar,
.messages-content::-webkit-scrollbar {
  width: 6px;
}

.messages-list::-webkit-scrollbar-track,
.messages-content::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.messages-list::-webkit-scrollbar-thumb,
.messages-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.messages-list::-webkit-scrollbar-thumb:hover,
.messages-content::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>