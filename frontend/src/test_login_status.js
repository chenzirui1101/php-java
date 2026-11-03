// 前端登录状态测试脚本
// 用于验证实时判定登录状态的功能

import { useUserStore } from '@/stores/modules/user'
import { setStorage, getStorage, removeStorage } from '@/utils/storage.js'

// 测试1：初始登录状态测试
const testInitialLoginState = () => {
    console.log('\n===== 测试1：初始登录状态测试 =====')
    
    const userStore = useUserStore()
    console.log('初始token:', userStore.token)
    console.log('初始userInfo:', userStore.userInfo)
    console.log('初始isLogin:', userStore.isLogin)
    
    // 清除之前的测试数据
    removeStorage('token')
    removeStorage('userInfo')
    
    // 重新创建store实例
    const userStore2 = useUserStore()
    console.log('清除后token:', userStore2.token)
    console.log('清除后userInfo:', userStore2.userInfo)
    console.log('清除后isLogin:', userStore2.isLogin)
    
    if (!userStore2.token && !userStore2.userInfo.id && !userStore2.isLogin) {
        console.log('✅ 初始登录状态测试通过')
    } else {
        console.log('❌ 初始登录状态测试失败')
    }
}

// 测试2：登录状态设置测试
const testSetLoginState = () => {
    console.log('\n===== 测试2：登录状态设置测试 =====')
    
    const userStore = useUserStore()
    
    // 设置登录状态
    const testToken = 'test-token-123456'
    const testUserInfo = {
        id: 1,
        username: 'testuser',
        name: '测试用户',
        email: 'test@example.com'
    }
    
    // 使用store的actions设置登录状态
    userStore.token = testToken
    userStore.userInfo = testUserInfo
    
    // 存储到localStorage
    setStorage('token', testToken)
    setStorage('userInfo', testUserInfo)
    
    // 验证状态
    console.log('设置后token:', userStore.token)
    console.log('设置后userInfo:', userStore.userInfo)
    console.log('设置后isLogin:', userStore.isLogin)
    
    if (userStore.token === testToken && userStore.userInfo.id === testUserInfo.id && userStore.isLogin) {
        console.log('✅ 登录状态设置测试通过')
    } else {
        console.log('❌ 登录状态设置测试失败')
    }
}

// 测试3：退出登录测试
const testLogout = () => {
    console.log('\n===== 测试3：退出登录测试 =====')
    
    const userStore = useUserStore()
    
    // 确保有登录状态
    const testToken = 'test-token-123456'
    const testUserInfo = {
        id: 1,
        username: 'testuser',
        name: '测试用户',
        email: 'test@example.com'
    }
    
    userStore.token = testToken
    userStore.userInfo = testUserInfo
    setStorage('token', testToken)
    setStorage('userInfo', testUserInfo)
    
    console.log('退出前token:', userStore.token)
    console.log('退出前isLogin:', userStore.isLogin)
    
    // 调用退出登录
    userStore.logout()
    
    console.log('退出后token:', userStore.token)
    console.log('退出后userInfo:', userStore.userInfo)
    console.log('退出后isLogin:', userStore.isLogin)
    console.log('退出后localStorage.token:', getStorage('token'))
    console.log('退出后localStorage.userInfo:', getStorage('userInfo'))
    
    if (!userStore.token && !userStore.userInfo.id && !userStore.isLogin && !getStorage('token') && !getStorage('userInfo')) {
        console.log('✅ 退出登录测试通过')
    } else {
        console.log('❌ 退出登录测试失败')
    }
}

// 测试4：isLogin计算属性测试
const testIsLoginGetter = () => {
    console.log('\n===== 测试4：isLogin计算属性测试 =====')
    
    const userStore = useUserStore()
    
    // 清除之前的状态
    userStore.token = ''
    userStore.userInfo = {}
    removeStorage('token')
    removeStorage('userInfo')
    
    // 测试1：空token，空userInfo
    console.log('测试1 - 空token，空userInfo:', userStore.isLogin)
    const test1 = !userStore.isLogin
    
    // 测试2：有token，空userInfo
    userStore.token = 'test-token'
    console.log('测试2 - 有token，空userInfo:', userStore.isLogin)
    const test2 = !userStore.isLogin
    
    // 测试3：空token，有userInfo
    userStore.token = ''
    userStore.userInfo = { id: 1 }
    console.log('测试3 - 空token，有userInfo:', userStore.isLogin)
    const test3 = !userStore.isLogin
    
    // 测试4：有token，有userInfo
    userStore.token = 'test-token'
    userStore.userInfo = { id: 1 }
    console.log('测试4 - 有token，有userInfo:', userStore.isLogin)
    const test4 = userStore.isLogin
    
    // 验证结果
    if (test1 && test2 && test3 && test4) {
        console.log('✅ isLogin计算属性测试通过')
    } else {
        console.log('❌ isLogin计算属性测试失败')
    }
}

// 运行所有测试
const runAllTests = () => {
    console.log('\n====================================')
    console.log('开始测试实时登录状态判定功能')
    console.log('====================================')
    
    testInitialLoginState()
    testSetLoginState()
    testLogout()
    testIsLoginGetter()
    
    console.log('\n====================================')
    console.log('所有测试完成')
    console.log('====================================')
}

// 导出测试函数
export { runAllTests }

// 如果直接运行此文件，执行测试
if (typeof window !== 'undefined') {
    runAllTests()
}
