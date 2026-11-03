-- 潮青行 Java 后端种子数据（MySQL 8.0）
-- 数据库：laravel_graduation
-- 执行方式：mysql --default-character-set=utf8mb4 --database=laravel_graduation < seed.sql
-- 说明：所有 INSERT 均带存在性判断，可重复执行；图片统一使用在线图片（picsum.photos）。

SET NAMES utf8mb4;

-- ============================================================
-- 1. 演示用户（密码 123456，BCrypt，与 init.sql 中 admin 使用同一哈希）
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM users WHERE username = 'xiaoming');
INSERT INTO users (name, username, email, avatar, bio, password, role, topics_count, email_verified_at)
SELECT '小明', 'xiaoming', 'xiaoming@example.com',
       'https://picsum.photos/200/200?random=21',
       '喜欢逛校园的每一个角落，也喜欢在论坛分享日常。',
       '$2b$10$BlWG8SZwZ6kHTFoeA8YVw.BK5WWO55lPj1ygwIQR0PqduPkHQeWJe', 'user', 0, NOW()
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM users WHERE username = 'xiaohong');
INSERT INTO users (name, username, email, avatar, bio, password, role, topics_count, email_verified_at)
SELECT '小红', 'xiaohong', 'xiaohong@example.com',
       'https://picsum.photos/200/200?random=22',
       '摄影爱好者，经常在校园里记录四季。',
       '$2b$10$BlWG8SZwZ6kHTFoeA8YVw.BK5WWO55lPj1ygwIQR0PqduPkHQeWJe', 'user', 0, NOW()
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM users WHERE username = 'xiaogang');
INSERT INTO users (name, username, email, avatar, bio, password, role, topics_count, email_verified_at)
SELECT '小刚', 'xiaogang', 'xiaogang@example.com',
       'https://picsum.photos/200/200?random=23',
       '爱运动、爱学习，欢迎一起交流进步。',
       '$2b$10$BlWG8SZwZ6kHTFoeA8YVw.BK5WWO55lPj1ygwIQR0PqduPkHQeWJe', 'user', 0, NOW()
FROM DUAL WHERE @exists = 0;

-- ============================================================
-- 2. 校园论坛：话题
-- category_id：1 校园趣事 / 2 校园闲聊 / 3 学习交流 / 4 创意分享 / 5 求职招聘
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM topics WHERE title = '校园里最美的角落在哪里？');
INSERT INTO topics (title, content, user_id, category_id, likes_count, favorites_count, status, created_at)
SELECT '校园里最美的角落在哪里？',
       '大家觉得咱们校园里最美的地方是哪里呢？我觉得图书馆后面的小花园很不错！春天的时候百花齐放，夏天的时候绿树成荫，秋天的时候落叶满地，冬天的时候银装素裹，每个季节都有不同的美景！',
       (SELECT id FROM users WHERE username = 'xiaoming'), 1, 18, 4, 'approved', NOW() - INTERVAL 6 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM topics WHERE title = '推荐一下食堂的招牌菜');
INSERT INTO topics (title, content, user_id, category_id, likes_count, favorites_count, status, created_at)
SELECT '推荐一下食堂的招牌菜',
       '新同学刚入学，想了解一下食堂有什么好吃的，大家推荐一下吧！',
       (SELECT id FROM users WHERE username = 'xiaohong'), 2, 12, 2, 'approved', NOW() - INTERVAL 5 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM topics WHERE title = '期末考试复习资料分享');
INSERT INTO topics (title, content, user_id, category_id, likes_count, favorites_count, status, created_at)
SELECT '期末考试复习资料分享',
       '马上就要期末考试了，大家有什么好的复习资料可以分享吗？',
       (SELECT id FROM users WHERE username = 'xiaogang'), 3, 24, 6, 'approved', NOW() - INTERVAL 4 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM topics WHERE title = '学校附近有什么好玩的地方？');
