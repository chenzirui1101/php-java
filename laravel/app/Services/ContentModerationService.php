<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * 内容审查服务
 * 集成百度AI内容审核API + 本地敏感词过滤
 */
class ContentModerationService
{
    // 百度AI API配置
    private $appId;
    private $apiKey;
    private $secretKey;
    private $accessToken;
    
    // 敏感词列表（本地备份，用于快速过滤）
    private $sensitiveWords = [];
    
    // 审核结果状态
    const STATUS_PENDING = 'pending';    // 待审核
    const STATUS_APPROVED = 'approved';  // 已通过
    const STATUS_REJECTED = 'rejected';  // 已拒绝
    
    public function __construct()
    {
        $this->appId = config('services.baidu_ai.app_id');
        $this->apiKey = config('services.baidu_ai.api_key');
        $this->secretKey = config('services.baidu_ai.secret_key');
        
        // 加载敏感词库
        $this->loadSensitiveWords();
    }
    
    /**
     * 加载敏感词库
     */
    private function loadSensitiveWords()
    {
        $wordsFile = storage_path('app/sensitive_words.txt');
        if (file_exists($wordsFile)) {
            $content = file_get_contents($wordsFile);
            $this->sensitiveWords = array_filter(explode("\n", $content));
        }
    }
    
    /**
     * 获取百度AI Access Token
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $url = "https://aip.baidubce.com/oauth/2.0/token";
        $response = Http::asForm()->post($url, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->apiKey,
            'client_secret' => $this->secretKey,
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken;
        }
        
        Log::error('获取百度AI Access Token失败', ['response' => $response->body()]);
        return null;
    }
    
    /**
     * 审核文本内容
     * 
     * @param string $text 要审核的文本
     * @param string $type 内容类型: product, forum, comment, lostfound
     * @return array ['passed' => bool, 'reason' => string, 'confidence' => float]
     */
    public function moderateText(string $text, string $type = 'general'): array
    {
        // 1. 先进行本地敏感词过滤
        $localCheck = $this->checkSensitiveWords($text);
        if (!$localCheck['passed']) {
            return $localCheck;
        }
        
        // 2. 调用百度AI进行审核（如果配置了API）
        if ($this->apiKey && $this->secretKey) {
            $baiduCheck = $this->checkWithBaiduAI($text, $type);
            if (!$baiduCheck['passed']) {
                return $baiduCheck;
            }
        }
        
        return [
            'passed' => true,
            'reason' => '',
            'confidence' => 1.0,
            'source' => 'local'
        ];
    }
    
    /**
     * 本地敏感词检测
     */
    private function checkSensitiveWords(string $text): array
    {
        $foundWords = [];
        
        foreach ($this->sensitiveWords as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            if (stripos($text, $word) !== false) {
                $foundWords[] = $word;
            }
        }
        
        if (!empty($foundWords)) {
            return [
                'passed' => false,
                'reason' => '内容包含敏感词: ' . implode(', ', array_slice($foundWords, 0, 3)),
                'confidence' => 0.99,
                'source' => 'local',
                'words' => $foundWords
            ];
        }
        
        return ['passed' => true];
    }
    
    /**
     * 百度AI内容审核
     */
    private function checkWithBaiduAI(string $text, string $type): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            // API未配置，跳过
            return ['passed' => true];
        }
        
        $url = "https://aip.baidubce.com/rest/2.0/solution/v1/text_censor/v2/user_defined?access_token={$accessToken}";
        
        $response = Http::asForm()->post($url, [
            'text' => $text,
        ]);
        
        if (!$response->successful()) {
            Log::error('百度AI文本审核失败', ['response' => $response->body()]);
            return ['passed' => true]; // 失败时默认通过，避免阻塞
        }
        
        $data = $response->json();
        
        // 百度AI返回结果解析
        // conclusionType: 1-合规, 2-不合规, 3-疑似, 4-审核失败
        $conclusionType = $data['conclusionType'] ?? 1;
        
        if ($conclusionType == 2) {
            $reasons = [];
            if (isset($data['data'])) {
                foreach ($data['data'] as $item) {
                    $reasons[] = $item['msg'] ?? '包含违规内容';
                }
            }
            
            return [
                'passed' => false,
                'reason' => implode(', ', array_unique($reasons)),
                'confidence' => 0.95,
                'source' => 'baidu_ai',
                'raw' => $data
            ];
        }
        
        if ($conclusionType == 3) {
            // 疑似违规，需要人工审核
            return [
                'passed' => true, // 先通过，但标记为待审核
                'reason' => '内容疑似违规，已提交人工审核',
                'confidence' => 0.5,
                'source' => 'baidu_ai',
                'needs_review' => true,
                'raw' => $data
            ];
        }
        
        return [
            'passed' => true,
            'reason' => '',
            'confidence' => 0.99,
            'source' => 'baidu_ai'
        ];
    }
    
    /**
     * 审核图片内容
     * 
     * @param string $imagePath 图片路径或URL
     * @param bool $isUrl 是否为URL
     * @return array ['passed' => bool, 'reason' => string]
     */
    public function moderateImage(string $imagePath, bool $isUrl = false): array
    {
        if (!$this->apiKey || !$this->secretKey) {
            // 未配置API，默认通过
            return ['passed' => true, 'source' => 'none'];
        }
        
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['passed' => true, 'source' => 'none'];
        }
        
        $url = "https://aip.baidubce.com/rest/2.0/solution/v1/img_censor/v2/user_defined?access_token={$accessToken}";
        
        // 获取图片内容
        if ($isUrl) {
            $imageData = file_get_contents($imagePath);
        } else {
            $fullPath = storage_path('app/public/' . $imagePath);
            if (!file_exists($fullPath)) {
                return ['passed' => false, 'reason' => '图片文件不存在'];
            }
            $imageData = file_get_contents($fullPath);
        }
        
        $base64Image = base64_encode($imageData);
        
        $response = Http::asForm()->post($url, [
            'image' => $base64Image,
        ]);
        
        if (!$response->successful()) {
            Log::error('百度AI图片审核失败', ['response' => $response->body()]);
            return ['passed' => true]; // 失败时默认通过
        }
        
        $data = $response->json();
        $conclusionType = $data['conclusionType'] ?? 1;
        
        if ($conclusionType == 2) {
            $reasons = [];
            if (isset($data['data'])) {
                foreach ($data['data'] as $item) {
                    $reasons[] = $item['msg'] ?? '图片包含违规内容';
                }
            }
            
            return [
                'passed' => false,
                'reason' => implode(', ', array_unique($reasons)),
                'source' => 'baidu_ai',
                'raw' => $data
            ];
        }
        
        return [
            'passed' => true,
            'source' => 'baidu_ai'
        ];
    }
    
    /**
     * 批量审核文本
     */
    public function moderateMultipleTexts(array $texts, string $type = 'general'): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->moderateText($text, $type);
        }
        return $results;
    }
    
    /**
     * 替换敏感词为***
     */
    public function filterSensitiveWords(string $text): string
    {
        foreach ($this->sensitiveWords as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            
            $replacement = str_repeat('*', mb_strlen($word));
            $text = str_ireplace($word, $replacement, $text);
        }
        
        return $text;
    }
}
