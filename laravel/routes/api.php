<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\LostFoundController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\ContentModerationController;

// API 路由
// 用户公开接口
Route::get('user/{id}/profile', [ForumController::class, 'userProfile']);
Route::post('user/{id}/follow', [ForumController::class, 'userFollow']);

// 无需登录的公开接口
Route::prefix('auth')->group(function () {
    Route::get('captcha', [AuthController::class, 'captcha']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register'])->middleware('ip.limit');
});

// 商品公开接口
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    // 商品评价公开接口
    Route::get('/{productId}/ratings', [RatingController::class, 'productRatings']);
});

// 失物招领公开接口
Route::prefix('lostfound')->group(function () {
    Route::get('/', [LostFoundController::class, 'index']);
    Route::get('/{id}', [LostFoundController::class, 'show']);
});

// 论坛相关接口
Route::prefix('forum')->group(function () {
    // 获取话题列表
    Route::get('/topics', [ForumController::class, 'getTopics']);
    // 获取话题详情
    Route::get('/topics/{id}', [ForumController::class, 'getTopicDetail']);
    // 获取话题评论
    Route::get('/topics/{id}/replies', [ForumController::class, 'getComments']);
    // 获取热门话题
    Route::get('/hot-topics', [ForumController::class, 'getHotTopics']);
    // 获取活跃用户
    Route::get('/active-users', [ForumController::class, 'getActiveUsers']);
    // 添加评论（需要内容审核）
    Route::post('/topics/{id}/replies', [ForumController::class, 'addComment'])
        ->middleware(['auth:sanctum', 'content.moderation:comment,content']);
    // 论坛话题发布接口（需要内容审核）
    Route::post('/topics', [ForumController::class, 'createTopic'])
        ->middleware(['auth:sanctum', 'content.moderation:forum,title,content']);
    // 点赞话题
    Route::post('/topics/{id}/like', [ForumController::class, 'likeTopic'])
        ->middleware('auth:sanctum');
    // 收藏话题
    Route::post('/topics/{id}/favorite', [ForumController::class, 'favoriteTopic'])
        ->middleware('auth:sanctum');
    // 获取推荐话题（点赞数前五）
    Route::get('/recommendations', [ForumController::class, 'getRecommendations']);
    // 获取话题导航（上下篇）
    Route::get('/topics/{id}/navigation', [ForumController::class, 'getTopicNavigation']);
    // 获取用户对话题的状态（点赞、收藏）
    Route::get('/topics/{id}/user-status', [ForumController::class, 'getUserTopicStatus'])
        ->middleware('auth:sanctum');
    // 获取用户收藏的话题列表
    Route::get('/favorites', [ForumController::class, 'getUserFavorites'])
        ->middleware('auth:sanctum');
});

// 活动公开接口
Route::prefix('activities')->group(function () {
    Route::get('/', [ActivityController::class, 'index']);
    Route::get('/{id}', [ActivityController::class, 'show']);
});

// 上传接口（需要图片审核）
Route::prefix('upload')->group(function () {
    Route::post('image', [UploadController::class, 'uploadImage'])
        ->middleware('auth:sanctum');
});

// 测试接口
Route::prefix('test')->group(function () {
    Route::get('create-test-user', [TestController::class, 'createTestUser']);
    // 测试LostFound模型
    Route::get('lostfound/{id}', function ($id) {
        $lostFound = \App\Models\LostFound::find($id);
        if (!$lostFound) {
            return response()->json(['code' => 404, 'msg' => '失物招领不存在']);
        }
        return response()->json(['code' => 200, 'data' => $lostFound, 'msg' => 'success']);
    });
    // 获取所有LostFound记录
    Route::get('lostfound-list', function () {
        $lostFounds = \App\Models\LostFound::all();
        return response()->json(['code' => 200, 'data' => $lostFounds, 'msg' => 'success']);
    });
});

