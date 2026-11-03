-- 潮青行 Java 后端初始化脚本（MySQL 8.0）
-- 数据库：laravel_graduation
-- 执行方式：mysql --database=laravel_graduation < init.sql

SET NAMES utf8mb4;

-- ============================================================
-- 1. users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name              VARCHAR(255)    NOT NULL DEFAULT '',
    username          VARCHAR(191)    NOT NULL,
    email             VARCHAR(255)    NOT NULL,
    email_verified_at TIMESTAMP       NULL DEFAULT NULL,
    avatar            VARCHAR(255)    DEFAULT NULL COMMENT '用户头像',
    bio               TEXT            NULL COMMENT '个人简介',
    topics_count      INT             NOT NULL DEFAULT 0,
    password          VARCHAR(255)    NOT NULL,
    role              VARCHAR(20)     NOT NULL DEFAULT 'user' COMMENT 'user / admin',
    remember_token    VARCHAR(100)    DEFAULT NULL,
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_username (username),
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_role (role)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户表';

-- ============================================================
-- 2. products
-- ============================================================
CREATE TABLE IF NOT EXISTS products (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(255)    NOT NULL DEFAULT '',
    category_id INT             NOT NULL DEFAULT 0 COMMENT '1数码 2生活 3学习 4服饰 5其他',
    price       DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    stock       INT             NOT NULL DEFAULT 0 COMMENT '库存',
    cover_img   VARCHAR(255)    DEFAULT NULL,
    description TEXT            NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    status      VARCHAR(20)     NOT NULL DEFAULT 'pending' COMMENT 'pending / approved / rejected',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_products_user (user_id),
    KEY idx_products_status (status),
    KEY idx_products_category (category_id),
    CONSTRAINT fk_products_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '二手商品表';

-- ============================================================
-- 3. topics
-- ============================================================
CREATE TABLE IF NOT EXISTS topics (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title           VARCHAR(100)    NOT NULL DEFAULT '',
    content         TEXT            NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    category_id     BIGINT UNSIGNED DEFAULT NULL,
    replies_count   INT             NOT NULL DEFAULT 0,
    likes_count     INT             NOT NULL DEFAULT 0,
    favorites_count INT             NOT NULL DEFAULT 0,
    status          VARCHAR(20)     NOT NULL DEFAULT 'pending' COMMENT 'pending / approved / rejected',
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at      TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_topics_user (user_id),
    KEY idx_topics_status (status),
    KEY idx_topics_category (category_id),
    CONSTRAINT fk_topics_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '论坛话题表';

-- ============================================================
-- 4. replies
-- ============================================================
CREATE TABLE IF NOT EXISTS replies (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    topic_id   BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    content    TEXT            NOT NULL,
    status     VARCHAR(20)     NOT NULL DEFAULT 'pending' COMMENT 'pending / approved / rejected',
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_replies_topic (topic_id),
    KEY idx_replies_user (user_id),
    KEY idx_replies_status (status),
    CONSTRAINT fk_replies_topic FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '话题评论表';

-- ============================================================
-- 5. lost_found
-- ============================================================
CREATE TABLE IF NOT EXISTS lost_found (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title             VARCHAR(255)    NOT NULL DEFAULT '',
    type              VARCHAR(20)     NOT NULL COMMENT 'lost 寻物启事 / found 失物招领',
    status            VARCHAR(20)     NOT NULL COMMENT 'looking 寻找中 / found 已找到',
    location          VARCHAR(255)    NOT NULL DEFAULT '',
    cover_img         VARCHAR(255)    DEFAULT NULL,
    description       TEXT            NOT NULL,
    user_id           BIGINT UNSIGNED NOT NULL,
    moderation_status VARCHAR(20)     NOT NULL DEFAULT 'pending' COMMENT 'pending / approved / rejected',
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_lost_found_user (user_id),
    KEY idx_lost_found_type (type),
    KEY idx_lost_found_moderation (moderation_status),
    CONSTRAINT fk_lost_found_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '失物招领表';

-- ============================================================
-- 6. conversations
-- ============================================================
CREATE TABLE IF NOT EXISTS conversations (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id        BIGINT UNSIGNED DEFAULT NULL,
    user_id           BIGINT UNSIGNED NOT NULL COMMENT '买家',
    seller_id         BIGINT UNSIGNED NOT NULL COMMENT '卖家',
    last_message      TEXT            NULL,
    last_message_time TIMESTAMP       NULL DEFAULT NULL,
    unread_count      INT             NOT NULL DEFAULT 0,
    resource_type     VARCHAR(20)     DEFAULT NULL COMMENT 'product / lost_found / forum',
    resource_id       BIGINT UNSIGNED DEFAULT NULL,
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at        TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_conversations_resource (user_id, resource_type, resource_id),
    KEY idx_conversations_seller (seller_id),
    KEY idx_conversations_product (product_id),
    CONSTRAINT fk_conversations_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_conversations_seller FOREIGN KEY (seller_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会话表';

-- ============================================================
-- 7. activities（校园活动，对齐 Laravel activities 表）
-- ============================================================
CREATE TABLE IF NOT EXISTS activities (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title             VARCHAR(255)    NOT NULL,
    poster            LONGTEXT        NOT NULL,
    start_time        DATETIME        NOT NULL,
    end_time          DATETIME        NOT NULL,
    description       TEXT            NULL,
    category_id       BIGINT UNSIGNED NOT NULL,
    user_id           BIGINT UNSIGNED NOT NULL,
    moderation_status VARCHAR(20)     NOT NULL DEFAULT 'pending' COMMENT 'pending / approved / rejected',
    moderated_at      TIMESTAMP       NULL DEFAULT NULL,
    moderated_by      BIGINT UNSIGNED NULL,
    reject_reason     TEXT            NULL,
    created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activities_moderation (moderation_status),
    KEY idx_activities_category (category_id),
    KEY idx_activities_user (user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '校园活动表';

-- ============================================================
-- 超级管理员初始化（账号 admin / 密码 123456，BCrypt）
-- ============================================================
INSERT INTO users (name, username, email, password, role, topics_count)
SELECT '超级管理员', 'admin', 'admin@chaoyoung.local',
       '$2b$10$BlWG8SZwZ6kHTFoeA8YVw.BK5WWO55lPj1ygwIQR0PqduPkHQeWJe',
       'admin', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

-- ============================================================
-- 论坛点赞 / 收藏 / 关注（对齐 Laravel 迁移）
-- ============================================================
CREATE TABLE IF NOT EXISTS forum_likes (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    topic_id    BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_forum_likes_user_topic (user_id, topic_id),
    KEY idx_forum_likes_topic (topic_id),
    CONSTRAINT fk_forum_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_forum_likes_topic FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '话题点赞表';

CREATE TABLE IF NOT EXISTS forum_favorites (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    topic_id    BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_forum_favorites_user_topic (user_id, topic_id),
    KEY idx_forum_favorites_topic (topic_id),
    CONSTRAINT fk_forum_favorites_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_forum_favorites_topic FOREIGN KEY (topic_id) REFERENCES topics (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '话题收藏表';

CREATE TABLE IF NOT EXISTS follows (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    follower_id BIGINT UNSIGNED NOT NULL,
    followed_id BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_follows_follower_followed (follower_id, followed_id),
    KEY idx_follows_followed (followed_id),
    CONSTRAINT fk_follows_follower FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_follows_followed FOREIGN KEY (followed_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户关注表';

-- ============================================================
-- 商品收藏（对齐 Laravel create_favorites_table）
-- ============================================================
CREATE TABLE IF NOT EXISTS favorites (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_favorites_user_product (user_id, product_id),
    KEY idx_favorites_product (product_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商品收藏表';

-- ============================================================
-- 商品评价（对齐 Laravel create_ratings_table）
-- ============================================================
CREATE TABLE IF NOT EXISTS ratings (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    rating      TINYINT UNSIGNED NOT NULL,
    comment     TEXT            NULL,
    status      VARCHAR(20)     NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_ratings_user_product (user_id, product_id),
    KEY idx_ratings_product (product_id),
    KEY idx_ratings_status (status),
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '商品评价表';
