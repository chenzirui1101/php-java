<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * 处理传入的请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 核心：允许跨域携带凭证（Cookie/Session）
        $origin = $request->header('Origin');
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true'); // 允许携带Cookie
            $response->headers->set('Access-Control-Max-Age', '86400'); // 预检请求缓存时间
        }

        // 处理 OPTIONS 预检请求
        if ($request->isMethod('OPTIONS')) {
            return response()->json(['status' => 'success'], 200);
        }

        return $response;
    }
}
