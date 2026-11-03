<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * 登录
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            // 查找用户（支持用户名或邮箱登录）
            $user = User::where('username', $request->username)->orWhere('email', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'username' => ['用户名或密码错误'],
                ]);
            }

            // 生成token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'code' => 200,
                'msg' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'role' => $user->role,
                    ],
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('登录失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '登录失败',
            ], 500);
        }
    }

    /**
     * 注册
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|min:3|max:25|unique:users',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'name' => 'nullable|string|max:50',
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'name' => $request->name,
                'role' => 'user', // 注册用户默认为普通用户
            ]);

            // 生成token
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'code' => 200,
                'msg' => '注册成功',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'role' => $user->role,
                    ],
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('注册失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '注册失败',
            ], 500);
        }
    }

    /**
     * 退出登录
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'code' => 200,
                'msg' => '退出成功',
            ]);
        } catch (\Exception $e) {
            Log::error('退出失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '退出失败',
            ], 500);
        }
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'code' => 200,
                'msg' => '获取成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('获取用户信息失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '获取失败',
            ], 500);
        }
    }

    /**
     * 检查token有效性
     */
    public function checkToken(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'code' => 200,
                'msg' => 'token有效',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('token无效：' . $e->getMessage());
            return response()->json([
                'code' => 401,
                'msg' => 'token无效',
            ], 401);
        }
    }

    /**
     * 更新用户信息
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $request->validate([
                'name' => 'nullable|string|max:50',
                'avatar' => 'nullable|string',
            ]);

            $user->update([
                'name' => $request->name ?? $user->name,
                'avatar' => $request->avatar ?? $user->avatar,
            ]);

            return response()->json([
                'code' => 200,
                'msg' => '更新成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('更新失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '更新失败',
            ], 500);
        }
    }

    /**
     * 验证码
     */
    public function captcha()
    {
        try {
            // 生成简单的加减题验证码
            $num1 = rand(1, 20);
            $num2 = rand(1, 20);
            $operator = rand(0, 1) ? '+' : '-';
            
            // 确保减法结果为正数
            if ($operator == '-' && $num1 < $num2) {
                $temp = $num1;
                $num1 = $num2;
                $num2 = $temp;
            }
            
            // 计算答案
            $answer = $operator == '+' ? $num1 + $num2 : $num1 - $num2;
            
            // 生成验证码文本
            $captchaText = "{$num1} {$operator} {$num2} = ?";
            
            return response()->json([
                'code' => 200,
                'msg' => '获取成功',
                'data' => [
                    'captcha' => (string)$answer, // 答案
                    'image' => $captchaText, // 验证码文本
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('获取验证码失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '获取失败',
            ], 500);
        }
    }
}
