// 路由规则列表
import { createRouter, createWebHistory } from 'vue-router'
const routes = [
  {
    path: '/',
    redirect: '/home' // 默认跳转到首页
  },
  {
    path: '/login',
    name: 'Login',
    redirect: '/', // 将登录页面重定向到首页，通过首页的导航条模态框登录
    meta: { title: '登录', requiresAuth: false }
  },
  // 首页
  {
    path: '/home',
    name: 'Home',
    component: () => import('../views/Home/index.vue'),
    meta: { requiresAuth: false, title: '首页' } // 未登录也能看首页
  },
  // 商品详情页
  {
    path: '/product/:id',
    name: 'ProductDetail',
    component: () => import('../views/ProductDetail/index.vue'),
    meta: { requiresAuth: false, title: '商品详情' }
  },
  // 发布商品页（需要登录）
  {
    path: '/publish',
    name: 'PublishProduct',
    component: () => import('../views/PublishProduct/index.vue'),
    meta: { requiresAuth: true, title: '发布商品' } // 需登录才能访问
  },
  // 个人中心（需要登录）
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../views/Profile/index.vue'),
    meta: { requiresAuth: true, title: '个人中心' }
  },
  // 聊天页面（需要登录）
  {
    path: '/chat',
    name: 'Chat',
    component: () => import('../views/Chat/index.vue'),
    meta: { requiresAuth: true, title: '消息中心' }
  },
  // 校园热点
  {
    path: '/campus-hot',
    name: 'campus-hot',
    component: () => import('../views/Forum/ForumPage.vue'),
    meta: { requiresAuth: false, title: '校园热点' }
  },
  // 校园趣事
  {
    path: '/campus-fun',
    name: 'campus-fun',
    component: () => import('../views/Forum/ForumPage.vue'),
    meta: { requiresAuth: false, title: '校园趣事' }
  },
  // 校园闲聊
  {
    path: '/forum',
    name: 'forum',
    component: () => import('../views/Forum/ForumPage.vue'),
    meta: { requiresAuth: false, title: '校园闲聊' }
  },
  // 论坛话题详情
  {
    path: '/forum/topic/:id',
    name: 'ForumDetail',
    component: () => import('../views/Forum/ForumDetail.vue'),
    meta: { requiresAuth: false, title: '话题详情' }
  },

  // 失物招领
  {
    path: '/lostfound',
    name: 'lostfound',
    component: () => import('../views/LostFound/LostFoundPage.vue'),
    meta: { requiresAuth: false, title: '失物招领' }
  },
  // 失物招领详情
  {
    path: '/lostfound/:id',
    name: 'LostFoundDetail',
    component: () => import('../views/LostFound/LostFoundDetail.vue'),
    meta: { requiresAuth: false, title: '失物招领详情' }
  },
  // 发布失物招领（需要登录）
  {
    path: '/lostfound/publish',
    name: 'PublishLostFound',
    component: () => import('../views/LostFound/PublishLostFound.vue'),
    meta: { requiresAuth: true, title: '发布失物招领' }
  },
  // 物品展示
  {
    path: '/secondhand',
    name: 'secondhand',
    component: () => import('../views/SecondHand/SecondHandPage.vue'),
    meta: { requiresAuth: false, title: '物品展示' }
  },
  // 校园活动
  {
    path: '/campus-activities',
    name: 'campus-activities',
    component: () => import('../views/CampusActivities/CampusActivitiesPage.vue'),
    meta: { requiresAuth: false, title: '校园活动' }
  },
  // 发布活动（需要登录）
  {
    path: '/campus-activities/publish',
    name: 'PublishActivity',
    component: () => import('../views/CampusActivities/PublishActivity.vue'),
    meta: { requiresAuth: true, title: '发布活动' }
  },
  // 更多（包含联系我们、关于我们）
  {
    path: '/more',
    name: 'more',
    component: () => import('../views/More/MorePage.vue'),
    meta: { requiresAuth: false, title: '更多' },
    children: [
      {
        path: 'contact',
        name: 'contact',
        component: () => import('../views/More/ContactUs.vue'),
        meta: { title: '联系我们' }
      },
      {
        path: 'about',
        name: 'about',
        component: () => import('../views/More/AboutUs.vue'),
        meta: { title: '关于我们' }
      },
      {
        path: 'to-dream',
        name: 'to-dream',
        component: () => import('../views/More/ToDream.vue'),
        meta: { title: '到梦活动' }
      },
      {
        path: 'xuexitong',
        name: 'xuexitong',
        component: () => import('../views/More/XueXiTong.vue'),
        meta: { title: '学习通' }
      },
      {
        path: 'volunteer',
        name: 'volunteer',
        component: () => import('../views/More/Volunteer.vue'),
        meta: { title: '志愿者' }
      }
    ]
  },
  // DIY纪念册（未登录也可以编辑，作品直接下载到本地）
  {
    path: '/diy-album',
    name: 'DIYAlbum',
    component: () => import('../views/DIYAlbum/index.vue'),
    meta: { requiresAuth: false, title: 'DIY纪念册' }
  },
  // 管理员后台
  {
    path: '/admin',
    redirect: '/admin/products',
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'Admin',
    component: () => import('../views/Admin/Index.vue'),
    meta: { title: '管理员后台', requiresAuth: true },
    children: [
        {
          path: 'products',
          name: 'AdminProducts',
          component: () => import('../views/Admin/Products.vue'),
          meta: { title: '商品管理', requiresAuth: true }
        },
        {
          path: 'lostfound',
          name: 'AdminLostFound',
          component: () => import('../views/Admin/LostFound.vue'),
          meta: { title: '失物管理', requiresAuth: true }
        },
        {
          path: 'forum',
          name: 'AdminForum',
          component: () => import('../views/Admin/Forum.vue'),
          meta: { title: '话题管理', requiresAuth: true }
        },
        {
          path: 'activities',
          name: 'AdminActivities',
          component: () => import('../views/Admin/Activities.vue'),
          meta: { title: '活动管理', requiresAuth: true }
        },
        {
          path: 'users',
          name: 'AdminUsers',
          component: () => import('../views/Admin/Users.vue'),
          meta: { title: '用户管理', requiresAuth: true }
        },
        {
          path: 'moderation',
          name: 'AdminModeration',
          component: () => import('../views/Admin/Moderation.vue'),
          meta: { title: '内容审核', requiresAuth: true }
        }
      ]
  },
  // 404页面
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound/index.vue'),
    meta: { title: '页面不存在' }
  }
]
export default routes
