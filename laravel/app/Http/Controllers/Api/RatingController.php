<?php

namespace App\Http\Controllers\Api;

use App\Models\Rating;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RatingController extends Controller
{
    /**
     * 创建商品评价
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        $productId = $request->input('product_id');
        $rating = $request->input('rating');
        $comment = $request->input('comment');

        // 检查是否已经评价过该商品
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingRating) {
            return response()->json([
                'code' => 400,
                'msg' => '您已评价过该商品',
                'data' => []
            ], 400);
        }

        // 创建评价
        $rating = Rating::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $rating,
            'comment' => $comment
        ]);

        return response()->json([
            'code' => 200,
            'msg' => '评价成功',
            'data' => $rating
        ]);
    }

    /**
     * 获取商品评价列表
     */
    public function productRatings(Request $request, $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'code' => 404,
                'msg' => '商品不存在',
                'data' => []
            ], 404);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $ratings = $product->ratings()
            ->with('user')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'code' => 200,
            'msg' => '获取商品评价列表成功',
            'data' => $ratings
        ]);
    }

    /**
     * 获取用户评价列表
     */
    public function userRatings(Request $request): JsonResponse
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $ratings = $user->ratings()
            ->with('product')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'code' => 200,
            'msg' => '获取用户评价列表成功',
            'data' => $ratings
        ]);
    }

    /**
     * 更新评价
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ]);

        $user = Auth::user();
        $rating = Rating::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'code' => 404,
                'msg' => '评价不存在或无权限修改',
                'data' => []
            ], 404);
        }

        $rating->update([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment')
        ]);

        return response()->json([
            'code' => 200,
            'msg' => '评价更新成功',
            'data' => $rating
        ]);
    }

    /**
     * 删除评价
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $rating = Rating::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'code' => 404,
                'msg' => '评价不存在或无权限删除',
                'data' => []
            ], 404);
        }

        $rating->delete();

        return response()->json([
            'code' => 200,
            'msg' => '评价删除成功',
            'data' => []
        ]);
    }
}
