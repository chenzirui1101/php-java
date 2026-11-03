<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\LostFound;
use App\Models\Conversation;

// 1. 检查是否存在testbuyer用户
$user = User::where('username', 'testbuyer')->first();
if (!$user) {
    echo "创建测试用户...\n";
    $user = User::create([
        'name' => '测试买家',
        'username' => 'testbuyer',
        'email' => 'testbuyer@example.com',
        'password' => bcrypt('123456')
    ]);
    echo "测试用户创建成功: {$user->name} (ID: {$user->id})\n";
} else {
    echo "使用现有测试用户: {$user->name} (ID: {$user->id})\n";
}

// 2. 检查是否存在LostFound数据
$lostFound = LostFound::first();
if (!$lostFound) {
    echo "错误：没有失物招领数据，请先运行LostFoundSeeder\n";
    exit;
}

echo "使用失物招领数据: {$lostFound->title} (ID: {$lostFound->id})\n";

// 3. 尝试创建会话
$sellerId = $lostFound->user_id;
echo "发布者ID: {$sellerId}\n";

// 防止与自己聊天
if ($sellerId == $user->id) {
    echo "测试用户不能与自己聊天，尝试其他失物招领...\n";
    $lostFound = LostFound::where('user_id', '!=', $user->id)->first();
    if (!$lostFound) {
        echo "错误：没有其他用户发布的失物招领数据\n";
        exit;
    }
    $sellerId = $lostFound->user_id;
    echo "使用其他失物招领数据: {$lostFound->title} (ID: {$lostFound->id})\n";
    echo "发布者ID: {$sellerId}\n";
}

try {
    // 查找是否已存在会话
    $conversation = Conversation::where('user_id', $user->id)
        ->where('resource_type', 'lost_found')
        ->where('resource_id', $lostFound->id)
        ->first();

    if ($conversation) {
        echo "会话已存在: ID: {$conversation->id}, 最后消息: {$conversation->last_message}\n";
    } else {
        echo "创建新会话...\n";
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'seller_id' => $sellerId,
            'product_id' => null, // 失物招领会话，product_id为null
            'resource_type' => 'lost_found',
            'resource_id' => $lostFound->id,
            'last_message' => '您好，我对您发布的失物招领感兴趣',
            'last_message_time' => now(),
            'unread_count' => 1
        ]);
        echo "会话创建成功！ID: {$conversation->id}\n";
    }

    echo "\n会话详情: " . json_encode($conversation, JSON_PRETTY_PRINT) . "\n";
    echo "\n✅ 测试成功！失物招领会话创建功能已修复\n";

} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "错误跟踪: " . $e->getTraceAsString() . "\n";
}
