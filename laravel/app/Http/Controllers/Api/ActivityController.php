<?php

namespace App\Http\Controllers\Api;

use App\Models\Activity;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    // 获取活动列表
    public function index(Request $request)
    {
        $pageNum = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 12);
        $type = $request->input('category_id', 'all');
        $status = $request->input('status', 'all');
        $keyword = $request->input('keyword', '');

        $query = Activity::where('moderation_status', 'approved');

        if ($keyword) {
            $query->where('title', 'like', '%' . $keyword . '%');
        }

        if ($type != 'all' && $type != 0) {
            $query->where('category_id', $type);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $pageNum);

        return response()->json([
            'code' => 200,
            'data' => $activities,
            'msg' => 'success'
        ]);
    }

    // 获取活动详情
    public function show($id)
    {
        $activity = Activity::where('id', $id)
            ->whereIn('moderation_status', ['approved', 'pending'])
            ->first();

        if (!$activity) {
            return response()->json([
                'code' => 404,
                'msg' => '活动不存在'
            ]);
        }

        return response()->json([
            'code' => 200,
            'data' => $activity,
            'msg' => 'success'
        ]);
    }

    // 发布活动
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|max:255',
                'poster' => 'required',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
            ]);

            $activity = Activity::create([
                'title' => $validatedData['title'],
                'poster' => $validatedData['poster'],
                'start_time' => $validatedData['start_time'],
                'end_time' => $validatedData['end_time'],
                'description' => $validatedData['description'] ?? '',
                'category_id' => $validatedData['category_id'],
                'user_id' => $request->user()->id,
                'moderation_status' => 'pending',
            ]);

            return response()->json([
                'code' => 200,
                'data' => $activity,
                'msg' => '发布成功，请等待管理员审核'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        }
    }

    // 更新活动
    public function update(Request $request, $id)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'code' => 404,
                'msg' => '活动不存在'
            ]);
        }

        if ($activity->user_id != $request->user()->id) {
            return response()->json([
                'code' => 403,
                'msg' => '无权更新此活动'
            ]);
        }

        $validatedData = $request->validate([
            'title' => 'required|max:255',
            'poster' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
        ]);

        $activity->update([
            'title' => $validatedData['title'],
            'poster' => $validatedData['poster'],
            'start_time' => $validatedData['start_time'],
            'end_time' => $validatedData['end_time'],
            'description' => $validatedData['description'] ?? '',
            'category_id' => $validatedData['category_id'],
            'moderation_status' => 'pending',
        ]);

        return response()->json([
            'code' => 200,
            'data' => $activity,
            'msg' => '更新成功，请等待管理员审核'
        ]);
    }

    // 删除活动
    public function destroy($id, Request $request)
    {
        $activity = Activity::find($id);

        if (!$activity) {
            return response()->json([
                'code' => 404,
                'msg' => '活动不存在'
            ]);
        }

        if ($activity->user_id != $request->user()->id) {
            return response()->json([
                'code' => 403,
                'msg' => '无权删除此活动'
            ]);
        }

        $activity->delete();

        return response()->json([
            'code' => 200,
            'msg' => '删除成功'
        ]);
    }

    // 获取用户发布的活动
    public function myActivities(Request $request)
    {
        $pageNum = $request->input('pageNum', 1);
        $pageSize = $request->input('pageSize', 12);

        $activities = Activity::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $pageNum);

        return response()->json([
            'code' => 200,
            'data' => $activities,
            'msg' => 'success'
        ]);
    }

    // 获取活动分类
    public function categories()
    {
        $categories = Category::where('type', 'activity')->get();

        return response()->json([
            'code' => 200,
            'data' => $categories,
            'msg' => 'success'
        ]);
    }
}
