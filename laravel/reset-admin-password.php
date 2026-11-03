<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 重置管理员密码
try {
    $user = User::where('username', 'admin')->first();

    if ($user) {
        $newPassword = '123456';
        $user->password = Hash::make($newPassword);
        $user->save();

        echo "管理员密码重置成功！\n";
        echo "用户名: admin\n";
        echo "新密码: 123456\n";
    } else {
        echo "未找到管理员账号，正在创建新的管理员...\n";

        User::create([
            'name' => '系统管理员',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
        ]);

        echo "管理员账号创建成功！\n";
        echo "用户名: admin\n";
        echo "密码: 123456\n";
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}
?>
