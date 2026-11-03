<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    // 创建测试用户
    public function createTestUser()
    {
        // 检查用户是否已存在
        $existingUser = User::where('username', 'testbuyer')->first();

        if ($existingUser) {
            return response()->json([
                'code' => 200,
                'data' => $existingUser,
                'msg' => '测试用户已存在'
            ]);
        }

        // 创建新用户
        $user = User::create([
            'name' => '测试买家',
            'username' => 'testbuyer',
            'email' => 'testbuyer@example.com',
            'password' => Hash::make('123456')
        ]);

        return response()->json([
            'code' => 200,
            'data' => $user,
            'msg' => '测试用户创建成功'
        ]);
    }
}