// 需要登录的接口
Route::middleware('auth:sanctum')->group(function () {
    // 认证相关
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'getUserInfo']);
        Route::get('check-token', [AuthController::class, 'checkToken']);
        Route::post('update', [AuthController::class, 'update']);
    });

    // 商品相关接口（发布和更新需要内容审核）
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store'])
            ->middleware('content.moderation:product,title,description');
        Route::put('/{id}', [ProductController::class, 'update'])
            ->middleware('content.moderation:product,title,description');
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::get('/my/list', [ProductController::class, 'myProducts']);
    });

    // 聊天相关接口（聊天消息需要内容审核）
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::post('/conversations', [ChatController::class, 'createConversation']);
        Route::get('/conversations/{id}/messages', [ChatController::class, 'getMessages']);
        Route::post('/conversations/{id}/messages', [ChatController::class, 'sendMessage'])
            ->middleware('content.moderation:chat,content');
    });

    // DIY纪念册相关接口（标题需要内容审核）
    Route::prefix('albums')->group(function () {
        Route::get('/', [AlbumController::class, 'index']);
        Route::post('/', [AlbumController::class, 'store'])
            ->middleware('content.moderation:album,title');
        Route::get('/{id}', [AlbumController::class, 'show']);
    });

    // 收藏相关接口
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{productId}', [FavoriteController::class, 'destroy']);
        Route::get('/check/{productId}', [FavoriteController::class, 'check']);
        Route::post('/check/batch', [FavoriteController::class, 'batchCheck']); // 批量检查收藏状态
    });

    // 评价相关接口（评价内容需要审核）
    Route::prefix('ratings')->group(function () {
        Route::post('/', [RatingController::class, 'store'])
            ->middleware('content.moderation:rating,comment');
        Route::get('/user', [RatingController::class, 'userRatings']);
        Route::put('/{id}', [RatingController::class, 'update'])
            ->middleware('content.moderation:rating,comment');
        Route::delete('/{id}', [RatingController::class, 'destroy']);
    });

    // 失物招领相关接口（发布和更新需要内容审核）
    Route::prefix('lostfound')->group(function () {
        Route::post('/', [LostFoundController::class, 'store'])
            ->middleware('content.moderation:lostfound,title,description');
        Route::put('/{id}', [LostFoundController::class, 'update'])
            ->middleware('content.moderation:lostfound,title,description');
        Route::delete('/{id}', [LostFoundController::class, 'destroy']);
        Route::get('/my/list', [LostFoundController::class, 'myItems']);
    });

    // 活动相关接口（发布和更新需要内容审核）
    Route::prefix('activities')->group(function () {
        Route::post('/', [ActivityController::class, 'store'])
            ->middleware('content.moderation:activity,title,description');
        Route::put('/{id}', [ActivityController::class, 'update'])
            ->middleware('content.moderation:activity,title,description');
        Route::delete('/{id}', [ActivityController::class, 'destroy']);
        Route::get('/my/list', [ActivityController::class, 'myActivities']);
        Route::get('/categories', [ActivityController::class, 'categories']);
    });
});

// 管理员相关接口
Route::prefix('admin')->group(function () {
    // 管理员登录
    Route::post('login', [AdminController::class, 'login']);

    // 管理员需要登录的接口
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        // 商品管理
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminController::class, 'getProducts']);
            Route::delete('/{id}', [AdminController::class, 'deleteProduct']);
        });

        // 失物管理
        Route::prefix('lostfound')->group(function () {
            Route::get('/', [AdminController::class, 'getLostFound']);
            Route::delete('/{id}', [AdminController::class, 'deleteLostFound']);
        });

        // 话题管理
        Route::prefix('forum')->group(function () {
            Route::get('/', [AdminController::class, 'getForum']);
            Route::delete('/{id}', [AdminController::class, 'deleteForum']);
        });

        // 活动管理
        Route::prefix('activities')->group(function () {
            Route::get('/', [AdminController::class, 'getActivities']);
            Route::delete('/{id}', [AdminController::class, 'deleteActivity']);
        });

        // 用户管理
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
        });

        // 内容审核管理
        Route::prefix('moderation')->group(function () {
            // 获取待审核内容列表
            Route::get('/pending', [ContentModerationController::class, 'pending']);
            // 审核通过
            Route::post('/approve/{type}/{id}', [ContentModerationController::class, 'approve']);
            // 审核拒绝
            Route::post('/reject/{type}/{id}', [ContentModerationController::class, 'reject']);
        });
    });
});
