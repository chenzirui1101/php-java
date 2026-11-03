<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Topic;
use App\Models\Reply;

// 初始化 Laravel 应用
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// 检查话题 1
$topic = Topic::find(1);
if ($topic) {
    echo "话题标题: " . $topic->title . "\n";
    echo "回复数: " . $topic->replies_count . "\n";
    
    // 检查评论
    $comments = Reply::where('topic_id', 1)->where('status', 'approved')->get();
    echo "已通过审核的评论数: " . $comments->count() . "\n";
    
    if ($comments->count() > 0) {
        echo "评论内容:\n";
        foreach ($comments as $comment) {
            echo "- " . $comment->content . " (用户: " . $comment->user->username . ")\n";
        }
    } else {
        echo "暂无评论\n";
    }
} else {
    echo "话题 1 不存在\n";
}
