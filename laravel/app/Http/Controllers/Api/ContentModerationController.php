<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Topic;
use App\Models\Reply;
use App\Models\LostFound;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentModerationController extends Controller
{
    /**
     * 待审核内容列表
     */
    public function pending(Request $request)
    {
        $type = $request->input('type', 'all');
        $contents = [];

        // 获取待审核的商品
        if ($type === 'all' || $type === 'product') {
            $products = Product::where('status', 'pending')
                ->with('user:id,username,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'product',
                        'title' => $item->title,
                        'content' => $item->description,
                        'author' => $item->user->name ?? $item->user->username ?? '未知用户',
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'status' => $item->status ?? 'pending',
                        'image' => $this->getImageUrl($item->cover_img),
                    ];
                });
            $contents = array_merge($contents, $products->toArray());
        }

        // 获取待审核的论坛话题
        if ($type === 'all' || $type === 'topic') {
            $topics = Topic::where('status', 'pending')
                ->with('user:id,username,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'topic',
                        'title' => $item->title,
                        'content' => $item->content,
                        'author' => $item->user->name ?? $item->user->username ?? '未知用户',
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'status' => $item->status ?? 'pending',
                        'image' => '',
                    ];
                });
            $contents = array_merge($contents, $topics->toArray());
        }

        // 获取待审核的评论
        if ($type === 'all' || $type === 'comment') {
            $replies = Reply::where('status', 'pending')
                ->with('user:id,username,name')
                ->with('topic:id,title')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'comment',
                        'title' => '评论：' . ($item->topic->title ?? '未知话题'),
                        'content' => $item->content,
                        'author' => $item->user->name ?? $item->user->username ?? '未知用户',
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'status' => $item->status ?? 'pending',
                        'image' => '',
                    ];
                });
            $contents = array_merge($contents, $replies->toArray());
        }

        // 获取待审核的失物招领
        if ($type === 'all' || $type === 'lostfound') {
            $lostFounds = LostFound::where('moderation_status', 'pending')
                ->with('user:id,username,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'lostfound',
                        'title' => $item->title,
                        'content' => $item->description,
                        'author' => $item->user->name ?? $item->user->username ?? '未知用户',
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'status' => $item->moderation_status ?? 'pending',
                        'image' => $this->getImageUrl($item->cover_img),
                    ];
                });
            $contents = array_merge($contents, $lostFounds->toArray());
        }

        // 获取待审核的活动
        if ($type === 'all' || $type === 'activity') {
            $activities = Activity::where('moderation_status', 'pending')
                ->with('user:id,username,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'activity',
                        'title' => $item->title,
                        'content' => $item->description ?? '',
                        'author' => $item->user->name ?? $item->user->username ?? '未知用户',
                        'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                        'status' => $item->moderation_status ?? 'pending',
                        'image' => $this->getImageUrl($item->poster),
                    ];
                });
            $contents = array_merge($contents, $activities->toArray());
        }

        // 按时间排序
        usort($contents, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // 分页
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $total = count($contents);
        $start = ($page - 1) * $pageSize;
        $end = $start + $pageSize;
        $paginatedContents = array_slice($contents, $start, $pageSize);

        return response()->json([
            'code' => 200,
            'data' => [
                'list' => $paginatedContents,
                'total' => $total,
                'page' => $page,
                'pageSize' => $pageSize
            ],
            'msg' => 'success'
        ]);
    }

    /**
     * 审核通过
     */
    public function approve($type, $id)
    {
        try {
            // 验证类型
            $validTypes = ['product', 'topic', 'comment', 'lostfound', 'activity'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'code' => 400,
                    'msg' => '无效的内容类型'
                ], 400);
            }

            // 获取对应模型
            $model = $this->getModelByType($type);
            if (!$model) {
                return response()->json([
                    'code' => 400,
                    'msg' => '无效的内容类型'
                ], 400);
            }

            // 查找内容
            $item = $model::find($id);
            if (!$item) {
                return response()->json([
                    'code' => 404,
                    'msg' => '内容不存在'
                ], 404);
            }

            // 根据类型设置正确的字段
            if ($type === 'lostfound' || $type === 'activity') {
                $item->moderation_status = 'approved';
            } else {
                $item->status = 'approved';
            }
            $item->moderated_at = now();
            $item->moderated_by = auth()->id();
            $item->save();

            // 如果是评论审核通过，更新对应话题的回复数
            if ($type === 'comment') {
                $topicId = $item->topic_id;
                if ($topicId) {
                    $topic = Topic::find($topicId);
                    if ($topic) {
                        // 计算该话题的已通过审核的评论数
                        $approvedRepliesCount = Reply::where('topic_id', $topicId)
                            ->where('status', 'approved')
                            ->count();
                        
                        // 更新话题的回复数
                        $topic->replies_count = $approvedRepliesCount;
                        $topic->save();
                    }
                }
            }

            // 记录审核日志
            Log::info('内容审核通过', [
                'type' => $type,
                'id' => $id,
                'moderator' => auth()->user()->username
            ]);

            return response()->json([
                'code' => 200,
                'msg' => '审核通过'
            ]);
        } catch (\Exception $e) {
            Log::error('审核通过失败', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'code' => 500,
                'msg' => '审核失败'
            ], 500);
        }
    }

    /**
     * 审核拒绝
     */
    public function reject($type, $id, Request $request)
    {
        try {
            // 验证类型
            $validTypes = ['product', 'topic', 'comment', 'lostfound', 'activity'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'code' => 400,
                    'msg' => '无效的内容类型'
                ], 400);
            }

            // 获取拒绝原因
            $reason = $request->input('reason', '内容不符合要求');

            // 获取对应模型
            $model = $this->getModelByType($type);
            if (!$model) {
                return response()->json([
                    'code' => 400,
                    'msg' => '无效的内容类型'
                ], 400);
            }

            // 查找内容
            $item = $model::find($id);
            if (!$item) {
                return response()->json([
                    'code' => 404,
                    'msg' => '内容不存在'
                ], 404);
            }

            // 根据类型设置正确的字段
            if ($type === 'lostfound' || $type === 'activity') {
                $item->moderation_status = 'rejected';
            } else {
                $item->status = 'rejected';
            }
            $item->moderated_at = now();
            $item->moderated_by = auth()->id();
            $item->reject_reason = $reason;
            $item->save();

            // 如果是评论审核拒绝，更新对应话题的回复数
            if ($type === 'comment') {
                $topicId = $item->topic_id;
                if ($topicId) {
                    $topic = Topic::find($topicId);
                    if ($topic) {
                        // 计算该话题的已通过审核的评论数
                        $approvedRepliesCount = Reply::where('topic_id', $topicId)
                            ->where('status', 'approved')
                            ->count();
                        
                        // 更新话题的回复数
                        $topic->replies_count = $approvedRepliesCount;
                        $topic->save();
                    }
                }
            }

            // 记录审核日志
            Log::info('内容审核拒绝', [
                'type' => $type,
                'id' => $id,
                'reason' => $reason,
                'moderator' => auth()->user()->username
            ]);

            return response()->json([
                'code' => 200,
                'msg' => '审核拒绝'
            ]);
        } catch (\Exception $e) {
            Log::error('审核拒绝失败', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'code' => 500,
                'msg' => '审核失败'
            ], 500);
        }
    }

    /**
     * 根据类型获取模型
     */
    private function getModelByType($type)
    {
        $models = [
            'product' => Product::class,
            'topic' => Topic::class,
            'comment' => Reply::class,
            'lostfound' => LostFound::class,
            'activity' => Activity::class
        ];
        return $models[$type] ?? null;
    }

    /**
     * 获取图片完整URL
     */
    private function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }
        
        // 如果已经是完整URL或base64编码的图片，直接返回
        if (filter_var($imagePath, FILTER_VALIDATE_URL) || strpos($imagePath, 'data:image/') === 0) {
            return $imagePath;
        }
        
        // 生成完整URL
        $path = ltrim($imagePath, '/');
        return url('storage/' . $path);
    }
}
