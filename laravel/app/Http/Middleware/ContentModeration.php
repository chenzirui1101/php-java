<?php

namespace App\Http\Middleware;

use App\Services\ContentModerationService;
use Closure;
use Illuminate\Http\Request;

/**
 * 内容审核中间件
 * 用于自动审核用户提交的内容
 */
class ContentModeration
{
    protected $moderationService;
    
    public function __construct(ContentModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $type  审核类型: product, forum, comment, lostfound
     * @param  string  $fields  要审核的字段，多个用逗号分隔
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $type = 'general', string $fields = '')
    {
        // 解析要审核的字段
        $fieldArray = $fields ? explode(',', $fields) : ['title', 'content', 'description'];
        
        // 收集需要审核的文本
        $textsToCheck = [];
        foreach ($fieldArray as $field) {
            $value = $request->input($field);
            if ($value && is_string($value)) {
                $textsToCheck[$field] = $value;
            }
        }
        
        // 如果没有内容需要审核，直接通过
        if (empty($textsToCheck)) {
            return $next($request);
        }
        
        // 进行内容审核
        $results = $this->moderationService->moderateMultipleTexts($textsToCheck, $type);
        
        // 检查是否有未通过审核的内容
        $failedFields = [];
        $failedReasons = [];
        
        foreach ($results as $field => $result) {
            if (!$result['passed']) {
                $failedFields[] = $field;
                $failedReasons[] = $result['reason'];
            }
        }
        
        // 如果有违规内容，返回错误
        if (!empty($failedFields)) {
            return response()->json([
                'code' => 422,
                'msg' => '内容审核未通过',
                'errors' => [
                    'content' => array_unique($failedReasons)
                ]
            ], 422);
        }
        
        return $next($request);
    }
}
