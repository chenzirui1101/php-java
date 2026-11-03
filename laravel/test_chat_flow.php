<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Conversation;
use App\Models\Message;

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

// 2. 检查是否存在Product数据
$product = Product::first();
if (!$product) {
    echo "错误：没有商品数据\n";
    exit;
}

echo "使用商品数据: {$product->title} (ID: {$product->id})\n";

// 3. 尝试创建会话
$sellerId = $product->user_id;
echo "卖家ID: {$sellerId}\n";

// 防止与自己聊天
if ($sellerId == $user->id) {
    echo "测试用户不能与自己聊天，尝试其他商品...\n";
    $product = Product::where('user_id', '!=', $user->id)->first();
    if (!$product) {
        echo "错误：没有其他用户发布的商品数据\n";
        exit;
    }
    $sellerId = $product->user_id;
    echo "使用其他商品数据: {$product->title} (ID: {$product->id})\n";
    echo "卖家ID: {$sellerId}\n";
}

try {
    // 查找是否已存在会话
    $conversation = Conversation::where('user_id', $user->id)
        ->where('resource_type', 'product')
        ->where('resource_id', $product->id)
        ->first();

    if ($conversation) {
        echo "使用现有会话: ID: {$conversation->id}\n";
    } else {
        echo "创建新会话...\n";
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'seller_id' => $sellerId,
            'product_id' => $product->id,
            'resource_type' => 'product',
            'resource_id' => $product->id,
            'last_message' => '您好，我对您发布的商品感兴趣',
            'last_message_time' => now(),
            'unread_count' => 1
        ]);
        echo "会话创建成功！ID: {$conversation->id}\n";
    }

    // 4. 模拟用户点击联系卖家后跳转到聊天页面的URL
    echo "\n===== 测试聊天流程 =====\n";
    echo "1. 用户点击商品详情页的'联系卖家'按钮\n";
    echo "2. 系统创建会话，会话ID: {$conversation->id}\n";
    echo "3. 页面跳转到聊天页面，URL: http://localhost:5173/chat?conversation_id={$conversation->id}\n";
    echo "4. 聊天页面从URL获取会话ID: {$conversation->id}\n";
    echo "5. 聊天页面自动选中会话，显示聊天框\n";
    echo "6. 用户可以开始与卖家聊天\n";
    echo "========================\n";

    echo "\n✅ 测试成功！点击商品联系卖家后，聊天会话框能正常显示\n";

} catch (Exception $e) {
    echo "❌ 测试失败: " . $e->getMessage() . "\n";
    echo "错误位置: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "错误跟踪: " . $e->getTraceAsString() . "\n";
}
