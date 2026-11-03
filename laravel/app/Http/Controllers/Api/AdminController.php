<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\LostFound;
use App\Models\Topic;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * 管理员登录
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ]);

            // 只允许admin用户登录
            if ($validated['username'] !== 'admin') {
                return response()->json([
                    'code' => 403,
                    'msg' => '非管理员账号，禁止登录',
                    'errors' => ['username' => ['非管理员账号，禁止登录']]
                ], 403);
            }

            if (!Auth::attempt([
                'username' => $validated['username'],
                'password' => $validated['password']
            ])) {
                return response()->json([
                    'code' => 422,
                    'msg' => '用户名或密码错误',
                    'errors' => ['password' => ['用户名或密码错误']]
                ], 422);
            }

            $user = Auth::user();
            $token = $user->createToken('admin_token')->plainTextToken;

            return response()->json([
                'code' => 200,
                'msg' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('管理员登录错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '登录失败',
                'errors' => ['login' => ['登录失败，请重试']]
            ], 500);
        }
    }

    /**
     * 获取所有商品列表（管理员）
     */
    public function getProducts(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 12);
            $keyword = $request->input('keyword', '');

            // 直接查询商品数据，不使用with('user')预加载
            $query = Product::orderBy('created_at', 'desc');

            // 添加搜索功能
            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                      // 暂时移除用户相关搜索，避免关联查询错误
                      // ->orWhereHas('user', function ($q) use ($keyword) {
                      //     $q->where('name', 'like', '%' . $keyword . '%')
                      //       ->orWhere('username', 'like', '%' . $keyword . '%');
                      // });
                });
            }

            $products = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            $list = collect($products->items())->map(function ($item) {
                // 直接返回商品信息，不包含用户详情
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'price' => number_format($item->price, 2, '.', ''),
                    'category' => $this->getCategoryName($item->category_id),
                    'categoryId' => $item->category_id,
                    'coverImg' => $item->cover_img ?? '',
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'sellerName' => '未知用户', // 暂时固定为未知用户
                    'sellerUsername' => '未知用户', // 暂时固定为未知用户
                    'sellerId' => $item->user_id
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $products->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('获取商品列表错误：' . $e->getMessage());
            Log::error('错误堆栈：' . $e->getTraceAsString());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误',
                'error' => $e->getMessage() // 返回具体错误信息
            ], 500);
        }
    }

    /**
     * 删除商品（管理员）
     */
    public function deleteProduct($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'code' => 404,
                    'msg' => '商品不存在'
                ], 404);
            }

            $product->delete();

            return response()->json([
                'code' => 200,
                'msg' => '商品删除成功'
            ]);

        } catch (\Exception $e) {
            Log::error('删除商品错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '删除失败'
            ], 500);
        }
    }

    /**
     * 获取所有用户列表（管理员）
     */
    public function getUsers(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 12);
            $keyword = $request->input('keyword', '');

            $query = User::orderBy('created_at', 'desc');

            // 添加搜索功能
            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('username', 'like', '%' . $keyword . '%')
                      ->orWhere('email', 'like', '%' . $keyword . '%');
                });
            }

            $users = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            $list = collect($users->items())->map(function ($item) {
                // 计算用户的商品数量
                $productCount = Product::where('user_id', $item->id)->count();
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'username' => $item->username,
                    'email' => $item->email,
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'productCount' => $productCount
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $users->total()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('获取用户列表错误：' . $e->getMessage());
            Log::error('错误堆栈：' . $e->getTraceAsString());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误',
                'error' => $e->getMessage() // 返回具体错误信息
            ], 500);
        }
    }

    /**
     * 获取分类名称
     */
    private function getCategoryName($id)
    {
        $categories = [
            1 => '数码产品',
            2 => '生活用品',
            3 => '学习资料',
            4 => '服饰鞋包',
            5 => '其他'
        ];
        return $categories[$id] ?? '其他';
    }

    /**
     * 获取失物列表（管理员）
     */
    public function getLostFound(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 10);
            $keyword = $request->input('keyword', '');

            $query = LostFound::orderBy('created_at', 'desc');

            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }

            $items = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            $list = collect($items->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'coverImg' => $item->cover_img ?? '',
                    'publisherName' => '未知用户',
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d H:i') : ''
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $items->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取失物列表错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误'
            ], 500);
        }
    }

    /**
     * 删除失物（管理员）
     */
    public function deleteLostFound($id)
    {
        try {
            $item = LostFound::find($id);
            if (!$item) {
                return response()->json(['code' => 404, 'msg' => '失物不存在'], 404);
            }
            $item->delete();
            return response()->json(['code' => 200, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除失物错误：' . $e->getMessage());
            return response()->json(['code' => 500, 'msg' => '删除失败'], 500);
        }
    }

    /**
     * 获取话题列表（管理员）
     */
    public function getForum(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 10);
            $keyword = $request->input('keyword', '');

            $query = Topic::orderBy('created_at', 'desc');

            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('content', 'like', '%' . $keyword . '%');
                });
            }

            $topics = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            $list = collect($topics->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => '论坛',
                    'publisherName' => '未知用户',
                    'replyCount' => $item->replies_count ?? 0,
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d H:i') : ''
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $topics->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取话题列表错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误'
            ], 500);
        }
    }

    /**
     * 删除话题（管理员）
     */
    public function deleteForum($id)
    {
        try {
            $topic = Topic::find($id);
            if (!$topic) {
                return response()->json(['code' => 404, 'msg' => '话题不存在'], 404);
            }
            $topic->delete();
            return response()->json(['code' => 200, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除话题错误：' . $e->getMessage());
            return response()->json(['code' => 500, 'msg' => '删除失败'], 500);
        }
    }

    /**
     * 获取活动列表（管理员）
     */
    public function getActivities(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 10);
            $keyword = $request->input('keyword', '');

            $query = Activity::orderBy('created_at', 'desc');

            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }

            $activities = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            $list = collect($activities->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'coverImg' => $item->poster ?? '',
                    'publisherName' => '未知用户',
                    'activityTime' => $item->start_time ? $item->start_time->format('Y-m-d H:i') : '',
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d H:i') : ''
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $activities->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('获取活动列表错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误'
            ], 500);
        }
    }

    /**
     * 删除活动（管理员）
     */
    public function deleteActivity($id)
    {
        try {
            $activity = Activity::find($id);
            if (!$activity) {
                return response()->json(['code' => 404, 'msg' => '活动不存在'], 404);
            }
            $activity->delete();
            return response()->json(['code' => 200, 'msg' => '删除成功']);
        } catch (\Exception $e) {
            Log::error('删除活动错误：' . $e->getMessage());
            return response()->json(['code' => 500, 'msg' => '删除失败'], 500);
        }
    }
}
