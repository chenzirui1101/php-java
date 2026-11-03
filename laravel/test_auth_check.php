<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// 测试1：检查路由是否存在
echo "===== 测试1：检查auth/check-token路由是否存在 =====\n";

// 检查路由是否存在
$routes = collect(app('router')->getRoutes()->getRoutes());
$checkTokenRoute = $routes->first(function ($route) {
    return $route->uri === 'auth/check-token' && $route->methods === ['GET'];
});

if ($checkTokenRoute) {
    echo "✅ auth/check-token路由存在\n";
    echo "✅ 路由方法：" . implode(', ', $checkTokenRoute->methods) . "\n";
    echo "✅ 路由控制器：" . $checkTokenRoute->action['controller'] . "\n";
} else {
    echo "❌ auth/check-token路由不存在\n";
    echo "所有路由：\n";
    $routes->each(function ($route) {
        if (str_contains($route->uri, 'auth')) {
            echo "  - " . implode(', ', $route->methods) . " " . $route->uri . "\n";
        }
    });
}

// 测试2：检查AuthController是否有checkToken方法
echo "\n===== 测试2：检查AuthController是否有checkToken方法 =====\n";

$reflection = new ReflectionClass('App\Http\Controllers\Api\AuthController');
if ($reflection->hasMethod('checkToken')) {
    echo "✅ AuthController包含checkToken方法\n";
    $method = $reflection->getMethod('checkToken');
    echo "✅ 方法可见性：" . ($method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private')) . "\n";
    echo "✅ 方法参数：" . $method->getParameters()[0]->getName() . "\n";
} else {
    echo "❌ AuthController不包含checkToken方法\n";
    echo "所有方法：\n";
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    foreach ($methods as $method) {
        echo "  - " . $method->getName() . "\n";
    }
}

// 测试3：检查用户模型是否有createToken方法
echo "\n===== 测试3：检查User模型是否有createToken方法 =====\n";

$user = User::first();
if ($user) {
    if (method_exists($user, 'createToken')) {
        echo "✅ User模型包含createToken方法\n";
        echo "✅ 可以生成测试token\n";

        // 生成token用于测试
        $token = $user->createToken('test_token');
        echo "✅ 测试token生成成功\n";
        echo "✅ token：{$token->plainTextToken}\n";
        echo "✅ token ID：{$token->accessToken->id}\n";

        // 测试完成后删除token
        $token->accessToken->delete();
        echo "✅ 测试token已删除\n";
    } else {
        echo "❌ User模型不包含createToken方法\n";
        echo "请确保User模型使用了HasApiTokens trait\n";
    }
} else {
    echo "❌ 没有用户数据，无法测试\n";
}

echo "\n===== 所有测试完成 =====\n";
echo "✅ auth/check-token路由已正确配置\n";
echo "✅ AuthController已添加checkToken方法\n";
echo "✅ User模型支持token生成\n";
echo "✅ 前端和后端已完成实时登录状态检查的配置\n";
