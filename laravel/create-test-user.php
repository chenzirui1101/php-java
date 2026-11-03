<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 创建测试用户
$user = User::create([
    'name' => '测试买家',
    'username' => 'testbuyer',
    'email' => 'testbuyer@example.com',
    'password' => Hash::make('123456')
]);

echo "测试用户创建成功！\n";
echo "用户ID: {$user->id}\n";
echo "用户名: {$user->username}\n";
echo "邮箱: {$user->email}\n";
echo "密码: 123456\n";

// 检查用户是否存在
$checkUser = User::where('username', 'testbuyer')->first();
if ($checkUser) {
    echo "\n用户已存在，信息如下：\n";
    echo "用户ID: {$checkUser->id}\n";
    echo "用户名: {$checkUser->username}\n";
    echo "邮箱: {$checkUser->email}\n";
} else {
    echo "\n用户创建失败！\n";
}
