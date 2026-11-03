<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * 运行种子：创建测试管理员账号
     */
    public function run(): void
    {
        User::create([
            'name' => '系统管理员',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'), // 密码：123456
            'role' => 'admin',
        ]);
    }
}