INSERT INTO topics (title, content, user_id, category_id, likes_count, favorites_count, status, created_at)
SELECT '学校附近有什么好玩的地方？',
       '周末想出去放松一下，学校附近有什么好玩的地方吗？',
       (SELECT id FROM users WHERE username = 'xiaoming'), 2, 9, 1, 'approved', NOW() - INTERVAL 3 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM topics WHERE title = '如何提高学习效率？');
INSERT INTO topics (title, content, user_id, category_id, likes_count, favorites_count, status, created_at)
SELECT '如何提高学习效率？',
       '最近感觉学习效率很低，大家有什么好的方法可以分享吗？',
       (SELECT id FROM users WHERE username = 'xiaohong'), 3, 16, 3, 'approved', NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

-- ============================================================
-- 3. 校园论坛：回复（每个话题 3 条）
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '校园里最美的角落在哪里？') AND content = '我也觉得图书馆后面的小花园很美，尤其是春天的时候，樱花盛开，非常漂亮！');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaohong'),
       '我也觉得图书馆后面的小花园很美，尤其是春天的时候，樱花盛开，非常漂亮！', 'approved', NOW() - INTERVAL 5 DAY
FROM topics t WHERE t.title = '校园里最美的角落在哪里？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '校园里最美的角落在哪里？') AND content = '我喜欢学校的湖心亭，那里很安静，适合看书和思考。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaogang'),
       '我喜欢学校的湖心亭，那里很安静，适合看书和思考。', 'approved', NOW() - INTERVAL 4 DAY
FROM topics t WHERE t.title = '校园里最美的角落在哪里？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '校园里最美的角落在哪里？') AND content = '操场边那排银杏，秋天特别好看。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaoming'),
       '操场边那排银杏，秋天特别好看。', 'approved', NOW() - INTERVAL 3 DAY
FROM topics t WHERE t.title = '校园里最美的角落在哪里？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '推荐一下食堂的招牌菜') AND content = '食堂二楼的红烧肉很不错，推荐大家去试试！');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaoming'),
       '食堂二楼的红烧肉很不错，推荐大家去试试！', 'approved', NOW() - INTERVAL 4 DAY
FROM topics t WHERE t.title = '推荐一下食堂的招牌菜' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '推荐一下食堂的招牌菜') AND content = '食堂一楼的早餐不错，种类很多，价格也便宜。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaogang'),
       '食堂一楼的早餐不错，种类很多，价格也便宜。', 'approved', NOW() - INTERVAL 3 DAY
FROM topics t WHERE t.title = '推荐一下食堂的招牌菜' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '推荐一下食堂的招牌菜') AND content = '三楼靠窗的麻辣烫窗口也值得一试。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaohong'),
       '三楼靠窗的麻辣烫窗口也值得一试。', 'approved', NOW() - INTERVAL 2 DAY
FROM topics t WHERE t.title = '推荐一下食堂的招牌菜' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '期末考试复习资料分享') AND content = '复习资料的话，可以去图书馆借一些历年真题，很有帮助！');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaohong'),
       '复习资料的话，可以去图书馆借一些历年真题，很有帮助！', 'approved', NOW() - INTERVAL 3 DAY
FROM topics t WHERE t.title = '期末考试复习资料分享' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '期末考试复习资料分享') AND content = '我把整理好的笔记放到共享盘了，需要的同学可以留言。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaoming'),
       '我把整理好的笔记放到共享盘了，需要的同学可以留言。', 'approved', NOW() - INTERVAL 2 DAY
FROM topics t WHERE t.title = '期末考试复习资料分享' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '期末考试复习资料分享') AND content = '建议按章节做思维导图，效率会高很多。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaogang'),
       '建议按章节做思维导图，效率会高很多。', 'approved', NOW() - INTERVAL 1 DAY
FROM topics t WHERE t.title = '期末考试复习资料分享' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '学校附近有什么好玩的地方？') AND content = '学校附近有一个湿地公园，风景很好，适合周末去散步。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaogang'),
       '学校附近有一个湿地公园，风景很好，适合周末去散步。', 'approved', NOW() - INTERVAL 2 DAY
