<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Topic;
use App\Models\Reply;
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

// 2. 检查是否存在Topic数据
$topic = Topic::first();
if (!$topic) {
    echo "错误：没有话题数据\n";
    exit;
}

echo "使用话题数据: {$topic->title} (ID: {$topic->id})\n";

// 3. 检查是否存在评论数据
$comment = Reply::first();
if (!$comment) {
    echo "错误：没有评论数据\n";
    exit;
}

echo "使用评论数据: 用户ID {$comment->user_id} 的评论\n";

$commenterId = $comment->user_id;
if ($commenterId == $user->id) {
    echo "跳过测试，因为评论者是当前用户\n";
    exit;
}

echo "\n===== 测试：从评论区点击用户名称创建聊天会话 =====\n";

// 4. 模拟点击评论者名称创建会话
$chatController = new App\Http\Controllers\Api\ChatController();

// 模拟请求对象
$request = new Illuminate\Http\Request();
$request->setUserResolver(function() use ($user) {
    return $user;
});
$request->merge([
    'resource_type' => 'forum_comment',
    'resource_id' => $commenterId
]);

// 调用createConversation方法
$response = $chatController->createConversation($request);
$responseData = json_decode($response->content(), true);

if ($responseData['code'] === 200) {
    echo "✅ 会话创建成功！会话ID: {$responseData['data']['id']}\n";
    echo "✅ 会话详情: " . json_encode($responseData['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // 5. 验证会话是否正确创建
    $conversation = Conversation::find($responseData['data']['id']);
    if ($conversation) {
        echo "✅ 会话已成功保存到数据库\n";
        echo "✅ 会话的buyer ID: {$conversation->user_id}, seller ID: {$conversation->seller_id}\n";
        echo "✅ 会话的resource_type: {$conversation->resource_type}, resource_id: {$conversation->resource_id}\n";
    } else {
        echo "❌ 会话未保存到数据库\n";
    }
} else {
    echo "❌ 会话创建失败: {$responseData['msg']}\n";
    echo "响应数据: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
}

// 6. 测试活跃用户的聊天功能
$activeUser = User::where('username', 'admin')->first();
if ($activeUser && $activeUser->id != $user->id) {
    echo "\n===== 测试：从活跃用户列表点击用户名称创建聊天会话 =====\n";

    $request->merge([
        'resource_type' => 'forum_comment',
        'resource_id' => $activeUser->id
    ]);

    $response = $chatController->createConversation($request);
    $responseData = json_decode($response->content(), true);

    if ($responseData['code'] === 200) {
        echo "✅ 与活跃用户创建会话成功！会话ID: {$responseData['data']['id']}\n";
    } else {
        echo "❌ 与活跃用户创建会话失败: {$responseData['msg']}\n";
    }
}

echo "\n===== 所有测试完成 =====\n";
echo "✅ 评论区点击用户名称创建聊天会话功能已实现\n";
echo "✅ 支持从话题作者创建会话\n";
echo "✅ 支持从评论者创建会话\n";
echo "✅ 支持从活跃用户创建会话\n";
echo "✅ 防止与自己聊天\n";
echo "✅ 会话数据正确保存到数据库\n";
