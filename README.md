# 潮青行 · 校园生活信息服务社区

> 一个面向高校学生的校园生活服务社区，覆盖二手交易、论坛交流、失物招领、校园活动与站内即时通讯。
>
> 仓库同时保留 **Laravel（PHP）** 与 **Spring Boot（Java）** 两套后端实现，可用于对比同一业务在两种技术栈下的架构演进。
---

## 目录

- [1. 项目简介与开源体验](#1-项目简介与开源体验)
- [2. 目录结构说明](#2-目录结构说明)
- [3. 前端核心设计与工程化](#3-前端核心设计与工程化)
- [4. 核心重构对比：Laravel 到 Spring Boot](#4-核心重构对比laravel-php-到-spring-boot-java)
- [5. 进阶架构设计：Redis 与锁机制](#5-进阶架构设计redis-与锁机制)
- [6. AI 辅助开发心得](#6-ai-辅助开发心得)
- [7. 后续规划](#7-后续规划)

---

## 1. 项目简介与开源体验

### 1.1 项目定位

「潮青行」是一个校园生活信息服务社区，目标是让学生在一个平台内完成**信息获取、闲置流转、互助沟通**三件事：

| 模块 | 能力 |
| --- | --- |
| 用户认证 | 注册 / 登录 / JWT 鉴权 / 个人资料 / 关注关系 |
| 校园二手交易 | 商品发布、分类筛选、收藏、评价、库存扣减、发布审核 |
| 校园论坛 | 话题发布、回复、点赞、收藏、热门话题、活跃用户 |
| 失物招领 | 寻物 / 招领信息发布、详情、审核 |
| 校园活动 | 活动发布、列表、详情、审核 |
| 即时通讯 | 会话列表、消息收发、未读计数 |
| 管理后台 | 商品 / 用户 / 论坛 / 失物招领 / 活动管理，统一内容审核 |
| 基础能力 | 图片上传、统一返回体、全局异常、限流、防重放、内容审核 |

### 1.2 仓库说明

本仓库是一份"可对比演进"的完整代码，包含三部分：

```text
php-java/
├── frontend/   前端（Vue 3 + Vite），当前线上使用版本
├── backend/    后端（Java 17 + Spring Boot 3），重构后的版本
└── laravel/    后端（PHP 8 + Laravel 9），重构前的原始版本
```

- `frontend/` 对接两套后端，接口路径保持一致，可直接切换。
- `backend/` 是本次重构的重点，采用标准 MVC 分层 + MyBatis-Plus + Redis。
- `laravel/` 原样保留，方便对照路由、模型、迁移、Seeder 与业务逻辑的对应关系。

> 说明：前端当前是一套 **Vue 3 Web 应用**，通过响应式布局同时适配 PC 与移动端浏览器。
> 若需要小程序 / App，可复用同一套 REST API 与业务模型，另建 UniApp 工程接入；本仓库未内置 `@dcloudio/uni-app` 依赖。

### 1.3 测试账号

数据库初始化后可直接登录（密码统一为 `123456`）：

| 账号 | 角色 | 说明 |
| --- | --- | --- |
| `admin` | 管理员 | 可进入管理后台、审核内容 |
| `xiaoming` | 普通用户 | 有话题、商品、失物招领等演示数据 |
| `xiaohong` | 普通用户 | 论坛活跃用户 |
| `xiaogang` | 普通用户 | 论坛活跃用户 |

> 生产环境请务必修改默认密码，并通过 `JWT_SECRET` 环境变量覆盖签名密钥。

### 1.4 快速开始

**环境要求**

| 依赖 | 版本 |
| --- | --- |
| JDK | 17+ |
| Maven | 3.8+ |
| Node.js | 18+ |
| MySQL | 8.0 |
| Redis | 6/7 |

**第一步：初始化数据库**

```bash
# 1. 创建数据库
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS laravel_graduation DEFAULT CHARACTER SET utf8mb4;"

# 2. 建表
mysql -u root -p laravel_graduation < backend/src/main/resources/db/init.sql

# 3. 写入种子数据（可重复执行，幂等）
mysql -u root -p laravel_graduation < backend/src/main/resources/db/seed.sql
```

**第二步：修改后端数据源**

编辑 `backend/src/main/resources/application-dev.yml`，将数据库账号密码改为本地值：

```yaml
spring:
  datasource:
    url: jdbc:mysql://127.0.0.1:3306/laravel_graduation?useUnicode=true&characterEncoding=utf8&serverTimezone=Asia/Shanghai
    username: your_username
    password: your_password
```

启动 Redis（默认 `127.0.0.1:6379`），然后启动后端：

```bash
cd backend
mvn spring-boot:run
# 默认激活 dev profile，接口地址 http://localhost:8080/api
```

**第三步：启动前端**

```bash
cd frontend
npm install
npm run dev
# 访问 http://localhost:3000
```

开发环境下 Vite 会把 `/api` 代理到 `http://127.0.0.1:8080`，无需额外配置跨域。

**一键启动（可选）**

```bash
# 根目录提供脚本时可封装为：
# Windows: start-dev.ps1
# macOS/Linux: start-dev.sh
# 内容依次为：启动 Redis -> 启动后端 -> 启动前端
```

---

## 2. 目录结构说明

```text
php-java/
├── frontend/                                # 前端工程（Vue 3 + Vite）
│   ├── src/
│   │   ├── api/                             # 接口层：按业务模块拆分 request 封装
│   │   │   ├── modules/                     # forum / product / lostfound / activity / rating ...
│   │   │   └── request.js                   # Axios 实例、Token 注入、统一错误处理
│   │   ├── assets/                          # 静态资源（样式、图标、图片）
│   │   ├── components/                      # 通用组件（导航、页脚、卡片、弹窗等）
│   │   ├── router/                          # Vue Router 路由与守卫
│   │   ├── stores/                          # Pinia 全局状态（用户、收藏、交互事件等）
│   │   ├── utils/                           # 工具函数（日期、格式化、校验、存储）
│   │   ├── views/                           # 页面级组件
│   │   │   ├── Home/                        # 首页
│   │   │   ├── Forum/                       # 论坛列表 / 详情
│   │   │   ├── SecondHand/                  # 二手交易列表
│   │   │   ├── ProductDetail/               # 商品详情 / 评价
│   │   │   ├── PublishProduct/              # 商品发布
│   │   │   ├── LostFound/                   # 失物招领列表 / 详情
│   │   │   ├── CampusActivities/            # 校园活动
│   │   │   ├── Chat/                        # 站内即时通讯
│   │   │   ├── Profile/                     # 个人中心
│   │   │   ├── Login/                       # 登录 / 注册
│   │   │   └── Admin/                       # 管理后台
│   │   └── main.js                          # 应用入口
│   ├── .env.development                     # 开发环境变量
│   ├── .env.production                      # 生产环境变量
│   └── vite.config.js                       # 代理、分包、构建配置
│
├── backend/                                 # 后端工程（Spring Boot 3）
│   ├── src/main/java/com/chaoyoung/
│   │   ├── ChaoyoungApplication.java        # 启动类
│   │   ├── annotation/                      # 自定义注解（限流等）
│   │   ├── aspect/                          # AOP 切面（日志、限流等横切逻辑）
│   │   ├── common/                          # 统一返回 Result、全局异常、通用常量
│   │   ├── config/                          # Security、WebMvc、MyBatis-Plus、Redis 配置
│   │   ├── controller/                      # REST 入口：只做参数接收与结果返回
│   │   ├── dto/                             # 请求 / 响应 DTO，隔离数据库实体
│   │   ├── entity/                          # 与数据表一一对应的持久化实体
│   │   ├── filter/                          # JWT、限流、XSS/SQL 注入过滤
│   │   ├── interceptor/                     # 防重放 Ticket 拦截器
│   │   ├── mapper/                          # MyBatis-Plus Mapper（含自定义 SQL）
│   │   ├── security/                        # JWT 工具、登录用户上下文
│   │   └── service/                         # 业务层：事务、库存扣减、票据服务
│   ├── src/main/resources/
│   │   ├── application.yml                  # 公共配置 + profile 激活
│   │   ├── application-dev.yml              # 开发环境（MySQL / Redis / SQL 日志）
│   │   ├── application-prod.yml             # 生产环境
│   │   └── db/
│   │       ├── init.sql                     # 建表脚本（对齐 Laravel Migration）
│   │       └── seed.sql                     # 种子数据（幂等，可重复执行）
│   ├── uploads/                             # 用户上传图片（按用户名分目录）
│   └── pom.xml
│
├── laravel/                                 # 重构前版本（PHP + Laravel）
│   ├── app/Http/Controllers/Api/            # 原 Controller
│   ├── app/Models/                          # Eloquent 模型
│   ├── database/migrations/                 # 表结构迁移
│   ├── database/seeders/                    # 种子数据
│   └── routes/api.php                       # 路由定义
│
└── README.md
```

**分层职责一句话说明**

| 目录 | 职责 |
| --- | --- |
| `controller` | 接收 HTTP 请求、参数校验、调用 Service，不写业务细节 |
| `service` | 编排业务规则与事务边界，例如库存扣减、审核状态流转 |
| `mapper` | 只负责 SQL 与数据访问，复杂查询在此收口 |
| `entity` | 数据库表的映射对象，字段与表结构一一对应 |
| `dto` | 面向前端的入参 / 出参对象，避免实体直接暴露 |
| `config` | 安全、跨域、静态资源、序列化等框架级配置 |
| `filter` / `interceptor` | 认证、限流、防重放等请求链路治理 |
| `common` | 统一响应、异常体系、常量，保证全站风格一致 |
| `security` | JWT 生成解析、当前登录用户上下文 |

---

## 3. 前端核心设计与工程化

### 3.1 技术选型

| 能力 | 方案 |
| --- | --- |
| 框架 | Vue 3（Composition API + `<script setup>`） |
| 构建 | Vite 4 |
| 状态管理 | Pinia |
| 路由 | Vue Router |
| 请求 | Axios（统一实例 + 拦截器） |
| 样式 | Bootstrap + SCSS + Font Awesome |
| 跨端适配 | 响应式布局 + 触控交互，兼容 PC / 移动端浏览器 |

前端按"页面（views）— 通用组件（components）— 接口（api）— 状态（stores）"拆分，接口层与页面解耦，便于同一套前端对接 Laravel / Spring Boot 两套后端。

### 3.2 开发环境与生产环境的区分原理

**环境变量文件**

```bash
frontend/
├── .env.development
└── .env.production
```

`.env.development`：

```ini
# 开发环境：请求走 Vite 代理，代理目标为本地 Spring Boot
VITE_API_BASE_URL=/
VITE_APP_TITLE='毕业作品-开发环境'
```

`.env.production`：

```ini
# 生产环境：直接请求线上域名
VITE_API_BASE_URL=https://www.cqh4u.cn
VITE_APP_TITLE='潮青行-毕业作品'
```

**Vite 如何识别与替换**

1. `npm run dev` 时 Vite 以 `development` 模式启动，自动加载 `.env.development`；
2. `npm run build` 时以 `production` 模式构建，自动加载 `.env.production`；
3. 只有 `VITE_` 前缀的变量会被注入到客户端代码；
4. 业务代码通过 `import.meta.env.VITE_API_BASE_URL` 读取，构建阶段会被静态替换为字面量，未使用的变量不会进入产物。

Axios 实例中统一读取：

```js
// src/api/request.js
const request = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000
})
```

**开发环境：Vite 代理解决跨域**

```js
// vite.config.js
server: {
  port: 3000,
  proxy: {
    '/api': {
      target: 'http://127.0.0.1:8080',
      changeOrigin: true
    }
  }
}
```

浏览器请求 `/api/...` 同源到 `localhost:3000`，由 Vite 转发到后端，浏览器侧不产生跨域，也不需要后端配置 CORS。

**生产环境：Nginx 反向代理**

```nginx
server {
    listen 80;
    server_name www.cqh4u.cn;

    root /var/www/chaoyoung/dist;
    index index.html;

    # SPA 路由回退
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 前端静态资源缓存
    location /static/ {
        expires 30d;
        add_header Cache-Control "public, max-age=2592000, immutable";
    }

    # 接口反向代理到 Spring Boot
    location /api/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # 用户上传图片（后端 /storage 映射到 uploads 目录）
    location /storage/ {
        proxy_pass http://127.0.0.1:8080;
    }
}
```

这样开发、生产两套环境共用同一份业务代码，仅通过环境变量与 Nginx 配置切换，避免环境差异导致的联调问题。

---

## 4. 核心重构对比：Laravel（PHP）到 Spring Boot（Java）

> 同一套业务先以 Laravel 实现，再用 AI 辅助迁移到 Spring Boot。迁移不是"翻译语法"，而是重新审视分层、类型、事务与安全边界。

### 4.1 语言特性对比

| 维度 | Laravel / PHP | Spring Boot / Java | 重构思考 |
| --- | --- | --- | --- |
| 类型系统 | 弱类型，数组即一切 | 强类型，实体 / DTO 明确 | 用 DTO 约束入参与出参，减少"字段拼错但运行不报错"的问题 |
| 数据模型 | Eloquent Model + `$fillable` | Entity + MyBatis-Plus 注解 | 字段映射从"隐式约定"变为"显式声明" |
| 时间处理 | Carbon 自动格式化 | `LocalDateTime` + Jackson 时区配置 | 统一 `Asia/Shanghai`，全站时间格式一致 |
| 空值处理 | 运行时判空 | `@NotNull` / `@Valid` + 全局异常 | 参数问题在进入业务前就被拦截 |
| 密码哈希 | `Hash::make`（bcrypt） | Spring Security `BCryptPasswordEncoder` | 同一数据库密码哈希可直接复用 |
| 逻辑删除 | SoftDeletes trait | MyBatis-Plus `logic-delete-field` | 删除语义统一，查询自动过滤 `deleted_at` |

**时间与序列化配置示例**

```yaml
spring:
  jackson:
    default-property-inclusion: non_null
    time-zone: Asia/Shanghai
    serialization:
      write-dates-as-timestamps: false
```

### 4.2 架构分层对比

| 环节 | Laravel 调用链 | Spring Boot 调用链 |
| --- | --- | --- |
| 入口 | `routes/api.php` | `@RestController` + `@RequestMapping` |
| 前置治理 | Middleware（`auth:sanctum`、限流、CORS） | Filter（JWT、限流、XSS/SQL 注入）+ Interceptor（防重放票据） |
| 参数接收 | `Request` / `FormRequest` | `@RequestBody` / `@RequestParam` + DTO + `@Valid` |
| 业务编排 | Controller 直接调用 Model / Service | Controller → Service（事务边界）→ Mapper |
| 数据访问 | Eloquent ORM | MyBatis-Plus Mapper + 自定义 SQL |
| 返回 | `response()->json()` | 统一 `Result<T>` |
| 异常 | `Handler` / `try-catch` | `@RestControllerAdvice` 全局异常处理 |

**Laravel 路由示例**

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/ratings',  [RatingController::class, 'store']);
});
```

**Spring Boot 对应实现**

```java
@RestController
@RequestMapping("/api")
public class RatingController {

    @PostMapping("/ratings")
    public Result<Rating> create(@RequestBody Map<String, Object> body) {
        // 认证由 JwtAuthenticationFilter 完成
        // 参数、权限、业务规则在 Service / 控制器内收口
    }
}
```

**统一返回体**

```java
public class Result<T> {
    private int code;
    private String msg;
    private T data;
    private Long total; // 仅列表接口使用，非空时序列化
}
```

统一返回 + 全局异常的价值：

- 前端只需判断 `code`，不用为每个接口写不同的错误分支；
- 业务异常（404/422/409）与系统异常（500）分离；
- 所有接口的字段风格一致，降低联调成本。

### 4.3 多环境配置对比

| 维度 | Laravel | Spring Boot |
| --- | --- | --- |
| 配置文件 | `.env` + `config/*.php` | `application.yml` + `application-{profile}.yml` |
| 环境切换 | 修改 `.env` / `APP_ENV` | `SPRING_PROFILES_ACTIVE=dev/prod` |
| 读取方式 | `env('DB_HOST')` | `${DB_HOST:默认值}` 占位符 |
| 敏感信息 | `.env` 不入库 | 生产通过环境变量覆盖，配置文件只留占位符 |

```yaml
# application.yml
spring:
  profiles:
    active: ${SPRING_PROFILES_ACTIVE:dev}
```

```bash
# 生产启动
SPRING_PROFILES_ACTIVE=prod JWT_SECRET=your-secret java -jar chaoyoung-backend.jar
```

### 4.4 数据库与迁移

| 维度 | Laravel | Spring Boot（当前） | 后续演进 |
| --- | --- | --- | --- |
| 建表 | `database/migrations` | `resources/db/init.sql` | 可平滑迁移到 Flyway |
| 种子数据 | `database/seeders` | `resources/db/seed.sql`（幂等） | 可按环境拆分 `seed-dev.sql` |
| 执行方式 | `php artisan migrate --seed` | 手动导入 SQL / 启动脚本 | `flyway:migrate` 纳入 CI |
| 约束 | 迁移文件版本化 | 表结构集中管理 | 版本化 + 回滚能力 |

当前的 `init.sql` / `seed.sql` 是**过渡方案**，优点是零依赖、便于开源用户直接导入；不足是缺少版本化。
后续引入 Flyway 后，可将每次表变更拆成 `V1__init.sql`、`V2__add_ratings.sql`，实现与 Laravel Migration 对等的可追踪演进。

---

## 5. 进阶架构设计：Redis 与锁机制

### 5.1 Redis 在项目中的定位

Redis 在本项目中承担三类职责：

| 场景 | Key 设计 | 作用 |
| --- | --- | --- |
| 接口限流 | `rate_limit:{ip}:{window}` | 单 IP 每分钟 60 次，超出返回 429，保护后端 |
| 防重放票据 | `ticket:{userId}:{nonce}` | 写请求携带一次性 `X-Ticket`，TTL 60 秒，防止重复提交与请求重放 |
| 商品库存缓存 | `chaoyoung:product:stock:{id}` | 缓存热点库存，事务提交后删除，保证读到的不是旧值 |

**设计原则**

1. Redis 只做**加速与治理**，权威数据始终以 MySQL 为准；
2. 缓存必须设置 TTL，并考虑空值缓存，避免穿透与雪崩；
3. 缓存失效放在事务 `afterCommit`，避免"事务未提交先删缓存"导致旧值被回填；
4. Redis 异常不影响主流程：库存缓存删除失败只记日志，不回滚订单事务。

### 5.2 悲观锁：库存扣减的并发安全

校园项目的真实并发并不高，但库存扣减是典型的**超卖风险点**。为了预演高并发场景（如二手商品限时秒杀），在 `ProductStockService` 中采用数据库悲观锁方案：

```java
// ProductMapper.java
@Select("SELECT * FROM products WHERE id = #{id} AND deleted_at IS NULL FOR UPDATE")
Product selectByIdForUpdate(@Param("id") Long id);
```

```java
// ProductStockService.java
@Transactional(rollbackFor = Exception.class)
public Map<String, Object> deductStock(Long productId, Integer quantity) {
    // 1. 事务内锁定商品行，其他扣减事务在此阻塞
    Product product = productMapper.selectByIdForUpdate(productId);

    // 2. 校验库存
    int currentStock = product.getStock() == null ? 0 : product.getStock();
    if (currentStock < quantity) {
        throw new BusinessException(400, "库存不足");
    }

    // 3. 条件更新，数据库层面再次兜底
    int affected = productMapper.decreaseStock(productId, quantity);
    if (affected == 0) {
        throw new BusinessException(409, "库存扣减失败，请重试");
    }

    // 4. 事务提交后再删除 Redis 库存缓存
    evictStockCacheAfterCommit(productId);

    return Map.of("productId", productId, "remaining", currentStock - quantity);
}
```

**执行流程**

```text
请求进入
  → 开启事务
  → SELECT ... FOR UPDATE 锁定商品行（行锁）
  → 校验库存是否充足
  → UPDATE stock = stock - quantity AND stock >= quantity
  → 事务提交
  → afterCommit 删除 Redis 库存缓存
  → 返回剩余库存
```

**为什么选择悲观锁而不是乐观锁**

| 方案 | 适用场景 | 本项目选择理由 |
| --- | --- | --- |
| 悲观锁（`FOR UPDATE`） | 冲突概率高、扣减链路短 | 实现直观、强一致，事务内串行化，杜绝超卖 |
| 乐观锁（version / CAS） | 冲突概率低、读多写少 | 需要重试逻辑，秒杀场景下失败率上升 |
| Redis 预扣（Lua） | 超高并发、可接受最终一致 | 需要补偿机制，复杂度更高 |

本项目采用**悲观锁 + 条件更新双保险**：

- `FOR UPDATE` 保证同一商品的扣减串行执行；
- `stock >= quantity` 的条件更新保证即使锁失效也不会扣成负数；
- `@Transactional` 保证锁在事务结束后才释放；
- Redis 缓存只做读加速，扣减完成后再失效，避免脏读。

**业务价值**

- 对校园二手交易：避免"最后一件商品被两个人同时买走"；
- 对技术展示：完整呈现"事务 + 行锁 + 缓存一致性"的落地方式；
- 对后续扩展：可在此基础上接入 Redis Lua 预扣，演进为高并发秒杀方案。

---

## 6. AI 辅助开发心得

### 6.1 AI 在重构中的角色

重构过程中使用 Claude / Cline / DeepSeek 等工具辅助完成：

- 将 Laravel Controller 的 CRUD 逻辑转换为 Spring Boot 的 Controller / Service / Mapper 骨架；
- 批量生成 Entity、DTO、Mapper 的样板代码；
- 对照 Laravel Migration 生成 `init.sql`；
- 解释 PHP 与 Java 在日期、空值、数组结构上的差异；
- 辅助排查接口 404/422/500 等联调问题。

AI 显著降低了"重复劳动"的成本，但**不能替代架构决策与质量把关**。

### 6.2 AI 生成 + 人工审查：我实际做的四件事

**（1）统一全局异常与返回格式**

AI 初始生成的代码存在三种返回风格：直接返回实体、返回 `Map`、抛裸异常。
我手动收敛为统一 `Result<T>` + `@RestControllerAdvice`：

- 业务异常 → `BusinessException(code, msg)`
- 参数校验异常 → 422 + 字段级错误
- 认证失败 → 401
- 无权限 → 403
- 未捕获异常 → 500，日志记录堆栈，响应不暴露内部细节

**（2）统一认证与安全边界**

AI 生成的接口默认"能跑就行"，我补充了：

- JWT 过滤器统一解析 Token，注入当前用户上下文；
- 写请求统一校验一次性 `X-Ticket`，防重放；
- 上传接口单独放行票据校验（前端使用原生 `fetch`，不携带 Ticket）；
- 管理接口统一 `ROLE_ADMIN` 权限；
- 图片上传限制类型与大小，保存到 `uploads/images/{用户名}/`，与 Laravel 的存储结构保持一致。

**（3）修正 AI 的"结构混乱"**

AI 常见问题及人工修正：

| 问题 | 修正方式 |
| --- | --- |
| Controller 里直接写 SQL / 业务 | 下沉到 Service / Mapper，Controller 只做参数与返回 |
| 同一业务多个相似接口 | 合并路由，统一分页与筛选参数 |
| 字段命名前后端不一致（`cover_img` vs `coverImg`） | DTO 使用 `@JsonAlias` 兼容，前端字段逐步统一 |
| 分页返回结构不统一 | 统一 `list/total` 或 Laravel 风格分页器 |
| 缺少事务边界 | 库存、审核、计数变更加 `@Transactional` |
| 缓存删除时机错误 | 改为 `afterCommit` 删除，避免旧值回填 |

**（4）用真实请求验证，而不是"看起来能跑"**

每个模块完成后，用真实账号走完整链路验证：

- 登录 → 获取 Ticket → 发布 → 审核 → 列表 → 详情 → 删除；
- 校验未登录 401、普通用户访问管理接口 403、重复评价 400、库存不足 400；
- 种子脚本重复执行验证幂等性。

### 6.3 我的结论

> AI 适合做"代码的第一稿"，人负责"架构的第二稿"。
>
> 真正的工程能力体现在：能否定义统一的规范、识别 AI 代码的边界问题、并用测试与真实请求闭环验证。

---

## 7. 后续规划

- [ ] 引入 Flyway，替换手写 `init.sql` / `seed.sql`，实现数据库版本化；
- [ ] 增加 Docker Compose，一键拉起 MySQL + Redis + 后端 + 前端；
- [ ] 补充 UniApp 工程，实现小程序 / App 端复用现有 API；
- [ ] 商品搜索接入 Elasticsearch，支持分词与高亮；
- [ ] 热点商品 / 分类列表接入 Redis 缓存，增加空值缓存与随机 TTL；
- [ ] 增加 JUnit + MockMvc 集成测试，覆盖认证、审核、库存扣减等核心链路；
- [ ] 接入对象存储（OSS / COS），替换本地 `uploads` 目录。

---

## License

本项目仅用于学习、毕业设计与面试展示，采用 MIT License。

> 如果这个项目对你有帮助，欢迎 Star 支持。