FROM topics t WHERE t.title = '学校附近有什么好玩的地方？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '学校附近有什么好玩的地方？') AND content = '学校附近有一个电影院，经常有学生优惠，推荐大家去看看！');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaoming'),
       '学校附近有一个电影院，经常有学生优惠，推荐大家去看看！', 'approved', NOW() - INTERVAL 1 DAY
FROM topics t WHERE t.title = '学校附近有什么好玩的地方？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '学校附近有什么好玩的地方？') AND content = '东门外的小吃街也很热闹，晚上去刚刚好。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaohong'),
       '东门外的小吃街也很热闹，晚上去刚刚好。', 'approved', NOW()
FROM topics t WHERE t.title = '学校附近有什么好玩的地方？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '如何提高学习效率？') AND content = '我觉得学习效率低的话，可以试试番茄工作法，效果不错！');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaohong'),
       '我觉得学习效率低的话，可以试试番茄工作法，效果不错！', 'approved', NOW() - INTERVAL 1 DAY
FROM topics t WHERE t.title = '如何提高学习效率？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '如何提高学习效率？') AND content = '我觉得学习的时候应该远离手机，这样可以提高注意力。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaogang'),
       '我觉得学习的时候应该远离手机，这样可以提高注意力。', 'approved', NOW()
FROM topics t WHERE t.title = '如何提高学习效率？' AND @exists = 0;

SET @exists := (SELECT COUNT(*) FROM replies WHERE topic_id = (SELECT id FROM topics WHERE title = '如何提高学习效率？') AND content = '每天给自己定三个小目标，完成后再奖励自己。');
INSERT INTO replies (topic_id, user_id, content, status, created_at)
SELECT t.id, (SELECT id FROM users WHERE username = 'xiaoming'),
       '每天给自己定三个小目标，完成后再奖励自己。', 'approved', NOW()
FROM topics t WHERE t.title = '如何提高学习效率？' AND @exists = 0;

-- 同步话题回复数与用户发帖数
UPDATE topics t
SET t.replies_count = (SELECT COUNT(*) FROM replies r WHERE r.topic_id = t.id AND r.deleted_at IS NULL)
WHERE t.title IN ('校园里最美的角落在哪里？', '推荐一下食堂的招牌菜', '期末考试复习资料分享',
                  '学校附近有什么好玩的地方？', '如何提高学习效率？');

UPDATE users u
SET u.topics_count = (SELECT COUNT(*) FROM topics t WHERE t.user_id = u.id AND t.deleted_at IS NULL)
WHERE u.username IN ('xiaoming', 'xiaohong', 'xiaogang');

