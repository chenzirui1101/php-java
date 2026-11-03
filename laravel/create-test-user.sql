-- 创建测试用户
INSERT INTO users (name, username, email, password, created_at, updated_at)
VALUES (
    '测试买家',
    'testbuyer',
    'testbuyer@example.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- 密码: 123456
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE
    name = '测试买家',
    email = 'testbuyer@example.com',
    updated_at = NOW();

-- 查看用户数据
SELECT id, name, username, email FROM users WHERE username = 'testbuyer';
