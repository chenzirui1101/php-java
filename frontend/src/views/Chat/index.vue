<template>
  <div class="container py-8">
    <div class="chat-page">
      <!-- 页面标题 -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-primary">
          <i class="fa fa-envelope-o mr-2"></i> 消息中心
        </h1>
        <p class="text-muted">与其他用户进行实时聊天</p>
      </div>
      
      <!-- 聊天主内容区 -->
      <div class="card shadow-lg hover:shadow-xl transition-all duration-300 d-flex flex-column" style="min-height: 600px;">
        <div class="row h-100 flex-grow-1">
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
            <div class="messages-list overflow-y-auto" style="max-height: 530px;">
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
                      style="transition: transform 0.3s ease;"
                      @mouseenter="$event.target.style.transform = 'scale(1.1)'"
                      @mouseleave="$event.target.style.transform = 'scale(1)'"
                    />
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center">
                        <h6 class="font-bold mb-0 mr-2">{{ conversation.other_user?.username || '未知用户' }}</h6>
                        <!-- 在线状态指示器 -->
                        <span 
                          class="online-indicator"
                          :class="{ 'online': isUserOnline(conversation), 'offline': !isUserOnline(conversation) }"
                          title="{{ isUserOnline(conversation) ? '在线' : '离线' }}"
                        ></span>
                      </div>
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
          <div class="col-md-8 h-100 d-flex flex-column">
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
                    <p class="text-sm text-muted mb-0">
                      <!-- 根据登录状态显示在线/离线 -->
                      <span v-if="currentChatUser?.id && isCurrentUserOnline" class="text-success">在线</span>
                      <span v-else class="text-muted">离线</span>
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- 聊天消息 -->
              <div class="flex-grow-1 p-4 overflow-y-auto messages-content">
                <div v-if="loading.messages" class="h-100 d-flex align-items-center justify-center">
                  <div>
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                      <span class="sr-only">加载中...</span>
                    </div>
                    <span class="ml-2 text-muted">加载消息中...</span>
                  </div>
                </div>
                <div v-else-if="chatMessages.length === 0" class="h-100 d-flex align-items-center justify-center">
                  <div>
                    <i class="fa fa-comments-o" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>开始聊天吧</p>
                  </div>
                </div>
                <div 
                  class="message mb-4" 
                  v-for="message in chatMessages" 
                  :key="message.id || message.tempId"
                  :class="{ 'sent': message.senderId === userStore.userInfo.id, 'received': message.senderId !== userStore.userInfo.id }"
                >
                  <div class="d-flex" :class="message.senderId === userStore.userInfo.id ? 'justify-content-end' : 'justify-content-start'">
                    <div 
                      class="message-bubble" 
                      :class="message.senderId === userStore.userInfo.id ? 'bg-primary text-white' : 'bg-white shadow-sm'"
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
            <div v-else class="flex-grow-1 d-flex flex-column align-items-center justify-content-center">
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
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useUserStore } from '@/stores/modules/user'
import { chatApi } from '@/api/modules/chat'

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

// 获取路由对象
const route = useRoute()

// 获取用户状态
const userStore = useUserStore()

// 计算当前会话的聊天对象信息
const currentChatUser = computed(() => {
    if (!currentConversation.value) return null
    
    return {
        id: currentConversation.value.other_user?.id || null,
        name: currentConversation.value.other_user?.username || '未知用户',
        avatar: currentConversation.value.other_user?.avatar || 'https://picsum.photos/40/40'
    }
})

// 计算当前聊天用户是否在线
const isCurrentUserOnline = computed(() => {
    if (!currentChatUser.value) return false
    
    // 对于当前聊天用户，使用isUserOnline函数判断
    return isUserOnline(currentConversation.value)
})

// 判断指定会话的用户是否在线
const isUserOnline = (conversation) => {
    if (!conversation || !conversation.other_user?.id) return false
    
    // 获取当前登录用户信息
    const userStore = useUserStore()
    
    // 如果当前用户未登录，所有用户都显示为离线
    if (!userStore.token || !userStore.userInfo.id) {
        return false
    }
    
    // 当前登录用户自己才显示为在线
    if (conversation.other_user.id === userStore.userInfo.id) {
        return true
    }
    
    // 对于其他用户，简单实现：如果最近1小时内有活动，就判定为在线
    // 这里可以根据实际需求调整时间阈值
    const oneHourAgo = Date.now() - 60 * 60 * 1000
    const lastActivityTime = conversation.updated_at ? new Date(conversation.updated_at).getTime() : 0
    
    return lastActivityTime > oneHourAgo
}