-- ============================================================
-- 4. 失物招领（5 条寻物启事 + 5 条失物招领）
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '丢失蓝色书包一个');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '丢失蓝色书包一个', 'lost', 'looking', '图书馆三楼自习室',
       'https://picsum.photos/800/600?random=1',
       '书包是蓝色的，上面有Hello Kitty图案，里面有一本高等数学教材和一个红色笔袋。最后一次见到是在图书馆三楼靠窗的位置。',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 12 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '丢失小米手环7');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '丢失小米手环7', 'lost', 'looking', '体育馆篮球场',
       'https://picsum.photos/800/600?random=2',
       '黑色小米手环7，表带是蓝色的，最后一次使用是在体育馆打篮球的时候。如果有人捡到，请联系我，必有重谢！',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 10 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '丢失身份证');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '丢失身份证', 'lost', 'looking', '食堂二楼',
       'https://picsum.photos/800/600?random=3',
       '姓名：张三，身份证号码：110101********1234，最后一次使用是在食堂二楼买饭的时候。如果捡到请联系，非常感谢！',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 8 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '丢失笔记本电脑');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '丢失笔记本电脑', 'lost', 'looking', '教学楼302教室',
       'https://picsum.photos/800/600?random=4',
       '银色联想笔记本电脑，外壳上有贴纸，最后一次使用是在302教室上课的时候。请捡到的同学联系我，非常重要！',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 6 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '丢失雨伞');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '丢失雨伞', 'lost', 'looking', '图书馆门口',
       'https://picsum.photos/800/600?random=5',
       '黑色折叠雨伞，上面有学校的logo，最后一次使用是在图书馆门口避雨的时候忘记拿了。',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 4 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '捡到钱包一个');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '捡到钱包一个', 'found', 'found', '食堂一楼',
       'https://picsum.photos/800/600?random=6',
       '在食堂一楼捡到一个黑色钱包，里面有一些现金和几张银行卡，还有一张学生证。请失主联系我领取。',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 9 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '捡到手机一部');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '捡到手机一部', 'found', 'found', '图书馆自习室',
       'https://picsum.photos/800/600?random=7',
       '在图书馆自习室捡到一部蓝色iPhone 13，屏幕有保护膜，背面有手机壳。请失主联系我，提供手机型号和锁屏密码领取。',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 7 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '捡到耳机一副');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '捡到耳机一副', 'found', 'found', '教学楼201教室',
       'https://picsum.photos/800/600?random=8',
       '在教学楼201教室捡到一副白色AirPods Pro，装在充电盒里。请失主联系我领取。',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 5 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '捡到眼镜一副');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '捡到眼镜一副', 'found', 'found', '图书馆阅览区',
       'https://picsum.photos/800/600?random=9',
       '在图书馆阅览区捡到一副黑色框架眼镜，镜片是近视镜片。请失主联系我，提供度数和镜框形状领取。',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 3 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM lost_found WHERE title = '捡到校园卡一张');
INSERT INTO lost_found (title, type, status, location, cover_img, description, user_id, moderation_status, created_at)
SELECT '捡到校园卡一张', 'found', 'found', '学校门口',
       'https://picsum.photos/800/600?random=10',
       '在学校门口捡到一张校园卡，姓名：李四，学号：20210001，学院：计算机学院。请失主联系我领取。',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 1 DAY
FROM DUAL WHERE @exists = 0;

-- ============================================================
-- 5. 物品展示（二手商品）
-- category_id：1 数码产品 / 2 生活用品 / 3 学习资料 / 4 服饰鞋包 / 5 其他
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM products WHERE title = 'iPhone 13 Pro 128GB');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT 'iPhone 13 Pro 128GB', 1, 5999.00, 1,
       'https://picsum.photos/400/300?random=1',
       '9成新，无划痕，配件齐全，保修期内',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 10 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = 'AirPods Pro 2代');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT 'AirPods Pro 2代', 1, 1299.00, 2,
       'https://picsum.photos/400/300?random=2',
       '全新未拆封，支持验货',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 9 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = '小米空气净化器');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT '小米空气净化器', 2, 399.00, 1,
       'https://picsum.photos/400/300?random=3',
       '使用半年，功能正常，因搬家转让',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 8 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = '大学英语四级词汇书');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT '大学英语四级词汇书', 3, 20.00, 3,
       'https://picsum.photos/400/300?random=4',
       '几乎全新，无笔记，送听力光盘',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 7 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = 'Nike Air Max 270');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT 'Nike Air Max 270', 4, 499.00, 1,
       'https://picsum.photos/400/300?random=5',
       '尺码42，穿过几次，鞋底无磨损',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 6 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = 'MacBook Pro 13英寸 M1');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT 'MacBook Pro 13英寸 M1', 1, 7999.00, 1,
       'https://picsum.photos/400/300?random=6',
       '2021款，8GB+256GB，电池循环次数低',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 5 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = '宜家书桌');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT '宜家书桌', 2, 199.00, 1,
       'https://picsum.photos/400/300?random=7',
       '白色，120x60cm，可拆卸，自提',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 4 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = '考研政治复习资料');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT '考研政治复习资料', 3, 50.00, 2,
       'https://picsum.photos/400/300?random=8',
       '全套资料，包含历年真题和模拟题',
       (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW() - INTERVAL 3 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = 'Adidas运动外套');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT 'Adidas运动外套', 4, 299.00, 1,
       'https://picsum.photos/400/300?random=9',
       'L码，黑色，保暖性好',
       (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM products WHERE title = '吉他');
