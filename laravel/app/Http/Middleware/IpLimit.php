<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpLimit
{
    /**
     * 同一IP在5分钟内允许的最大注册次数
     */
    const MAX_REGISTER_PER_IP = 3;

    /**
     * IP限制的时间窗口（分钟）
     */
    const TIME_WINDOW_MINUTES = 5;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取客户端IP地址
        $ip = $request->ip();

        // 生成缓存键
        $cacheKey = "register_ip_{$ip}";

        // 获取当前IP的注册次数
        $registerCount = Cache::get($cacheKey, 0);

        // 检查是否超过限制
        if ($registerCount >= self::MAX_REGISTER_PER_IP) {
            // 返回429状态码（请求过于频繁）
            return response()->json([
                'code' => 429,
                'msg' => '同一IP短时间内注册次数过多，请'.self::TIME_WINDOW_MINUTES.'分钟后重试',
                'errors' => [
                    'ip' => ['同一IP短时间内注册次数过多，请'.self::TIME_WINDOW_MINUTES.'分钟后重试']
                ]
            ], 429);
        }

        // 增加注册次数并设置过期时间
        Cache::put($cacheKey, $registerCount + 1, now()->addMinutes(self::TIME_WINDOW_MINUTES));

        return $next($request);
    }
}
