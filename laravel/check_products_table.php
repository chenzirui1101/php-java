<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 检查products表结构
$columns = DB::select('SHOW COLUMNS FROM products');
echo "Products Table Structure:\n";
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n";
echo "| Field            | Type                            | Null | Key | Default | Extra          |\n";
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n";

foreach ($columns as $column) {
    printf("| %-16s | %-31s | %-4s | %-3s | %-7s | %-16s |\n",
        $column->Field,
        $column->Type,
        $column->Null,
        $column->Key,
        $column->Default ?? '',
        $column->Extra
    );
}
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n\n";

// 检查商品数据
$productCount = DB::table('products')->count();
echo "商品总数: {$productCount}\n\n";

if ($productCount > 0) {
    echo "最近的5条商品数据:\n";
    $recentProducts = DB::table('products')->orderBy('id', 'desc')->take(5)->get();
    foreach ($recentProducts as $product) {
        echo "ID: {$product->id}, 标题: {$product->title}, 价格: {$product->price}, 用户ID: {$product->user_id}\n";
    }
} else {
    echo "没有商品数据\n";
}

// 检查是否有用户数据
$userCount = DB::table('users')->count();
echo "\n用户总数: {$userCount}\n";