INSERT INTO products (title, category_id, price, stock, cover_img, description, user_id, status, created_at)
SELECT '吉他', 5, 699.00, 1,
       'https://picsum.photos/400/300?random=10',
       '民谣吉他，送琴包和拨片',
       (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW() - INTERVAL 1 DAY
FROM DUAL WHERE @exists = 0;

-- ============================================================
-- 6. 校园活动
-- category_id：1 学术活动 / 2 文体活动 / 3 志愿服务 / 4 社团活动 / 5 其他活动
-- ============================================================
SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '校园歌手大赛');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '校园歌手大赛',
       'https://picsum.photos/800/500?random=11',
       NOW() + INTERVAL 3 DAY, NOW() + INTERVAL 3 DAY + INTERVAL 3 HOUR,
       '用歌声点亮校园，欢迎热爱音乐的同学报名参加，现场还有惊喜奖品。',
       2, (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '人工智能前沿讲座');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '人工智能前沿讲座',
       'https://picsum.photos/800/500?random=12',
       NOW() + INTERVAL 5 DAY, NOW() + INTERVAL 5 DAY + INTERVAL 2 HOUR,
       '邀请计算机学院老师分享大模型与智能体技术的最新进展，欢迎各专业同学参加。',
       1, (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '图书馆志愿整理活动');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '图书馆志愿整理活动',
       'https://picsum.photos/800/500?random=13',
       NOW() + INTERVAL 7 DAY, NOW() + INTERVAL 7 DAY + INTERVAL 4 HOUR,
       '协助图书馆整理书架与图书归位，参与者可获得志愿服务时长证明。',
       3, (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '摄影社秋季采风');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '摄影社秋季采风',
       'https://picsum.photos/800/500?random=14',
       NOW() + INTERVAL 9 DAY, NOW() + INTERVAL 9 DAY + INTERVAL 5 HOUR,
       '走进校园与湿地公园，用镜头记录秋天的色彩，欢迎摄影爱好者一起同行。',
       4, (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '迎新篮球友谊赛');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '迎新篮球友谊赛',
       'https://picsum.photos/800/500?random=15',
       NOW() + INTERVAL 11 DAY, NOW() + INTERVAL 11 DAY + INTERVAL 3 HOUR,
       '新生与老生组队对抗，欢迎到场加油，现场报名即可参加趣味投篮。',
       2, (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '考研经验分享会');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '考研经验分享会',
       'https://picsum.photos/800/500?random=16',
       NOW() + INTERVAL 13 DAY, NOW() + INTERVAL 13 DAY + INTERVAL 2 HOUR,
       '邀请已上岸的学长学姐分享备考规划、院校选择与复试经验。',
       1, (SELECT id FROM users WHERE username = 'xiaogang'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '校园环保公益跑');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '校园环保公益跑',
       'https://picsum.photos/800/500?random=17',
       NOW() + INTERVAL 15 DAY, NOW() + INTERVAL 15 DAY + INTERVAL 2 HOUR,
       '边跑步边清理校园垃圾，完成路线即可获得公益纪念徽章。',
       3, (SELECT id FROM users WHERE username = 'xiaoming'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;

SET @exists := (SELECT COUNT(*) FROM activities WHERE title = '动漫社主题漫展');
INSERT INTO activities (title, poster, start_time, end_time, description, category_id, user_id,
                        moderation_status, moderated_at, moderated_by, created_at)
SELECT '动漫社主题漫展',
       'https://picsum.photos/800/500?random=18',
       NOW() + INTERVAL 18 DAY, NOW() + INTERVAL 18 DAY + INTERVAL 6 HOUR,
       '社团作品展示、Cosplay 互动与同人周边交换，欢迎全校同学参与。',
       4, (SELECT id FROM users WHERE username = 'xiaohong'), 'approved', NOW(),
       (SELECT id FROM users WHERE username = 'admin'), NOW() - INTERVAL 2 DAY
FROM DUAL WHERE @exists = 0;