// 获取会话列表
const getConversations = async () => {
  try {
    loading.value.conversations = true
    error.value = ''
    const res = await chatApi.getConversations()
    console.log('后端返回的完整会话列表数据:', res)
    if (res.code === 200) {
      // 直接使用后端返回的数据，不进行过滤
      conversations.value = Array.isArray(res.data) ? res.data : []
      
      // 更新未读消息数量
      const userStore = useUserStore()
      const totalUnread = conversations.value.reduce((sum, conversation) => sum + (conversation.unread_count || 0), 0)
      userStore.updateUnreadMessageCount(totalUnread)
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
    console.log('后端返回的消息数据:', res)
    if (res.code === 200) {
      // 直接使用后端返回的数据，不进行过滤
      chatMessages.value = Array.isArray(res.data) ? res.data.map(msg => ({
        id: msg.id,
        senderId: msg.sender_id,
        content: msg.content,
        time: new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      })) : []
      
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
      senderId: userStore.userInfo.id || 1,
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
      const index = chatMessages.value.findIndex(msg => msg.tempId === tempMessage.tempId)
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
      const index = chatMessages.value.findIndex(msg => msg.tempId === tempMessage.tempId)
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
    // 发送完主动刷新一次消息
    getMessages(selectedConversationId.value)
    getConversations()
    
    // 刷新未读消息数量
    setTimeout(() => {
      const userStore = useUserStore()
      chatApi.getConversations().then(res => {
        if (res.code === 200 && Array.isArray(res.data)) {
          const totalUnread = res.data.reduce((sum, conversation) => sum + (conversation.unread_count || 0), 0)
          userStore.updateUnreadMessageCount(totalUnread)
        }
      })
    }, 500)

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

// 组件挂载时获取会话列表
onMounted(async () => {
  await getConversations()
  
  // 检查URL查询参数中是否有会话ID，自动选中会话
  const conversationIdFromQuery = route.query.conversation_id || route.query.conversationId
  if (conversationIdFromQuery) {
    console.log('从URL获取到会话ID:', conversationIdFromQuery)
    selectedConversationId.value = parseInt(conversationIdFromQuery)
  }
  
  // 页面切回来时自动刷新消息（非常省服务器）
  const handleVisible = () => {
    if (!document.hidden && selectedConversationId.value) {
      getMessages(selectedConversationId.value)
      getConversations()
    }
  }
  window.addEventListener('visibilitychange', handleVisible)

  // 组件销毁时移除监听
  onUnmounted(() => {
    window.removeEventListener('visibilitychange', handleVisible)
  })
})
</script>

<style scoped>
.chat-page {
  max-width: 1200px;
  margin: 0 auto;
}

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
  min-height: 400px;
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

/* 聊天输入样式 */
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

/* 在线状态指示器样式 */
.online-indicator {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-left: 5px;
  transition: all 0.3s ease;
}

.online-indicator.online {
  background-color: #28a745;
  box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
}

.online-indicator.offline {
  background-color: #6c757d;
  box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2);
}

/* 修复字母选择器定位问题 */
/* 将聊天页面设置为相对定位，确保它在字母选择器上方 */
.chat-page {
  position: relative;
  z-index: 10;
}

/* 隐藏垂直字母选择器 */
/* 针对截图中显示的垂直排列字母选择器 */
body > div:not([class^="container"]):not([class*=" container"]) {
  display: none !important;
  position: static !important;
}

/* 或者更精确地隐藏垂直排列的字母元素 */
div[style*="position: fixed"] {
  display: none !important;
}

/* 隐藏右侧固定定位的元素 */
body > div[style*="right: 0"] {
  display: none !important;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .card {
    margin: 0 -15px;
    border-radius: 0;
  }
  
  .row {
    flex-direction: column;
  }
  
  .col-md-4 {
    border-right: none;
    border-bottom: 1px solid #e9ecef;
    max-height: 300px;
  }
  
  .col-md-8 {
    height: auto;
    min-height: 400px;
  }
}
</style>