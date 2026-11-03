<?php

namespace App\Http\Controllers\Api;

use App\Models\LostFound;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class LostFoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LostFound::query();

        // Apply filters if provided
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('description', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 只显示已审核通过的内容
        $query->where('moderation_status', 'approved');

        // Paginate the results
        $lostFoundItems = $query->with('user')
                                ->orderBy('created_at', 'desc')
                                ->paginate(12);

        // Format the response
        $data = [
            'list' => collect($lostFoundItems->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $item->status,
                    'location' => $item->location,
                    'coverImg' => $this->getImageUrl($item->cover_img),
                    'description' => $item->description,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    'user' => $item->user ? [
                        'id' => $item->user->id,
                        'username' => $item->user->username,
                        'name' => $item->user->name,
                    ] : null,
                ];
            })->values()->toArray(),
            'total' => $lostFoundItems->total(),
            'pageNum' => $lostFoundItems->currentPage(),
            'pageSize' => $lostFoundItems->perPage(),
        ];

        return response()->json(['code' => 200, 'data' => $data, 'msg' => 'success']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:lost,found',
            'status' => 'required|in:looking,found',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Create the lost/found item
        $lostFound = LostFound::create([
            'title' => $request->title,
            'type' => $request->type,
            'status' => $request->status,
            'location' => $request->location,
            'cover_img' => $request->cover_img,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'moderation_status' => 'pending'
        ]);

        return response()->json(['code' => 200, 'data' => $lostFound, 'msg' => '发布成功，等待管理员审核']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // 手动查找模型实例，显示已审核通过或待审核的内容
        $lostFound = LostFound::whereIn('moderation_status', ['approved', 'pending'])->find($id);

        // 确保模型实例存在
        if (!$lostFound) {
            return response()->json(['code' => 404, 'msg' => '失物招领不存在'], 404);
        }

        // 加载关联的用户信息
        $lostFound->load('user');

        // 格式化日期，确保前端能正确解析
        if ($lostFound->created_at) {
            $lostFound->created_at = $lostFound->created_at->format('Y-m-d H:i:s');
        }
        if ($lostFound->updated_at) {
            $lostFound->updated_at = $lostFound->updated_at->format('Y-m-d H:i:s');
        }

        // 转换图片URL
        $lostFound->coverImg = $this->getImageUrl($lostFound->cover_img);

        // 移除原有的cover_img字段，使用coverImg
        $data = [
            'id' => $lostFound->id,
            'title' => $lostFound->title,
            'type' => $lostFound->type,
            'status' => $lostFound->status,
            'location' => $lostFound->location,
            'coverImg' => $lostFound->coverImg,
            'description' => $lostFound->description,
            'created_at' => $lostFound->created_at,
            'updated_at' => $lostFound->updated_at,
            'user' => $lostFound->user ? [
                'id' => $lostFound->user->id,
                'username' => $lostFound->user->username,
                'name' => $lostFound->user->name,
            ] : null,
        ];

        return response()->json(['code' => 200, 'data' => $data, 'msg' => 'success']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LostFound $lostFound)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:lost,found',
            'status' => 'required|in:looking,found',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Update the lost/found item
        $lostFound->update([
            'title' => $request->title,
            'type' => $request->type,
            'status' => $request->status,
            'location' => $request->location,
            'cover_img' => $request->cover_img,
            'description' => $request->description,
            'moderation_status' => 'pending' // 更新后重新审核
        ]);

        return response()->json(['code' => 200, 'data' => $lostFound, 'msg' => '更新成功，等待管理员审核']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LostFound $lostFound)
    {
        $lostFound->delete();
        return response()->json(['code' => 200, 'msg' => '删除成功']);
    }

    /**
     * 获取我的失物招领
     */
    public function myItems()
    {
        try {
            $items = LostFound::with('user')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            $list = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'status' => $item->status,
                    'location' => $item->location,
                    'coverImg' => $this->getImageUrl($item->cover_img),
                    'description' => $item->description,
                    'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
                    'moderation_status' => $item->moderation_status,
                    'moderation_status_text' => $this->getStatusText($item->moderation_status)
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
            return response()->json([
                'code' => 500,
                'msg' => '获取失败'
            ], 500);
        }
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
