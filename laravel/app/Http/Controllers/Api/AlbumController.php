<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    /**
     * 保存纪念册
     * 前端请求体：{ title, cover_image(dataURL), canvas_data(JSON字符串), width, height }
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'nullable|string|max:255',
                'cover_image' => 'required|string',
                'canvas_data' => 'required',
                'width' => 'nullable|integer|min:1|max:4000',
                'height' => 'nullable|integer|min:1|max:4000',
            ]);

            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'code' => 401,
                    'msg' => '请先登录'
                ], 401);
            }

            // 把 Base64 合成图片写入 storage/app/public/albums/
            $coverPath = $this->saveCoverImage($validatedData['cover_image']);

            // canvas_data 前端传的是 JSON 字符串，这里兼容对象/数组
            $canvasData = $request->input('canvas_data');
            if (is_array($canvasData) || is_object($canvasData)) {
                $canvasData = json_encode($canvasData, JSON_UNESCAPED_UNICODE);
            }

            $album = Album::create([
                'user_id' => $user->id,
                'title' => $validatedData['title'] ?? '我的纪念册',
                'cover_image' => $coverPath,
                'canvas_data' => $canvasData,
                'width' => $validatedData['width'] ?? 800,
                'height' => $validatedData['height'] ?? 600,
            ]);

            return response()->json([
                'code' => 200,
                'data' => $album,
                'msg' => '保存成功'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'code' => 422,
                'msg' => '验证失败',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => '保存失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 我的纪念册列表
     */
    public function index(Request $request)
    {
        $pageNum = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 12);

        // 列表不返回体积较大的 canvas_data
        $albums = Album::where('user_id', $request->user()->id)
            ->select(['id', 'user_id', 'title', 'cover_image', 'width', 'height', 'created_at', 'updated_at'])
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $pageNum);

        return response()->json([
            'code' => 200,
            'data' => $albums,
            'msg' => 'success'
        ]);
    }

    /**
     * 纪念册详情（含 canvas_data，用于二次编辑）
     */
    public function show(Request $request, $id)
    {
        $album = Album::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$album) {
            return response()->json([
                'code' => 404,
                'msg' => '纪念册不存在'
            ]);
        }

        return response()->json([
            'code' => 200,
            'data' => $album,
            'msg' => 'success'
        ]);
    }

    /**
     * 解析 dataURL 并保存图片，返回相对路径
     * 支持 data:image/png;base64,xxx 以及纯 base64 字符串
     */
    private function saveCoverImage(string $dataUrl): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $matches)) {
            $ext = strtolower($matches[1]);
            $ext = $ext === 'jpeg' ? 'jpg' : $ext;
            $binary = base64_decode(substr($dataUrl, strlen($matches[0])));
        } else {
            $ext = 'png';
            $binary = base64_decode($dataUrl);
        }

        if ($binary === false || $binary === '') {
            throw new \Exception('图片数据解码失败');
        }

        $path = 'albums/' . date('Ymd') . '_' . Str::random(20) . '.' . $ext;
        Storage::disk('public')->put($path, $binary);

        // Windows 环境同步复制到 public/storage（与 UploadController 保持一致）
        if (PHP_OS_FAMILY === 'Windows') {
            $sourcePath = storage_path('app/public/' . $path);
            $destPath = public_path('storage/' . $path);
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            if (file_exists($sourcePath)) {
                copy($sourcePath, $destPath);
            }
        }

        return $path;
    }
}
