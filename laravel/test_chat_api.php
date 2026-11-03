<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

require __DIR__ . '/vendor/autoload.php';

// 创建Guzzle客户端
$client = new Client([
    'base_uri' => 'http://localhost:8000/api/',
    'timeout'  => 10.0,
    'headers' => [
        'Content-Type' => 'application/json',
    ],
]);

try {
    // 1. 先登录获取token
    $loginResponse = $client->post('auth/login', [
        'json' => [
            'username' => 'xiaoming',
            'password' => '123456',
        ],
    ]);

    $loginData = json_decode($loginResponse->getBody(), true);
    $token = $loginData['data']['token'];

    echo "登录成功，获取到token: $token\n";

    // 2. 使用token调用createConversation API，测试失物招领会话创建
    $response = $client->post('chat/conversations', [
        'headers' => [
            'Authorization' => "Bearer $token",
        ],
        'json' => [
            'resource_type' => 'lost_found',
            'resource_id' => 1,
        ],
    ]);

    $responseData = json_decode($response->getBody(), true);
    echo "\n创建会话成功！\n";
    echo "响应数据: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

} catch (GuzzleException $e) {
    echo "\nAPI请求失败: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        $response = $e->getResponse();
        echo "状态码: " . $response->getStatusCode() . "\n";
        echo "响应内容: " . $response->getBody() . "\n";
    }
}
