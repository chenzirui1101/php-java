<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    protected $moderationService;
    
    public function __construct(ContentModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }
    
    /**
     * 上传图片（带内容审核）
     */
    public function uploadImage(Request $request)
    {
        try {
            // 验证请求
            $request->validate([
                'file' => 'required|image|max:2048', // 最大2MB
            ]);

            // 获取当前用户
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'code' => 401,
                    'msg' => '请先登录'
                ], 401);
            }

            $file = $request->file('file');
            
            // 先保存到临时目录进行审核
            $tempPath = $file->store('temp', 'local');
            $fullTempPath = storage_path('app/' . $tempPath);
            
            // 进行图片内容审核
            $moderationResult = $this->moderationService->moderateImage($fullTempPath);
            
            if (!$moderationResult['passed']) {
                // 审核不通过，删除临时文件
                Storage::delete($tempPath);
                
                return response()->json([
                    'code' => 422,
                    'msg' => '图片审核未通过：' . $moderationResult['reason']
                ], 422);
            }

            // 审核通过，移动到正式目录
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $userDir = 'images/' . $user->username;
            $disk = config('filesystems.default');

            if ($disk === 's3') {
                // 使用S3云存储
                $path = Storage::disk('s3')->putFileAs($userDir, $file, $filename, 'public');
                $url = Storage::disk('s3')->url($path);
            } else {
                // 使用本地存储
                if (!Storage::disk('public')->exists($userDir)) {
                    Storage::disk('public')->makeDirectory($userDir);
                }
                $path = $file->storeAs($userDir, $filename, 'public');
                $url = asset('storage/' . $path);
                
                // Windows系统：手动复制文件到public/storage目录
                if (PHP_OS_FAMILY === 'Windows') {
                    $sourcePath = storage_path('app/public/' . $path);
                    $destPath = public_path('storage/' . $path);
                    
                    // 确保目标目录存在
                    $destDir = dirname($destPath);
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }
                    
                    // 复制文件
                    if (file_exists($sourcePath)) {
                        copy($sourcePath, $destPath);
                    }
                }
            }
            
            // 删除临时文件
            Storage::delete($tempPath);

            return response()->json([
                'code' => 200,
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'disk' => $disk
                ],
                'msg' => '上传成功'
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
                'msg' => '上传失败: ' . $e->getMessage()
            ], 500);
        }
    }
}
