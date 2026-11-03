<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * 商品列表（分页+分类筛选+搜索）
     */
    public function index(Request $request)
    {
        try {
            $pageNum = $request->input('pageNum', 1);
            $pageSize = $request->input('pageSize', 12);
            $categoryId = $request->input('categoryId', 0);
            $keyword = $request->input('keyword', '');

            $query = Product::with('user')->where('status', 'approved')->orderBy('created_at', 'desc');

            if ($categoryId > 0) {
                $query->where('category_id', $categoryId);
            }

            // 添加搜索功能，搜索标题和描述
            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%');
                });
            }

            $products = $query->paginate($pageSize, ['*'], 'page', $pageNum);

            // 修复：使用 collect() 包装数组，或者直接使用集合方法
            $list = collect($products->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'price' => number_format($item->price, 2, '.', ''),
                    'category' => $this->getCategoryName($item->category_id),
                    'categoryId' => $item->category_id,
                    'coverImg' => $this->getImageUrl($item->cover_img),
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'sellerName' => $item->user ? $item->user->name ?? $item->user->username : '未知用户',
                    'sellerUsername' => $item->user ? $item->user->username : '未知用户'
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
            Log::error('商品列表错误：' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'code' => 500,
                'msg' => '服务器错误：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 商品详情
     */
    public function show($id)
    {
        try {
            // 对于编辑操作，需要获取所有状态的商品
            $product = Product::with('user')->find($id);

            if (!$product) {
                return response()->json([
                    'code' => 404,
                    'msg' => '商品不存在'
                ], 404);
            }
            
            // 检查权限：只有商品所有者或公开状态的商品可以访问
            if ($product->status !== 'approved' && $product->user_id !== auth()->id()) {
                return response()->json([
                    'code' => 403,
                    'msg' => '无权访问该商品'
                ], 403);
            }

            return response()->json([
                'code' => 200,
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => number_format($product->price, 2, '.', ''),
                    'category' => $this->getCategoryName($product->category_id),
                    'categoryId' => $product->category_id,
                    'coverImg' => $this->getImageUrl($product->cover_img),
                    'description' => $product->description ?? '',
                    'createTime' => $product->created_at ? $product->created_at->format('Y-m-d') : '',
                    'seller_id' => $product->user_id,
                    'sellerName' => $product->user ? $product->user->name ?? $product->user->username : '未知用户',
                    'sellerUsername' => $product->user ? $product->user->username : '未知用户'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('商品详情错误：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '服务器错误'
            ], 500);
        }
    }

    /**
     * 发布商品
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:50',
                'category_id' => 'required|integer|in:1,2,3,4,5',
                'price' => 'required|numeric|min:0.01|max:99999999.99',
                'cover_img' => 'required|string',
                'description' => 'required|string|max:500'
            ]);

            $product = Product::create([
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'cover_img' => $validated['cover_img'],
                'description' => $validated['description'],
                'user_id' => auth()->id(),
                'status' => 'pending'
            ]);

            return response()->json([
                'code' => 200,
                'msg' => '商品发布成功，等待管理员审核',
                'data' => $product
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('发布商品失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '发布失败'
            ], 500);
        }
    }

    /**
     * 删除商品
     */
    public function destroy($id)
    {
        try {
            $product = Product::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$product) {
                return response()->json([
                    'code' => 403,
                    'msg' => '无权删除该商品'
                ], 403);
            }

            $product->delete();

            return response()->json([
                'code' => 200,
                'msg' => '商品删除成功'
            ]);

        } catch (\Exception $e) {
            Log::error('删除商品失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '删除失败'
            ], 500);
        }
    }

    /**
     * 更新商品
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:50',
                'category_id' => 'required|integer|in:1,2,3,4,5',
                'price' => 'required|numeric|min:0.01|max:99999999.99',
                'cover_img' => 'required|string',
                'description' => 'required|string|max:500'
            ]);

            $product = Product::where('id', $id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$product) {
                return response()->json([
                    'code' => 403,
                    'msg' => '无权修改该商品'
                ], 403);
            }

            $product->update([
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'price' => $validated['price'],
                'cover_img' => $validated['cover_img'],
                'description' => $validated['description'],
                'status' => 'pending' // 修改后重新审核
            ]);

            return response()->json([
                'code' => 200,
                'msg' => '商品更新成功，等待管理员审核',
                'data' => $product
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('更新商品失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '更新失败'
            ], 500);
        }
    }

    /**
     * 我的发布商品
     */
    public function myProducts()
    {
        try {
            $products = Product::with('user')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            $list = $products->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'price' => number_format($item->price, 2, '.', ''),
                    'category' => $this->getCategoryName($item->category_id),
                    'categoryId' => $item->category_id,
                    'coverImg' => $this->getImageUrl($item->cover_img),
                    'createTime' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'status' => $item->status,
                    'statusText' => $this->getStatusText($item->status)
                ];
            })->values();

            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $list,
                    'total' => $list->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('获取我的商品失败：' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'msg' => '获取失败'
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
     * 获取图片完整URL
     */
    private function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return '';
        }
        
        // 如果已经是完整URL，直接返回
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }
        
        // 生成完整URL
        $path = ltrim($imagePath, '/');
        return url('storage/' . $path);
    }
}
