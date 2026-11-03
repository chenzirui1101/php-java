<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('username', 'admin')->first();

if ($user) {
    $user->password = Hash::make('123456');
    $user->save();
    echo "密码重置成功!\n";
    echo "用户名: admin\n";
    echo "密码: 123456\n";
} else {
    echo "未找到管理员账号\n";
}
?>