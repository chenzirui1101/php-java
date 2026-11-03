<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 管理员验证中间件
 * 只允许管理员访问后台接口
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 检查用户是否登录
        if (!auth()->check()) {
            return response()->json([
                'code' => 401,
                'msg' => '请先登录'
            ], 401);
        }
        
        // 检查用户是否是管理员
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'code' => 403,
                'msg' => '权限不足，只有管理员可以访问此功能'
            ], 403);
        }
        
        return $next($request);
    }
}
