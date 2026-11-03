<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class FavoriteController extends Controller
{
    /**
     * 收藏商品
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = Auth::user();
        $productId = $request->input('product_id');

        // 检查是否已经收藏
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'code' => 400,
                'msg' => '商品已收藏',
                'data' => []
            ], 400);
        }

        // 创建收藏记录
        $favorite = Favorite::create([
            'user_id' => $user->id,
            'product_id' => $productId
        ]);

        return response()->json([
            'code' => 200,
            'msg' => '商品收藏成功',
            'data' => $favorite
        ]);
    }

    /**
     * 取消收藏商品
     */
    public function destroy($productId): JsonResponse
    {
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'code' => 404,
                'msg' => '未找到收藏记录',
                'data' => []
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'code' => 200,
            'msg' => '取消收藏成功',
            'data' => []
        ]);
    }

    /**
     * 获取用户收藏的商品列表
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 12);

            $favoriteProducts = $user->favoriteProducts()
                ->with(['user'])
                ->paginate($pageSize, ['*'], 'page', $pageNum);

            // 转换为和ProductController一致的数据格式
            $list = collect($favoriteProducts->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'price' => number_format($item->price, 2, '.', ''),
                    'category' => $this->getCategoryName($item->category_id),
                    'categoryId' => $item->category_id,
                    'coverImg' => $item->cover_img ?? '',
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'sellerName' => $item->user ? $item->user->name ?? $item->user->username : '未知用户',
                    'sellerUsername' => $item->user ? $item->user->username : '未知用户'
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $favoriteProducts->total()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '获取收藏商品失败: ' . $e->getMessage()
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
     * 检查商品是否已收藏
     */
    public function check($productId): JsonResponse
    {
        $user = Auth::user();
        $isFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'code' => 200,
            'msg' => '检查收藏状态成功',
            'data' => ['is_favorite' => $isFavorite]
        ]);
    }

    /**
     * 批量检查商品是否已收藏
     */
    public function batchCheck(Request $request): JsonResponse
    {
        $user = Auth::user();
        $productIds = $request->input('product_ids', []);

        // 获取用户收藏的商品ID列表
        $favoriteProductIds = Favorite::where('user_id', $user->id)
            ->whereIn('product_id', $productIds)
            ->pluck('product_id')
            ->toArray();

        // 构建结果数组
        $result = array_fill_keys($productIds, false);
        foreach ($favoriteProductIds as $id) {
            $result[$id] = true;
        }

        return response()->json([
            'code' => 200,
            'msg' => '批量检查收藏状态成功',
            'data' => $result
        ]);
    }
}
