<?php

namespace App\Http\Controllers\Api;

use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ForumController extends Controller
{
    /**
     * 获取话题列表
     */
    public function getTopics(Request $request)
    {
        $query = Topic::with('user:id,username,avatar')
            ->where('status', 'approved')
            ->select('id', 'title', 'content', 'user_id', 'category_id', 'replies_count', 'likes_count', 'favorites_count', 'created_at');

        // 按分类过滤
        if ($request->has('category_id') && $request->category_id != 'all') {
            $query->where('category_id', $request->category_id);
        }

        // 排序方式：hot（按点赞数），new（按时间）
        $sortBy = $request->get('sort_by', 'new');
        if ($sortBy === 'hot') {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->latest();
        }

        $topics = $query->paginate(10);

        // 格式化响应数据
        $formattedTopics = $topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'title' => $topic->title,
                'content' => $topic->content,
                'category_id' => $topic->category_id,
                'author' => [
                    'id' => $topic->user->id,
                    'username' => $topic->user->username,
                    'avatar' => $topic->user->avatar || 'https://picsum.photos/100/100?random=' . $topic->user->id
                ],
                'created_at' => $topic->created_at->format('Y-m-d H:i:s'),
                'replies_count' => $topic->replies_count,
                'likes_count' => $topic->likes_count,
                'favorites_count' => $topic->favorites_count ?? 0
            ];
        });

        return response()->json([
            'code' => 200,
            'data' => $formattedTopics,
            'msg' => 'success',
            'total' => $topics->total()
        ]);
    }

    /**
     * 获取话题详情
     */
    public function getTopicDetail(Request $request, $id)
    {
        $topic = Topic::with('user:id,username,avatar')
            ->where('status', 'approved')
            ->findOrFail($id);

        // 格式化响应数据
        $formattedTopic = [
            'id' => $topic->id,
            'title' => $topic->title,
            'content' => $topic->content,
            'category_id' => $topic->category_id,
            'author' => [
                'id' => $topic->user->id,
                'username' => $topic->user->username,
                'avatar' => $topic->user->avatar || 'https://picsum.photos/100/100?random=' . $topic->user->id
            ],
            'created_at' => $topic->created_at->format('Y-m-d H:i:s'),
            'replies_count' => $topic->replies_count,
            'likes_count' => $topic->likes_count,
            'favorites_count' => $topic->favorites_count ?? 0
        ];

        return response()->json(['code' => 200, 'data' => $formattedTopic, 'msg' => 'success']);
    }

    /**
     * 获取话题评论
     */
    public function getComments(Request $request, $id)
    {
        $comments = Reply::with('user:id,username,avatar')
            ->where('topic_id', $id)
            ->where('status', 'approved')
            ->latest()
            ->get();

        // 格式化响应数据
        $formattedComments = $comments->map(function ($comment) {
            $avatar = $comment->user->avatar;
            if (empty($avatar) || $avatar === true) {
                $avatar = 'https://picsum.photos/100/100?random=' . $comment->user->id;
            }
            return [
                'id' => $comment->id,
                'user' => [
                    'id' => $comment->user->id,
                    'username' => $comment->user->username,
                    'avatar' => $avatar
                ],
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json(['code' => 200, 'data' => $formattedComments, 'msg' => 'success']);
    }

    /**
     * 添加评论
     */
    public function addComment(Request $request, $id)
    {
        // 获取当前用户（需要登录认证）
        $user = Auth::user();

        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '请先登录'], 401);
        }

        // 创建评论
        $comment = Reply::create([
            'topic_id' => $id,
            'user_id' => $user->id,
            'content' => $request->input('content'),
            'status' => 'pending'
        ]);

        // 加载用户信息
        $comment->load('user:id,username,avatar');

        // 格式化响应数据
        $formattedComment = [
            'id' => $comment->id,
            'user' => [
                'id' => $comment->user->id,
                'username' => $comment->user->username,
                'avatar' => $comment->user->avatar || 'https://picsum.photos/100/100?random=' . $comment->user->id
            ],
            'content' => $comment->content,
            'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            'status' => $comment->status,
            'statusText' => $this->getStatusText($comment->status)
        ];

        return response()->json(['code' => 200, 'data' => $formattedComment, 'msg' => '评论添加成功，等待管理员审核']);
    }

    /**
     * 创建话题
     */
    public function createTopic(Request $request)
    {
        // 获取当前用户（需要登录认证）
        $user = Auth::user();

        if (!$user) {
            return response()->json(['code' => 401, 'msg' => '请先登录'], 401);
        }

        // 验证请求数据
        $request->validate([
            'title' => 'required|string|max:100',
            'content' => 'required|string',
            'category_id' => 'nullable|integer|exists:categories,id'
        ]);

        // 创建话题
        $topic = Topic::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'category_id' => $request->input('category_id'),
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        // 加载用户信息
        $topic->load('user:id,username,avatar');

        // 格式化响应数据
        $formattedTopic = [
            'id' => $topic->id,
            'title' => $topic->title,
            'content' => $topic->content,
            'category_id' => $topic->category_id,
            'author' => [
                'id' => $topic->user->id,
                'username' => $topic->user->username,
                'avatar' => $topic->user->avatar || 'https://picsum.photos/100/100?random=' . $topic->user->id
            ],
            'created_at' => $topic->created_at->format('Y-m-d H:i:s'),
            'replies_count' => 0,
            'likes_count' => 0,
            'status' => $topic->status,
            'statusText' => $this->getStatusText($topic->status)
        ];

        return response()->json(['code' => 200, 'data' => $formattedTopic, 'msg' => '话题创建成功，等待管理员审核']);
    }

    /**
     * 获取热门话题
     */
    public function getHotTopics(Request $request)
    {
        $hotTopics = Topic::where('status', 'approved')
            ->select('id', 'title', 'replies_count')
            ->orderBy('replies_count', 'desc')
            ->take(5)
            ->get();

        return response()->json(['code' => 200, 'data' => $hotTopics, 'msg' => 'success']);
    }

    /**
     * 获取活跃用户
     */
    public function getActiveUsers(Request $request)
    {
        // 获取评论数最多的用户
        $activeUsers = Reply::select('user_id')
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as reply_count')
            ->groupBy('user_id')
            ->orderBy('reply_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $user = \App\Models\User::find($item->user_id);
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'avatar' => $user->avatar || 'https://picsum.photos/100/100?random=' . $user->id
                ];
            });

        return response()->json(['code' => 200, 'data' => $activeUsers, 'msg' => 'success']);
    }

    /**
     * 获取状态文本
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => '待审核',
            'approved' => '已通过',
            'rejected' => '已拒绝'
        ];
        return $statusMap[$status] ?? '未知';
    }

    /**
     * 获取用户资料
     */
    public function userProfile($id)
    {
        $user = \App\Models\User::select("id", "username", "avatar", "bio", "topics_count")->find($id);
        if (!$user) return response()->json(["code" => 404, "msg" => "用户不存在"]);

        $stats = \Illuminate\Support\Facades\DB::table("user_stats")->where("user_id", $id)->first();
        $reads = $stats ? ($stats->total_reads ?? 0) : 0;
        $fans = \Illuminate\Support\Facades\DB::table("follows")->where("followed_id", $id)->count();

        $isFollowing = false;
        if ($currentUser = auth("sanctum")->user()) {
            $isFollowing = \Illuminate\Support\Facades\DB::table("follows")
                ->where("follower_id", $currentUser->id)
                ->where("followed_id", $id)
                ->exists();
        }

        return response()->json([
            "code" => 200,
            "data" => [
                "name" => $user->username,
                "bio" => $user->bio ?? "",
                "avatar" => $user->avatar ?? "",
                "topics" => $user->topics_count ?? 0,
                "reads" => $reads,
                "fans" => $fans,
                "isFollowing" => $isFollowing
            ],
            "msg" => "success"
        ]);
    }

    /**
     * 点赞/取消点赞话题
     */
    public function likeTopic(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(["code" => 401, "msg" => "请先登录"], 401);

        $topic = Topic::find($id);
        if (!$topic) return response()->json(["code" => 404, "msg" => "话题不存在"]);

        $exists = \Illuminate\Support\Facades\DB::table("forum_likes")
            ->where("user_id", $user->id)
            ->where("topic_id", $id)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table("forum_likes")
                ->where("user_id", $user->id)
                ->where("topic_id", $id)
                ->delete();
            $topic->decrement('likes_count');
            return response()->json(["code" => 200, "data" => ["isLiked" => false], "msg" => "已取消点赞"]);
        } else {
            \Illuminate\Support\Facades\DB::table("forum_likes")->insert([
                "user_id" => $user->id,
                "topic_id" => $id,
                "created_at" => now(),
                "updated_at" => now()
            ]);
            $topic->increment('likes_count');
            return response()->json(["code" => 200, "data" => ["isLiked" => true], "msg" => "点赞成功"]);
        }
    }

    /**
     * 收藏/取消收藏话题
     */
    public function favoriteTopic(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(["code" => 401, "msg" => "请先登录"], 401);

        $topic = Topic::find($id);
        if (!$topic) return response()->json(["code" => 404, "msg" => "话题不存在"]);

        $exists = \Illuminate\Support\Facades\DB::table("forum_favorites")
            ->where("user_id", $user->id)
            ->where("topic_id", $id)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table("forum_favorites")
                ->where("user_id", $user->id)
                ->where("topic_id", $id)
                ->delete();
            $topic->decrement('favorites_count');
            return response()->json(["code" => 200, "data" => ["isFavorited" => false], "msg" => "已取消收藏"]);
        } else {
            \Illuminate\Support\Facades\DB::table("forum_favorites")->insert([
                "user_id" => $user->id,
                "topic_id" => $id,
                "created_at" => now(),
                "updated_at" => now()
            ]);
            $topic->increment('favorites_count');
            return response()->json(["code" => 200, "data" => ["isFavorited" => true], "msg" => "收藏成功"]);
        }
    }

    /**
     * 获取推荐话题（点赞数前五）
     */
    public function getRecommendations()
    {
        $topics = Topic::with('user')
            ->select('topics.*',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(CASE WHEN forum_likes.id IS NOT NULL THEN 1 ELSE 0 END), 0) as likes_count'))
            ->leftJoin('forum_likes', 'topics.id', '=', 'forum_likes.topic_id')
            ->groupBy('topics.id')
            ->orderBy('likes_count', 'desc')
            ->limit(5)
            ->get();

        $data = $topics->map(function ($topic) {
            return [
                'id' => $topic->id,
                'title' => $topic->title,
                'author_username' => $topic->user->username ?? '匿名用户',
                'created_at' => $topic->created_at,
                'reads_count' => $topic->reads_count ?? 0,
                'replies_count' => $topic->replies_count ?? 0,
            ];
        });

        return response()->json(["code" => 200, "data" => $data]);
    }

    /**
     * 获取话题导航（上下篇）
     */
    public function getTopicNavigation($id)
    {
        $currentTopic = Topic::find($id);
        if (!$currentTopic) return response()->json(["code" => 404, "msg" => "话题不存在"]);

        $categoryId = $currentTopic->category_id;

        // 获取当前栏目下按id正序排列的所有话题
        $topics = Topic::where('category_id', $categoryId)
            ->orderBy('id', 'asc')
            ->get(['id', 'title']);

        $currentIndex = $topics->search(function ($topic) use ($id) {
            return $topic->id == $id;
        });

        $prevTopic = null;
        $nextTopic = null;

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $prevTopic = $topics[$currentIndex - 1];
            }
            if ($currentIndex < count($topics) - 1) {
                $nextTopic = $topics[$currentIndex + 1];
            }
        }

        return response()->json([
            "code" => 200,
            "data" => [
                "prev" => $prevTopic ? ['id' => $prevTopic->id, 'title' => $prevTopic->title] : null,
                "next" => $nextTopic ? ['id' => $nextTopic->id, 'title' => $nextTopic->title] : null
            ]
        ]);
    }

    /**
     * 关注/取关用户
     */
    public function userFollow(\Illuminate\Http\Request $request, $id)
    {
        $currentUser = auth("sanctum")->user();
        if (!$currentUser) return response()->json(["code" => 401, "msg" => "请先登录"]);
        if ($currentUser->id == $id) return response()->json(["code" => 400, "msg" => "不能关注自己"]);

        $exists = \Illuminate\Support\Facades\DB::table("follows")
            ->where("follower_id", $currentUser->id)
            ->where("followed_id", $id)
            ->exists();

        if ($exists) {
            \Illuminate\Support\Facades\DB::table("follows")
                ->where("follower_id", $currentUser->id)
                ->where("followed_id", $id)
                ->delete();
            return response()->json(["code" => 200, "data" => ["isFollowing" => false], "msg" => "已取消关注"]);
        } else {
            \Illuminate\Support\Facades\DB::table("follows")->insert([
                "follower_id" => $currentUser->id,
                "followed_id" => $id,
                "created_at" => now(),
                "updated_at" => now()
            ]);
            return response()->json(["code" => 200, "data" => ["isFollowing" => true], "msg" => "已关注"]);
        }
    }

    /**
     * 获取用户对话题的状态（点赞、收藏）
     */
    public function getUserTopicStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                "code" => 200,
                "data" => [
                    "is_liked" => false,
                    "is_favorited" => false
                ],
                "msg" => "success"
            ]);
        }

        $isLiked = \Illuminate\Support\Facades\DB::table("forum_likes")
            ->where("user_id", $user->id)
            ->where("topic_id", $id)
            ->exists();

        $isFavorited = \Illuminate\Support\Facades\DB::table("forum_favorites")
            ->where("user_id", $user->id)
            ->where("topic_id", $id)
            ->exists();

        return response()->json([
            "code" => 200,
            "data" => [
                "is_liked" => $isLiked,
                "is_favorited" => $isFavorited
            ],
            "msg" => "success"
        ]);
    }

    /**
     * 获取用户收藏的话题列表
     */
    public function getUserFavorites(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(["code" => 401, "msg" => "请先登录"], 401);

        $favorites = \Illuminate\Support\Facades\DB::table("forum_favorites")
            ->where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->get();

        $topicIds = $favorites->pluck("topic_id")->toArray();

        $topics = Topic::whereIn("id", $topicIds)
            ->where("status", "approved")
            ->get([
                "id",
                "title",
                "content",
                "category_id",
                "user_id",
                "replies_count",
                "likes_count",
                "created_at"
            ]);

        return response()->json([
            "code" => 200,
            "data" => $topics,
            "msg" => "success"
        ]);
    }
}
