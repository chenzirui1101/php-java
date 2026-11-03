<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Product;
use App\Models\Rating;
use App\Models\User;

// 初始化Laravel应用
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 获取所有产品
$products = Product::all();

if ($products->isEmpty()) {
    echo "没有产品数据，无法添加评价！\n";
    exit;
}

// 获取所有用户
$users = User::all();

if ($users->isEmpty()) {
    echo "没有用户数据，无法添加评价！\n";
    exit;
}

// 评价内容模板
$ratingContents = [
    '商品质量非常好，与描述一致，推荐购买！',
    '物流很快，包装完好，商品很满意！',
    '性价比很高，下次还会再来！',
    '商品收到了，质量不错，使用起来很方便！',
    '卖家服务态度很好，有问题及时解决，好评！',
    '商品超出预期，质量很好，值得购买！',
    '物流速度快，商品包装精美，很喜欢！',
    '商品质量一般，与描述有些差距，勉强给个中评。',
    '物流太慢了，等了好久才收到，不满意！',
    '商品有瑕疵，联系卖家处理，态度还不错，给个好评吧。'
];

// 为每个产品添加2-5条评价
echo "开始添加评价数据...\n";
foreach ($products as $product) {
    $ratingCount = rand(2, 5);

    for ($i = 0; $i < $ratingCount; $i++) {
        // 随机选择一个用户
        $user = $users->random();

        // 检查该用户是否已经评价过该产品
        $existingRating = Rating::where('product_id', $product->id)
                            ->where('user_id', $user->id)
                            ->first();

        if ($existingRating) {
            // 如果已经评价过，跳过
            echo "用户 {$user->username} 已经评价过产品 {$product->title}，跳过\n";
            continue;
        }

        // 创建新评价
        $rating = new Rating();
        $rating->product_id = $product->id;
        $rating->user_id = $user->id;
        $rating->rating = rand(1, 5);
        $rating->comment = $ratingContents[array_rand($ratingContents)];
        $rating->save();

        echo "为产品 {$product->title} 添加了一条评价 (评分: {$rating->rating})\n";
    }
}

echo "\n评价数据添加完成！\n";
echo "共为 {$products->count()} 个产品添加了评价\n";
