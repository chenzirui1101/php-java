<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\LostFound;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class ChatController extends Controller
{
    // 获取用户的会话列表
    public function getConversations(Request $request)
    {
        $user = $request->user();

        // 获取用户作为买家或卖家的会话
        $conversations = Conversation::where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // 作为买家的会话
                      ->orWhere('seller_id', $user->id); // 作为卖家的会话
            })
            ->with(['seller', 'user']) // 加载卖家和买家信息
            ->orderBy('last_message_time', 'desc')
            ->get();

        // 处理数据，为每个会话添加other_user字段
        $conversations->each(function ($conversation) use ($user) {
            // 只保留必要的产品信息，不加载完整关联
            if ($conversation->product_id) {
                $conversation->product = [
                    'id' => $conversation->product_id
                ];
            }

            // 确定聊天对象
            if ($user->id === $conversation->user_id) {
                // 当前用户是买家，聊天对象是卖家
                $otherUser = $conversation->seller;
            } else {
                // 当前用户是卖家，聊天对象是买家
                $otherUser = $conversation->user;
            }

            // 添加other_user字段，包含聊天对象的必要信息
            $conversation->other_user = [
                'id' => $otherUser->id,
                'username' => $otherUser->name,
                'avatar' => $otherUser->avatar
            ];

            // 移除可能导致问题的关联
            unset($conversation->seller);
            unset($conversation->user);
            unset($conversation->lostFound);
            unset($conversation->resource);
        });

        return response()->json([
            'code' => 200,
            'data' => $conversations,
            'msg' => '获取会话列表成功'
        ]);
    }

    // 创建或获取会话
    public function createConversation(Request $request)
    {
        $user = $request->user();
        $resourceType = $request->input('resource_type', 'product');
        $resourceId = $request->input('resource_id');
        $productId = $request->input('product_id');

        // 调试日志
        Log::debug('Create conversation request:', [
            'user_id' => $user->id,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'product_id' => $productId,
            'request_all' => $request->all()
        ]);

        $sellerId = null;
        $resource = null;

        // 根据资源类型获取对应的资源和卖家ID
        switch ($resourceType) {
            case 'product':
                // 验证商品是否存在，优先使用product_id，然后是resource_id
                $productId = $productId ?: $resourceId;
                $resource = Product::find($productId);
                if (!$resource) {
                    return response()->json([
                        'code' => 404,
                        'msg' => '商品不存在'
                    ]);
                }
                $sellerId = $resource->user_id;
                $resourceId = $productId;
                break;

            case 'lost_found':
                // 验证resourceId是否存在
                if (empty($resourceId)) {
                    Log::debug('resourceId is empty for lost_found type');
                    return response()->json([
                        'code' => 400,
                        'msg' => '资源ID不能为空'
                    ]);
                }

                // 验证resourceId是否为数字
                if (!is_numeric($resourceId)) {
                    Log::debug('resourceId is not numeric:', ['resourceId' => $resourceId]);
                    return response()->json([
                        'code' => 400,
                        'msg' => '资源ID格式不正确'
                    ]);
                }

                // 调试日志
                Log::debug('Looking for LostFound with ID:', ['id' => $resourceId]);

                // 验证失物招领是否存在
                $resource = LostFound::find((int)$resourceId);
                if (!$resource) {
                    // 调试日志
                    Log::debug('LostFound not found with ID:', ['id' => $resourceId]);

                    // 检查数据库中是否存在该记录
                    $count = LostFound::where('id', (int)$resourceId)->count();
                    Log::debug('LostFound count for ID:', ['id' => $resourceId, 'count' => $count]);

                    // 获取所有LostFound记录，查看ID范围
                    $allLostFounds = LostFound::all()->pluck('id');
                    Log::debug('All LostFound IDs:', ['ids' => $allLostFounds->toArray()]);

                    return response()->json([
                        'code' => 404,
                        'msg' => '失物招领不存在',
                        'available_ids' => $allLostFounds->toArray()
                    ]);
                }
                Log::debug('Found LostFound:', ['id' => $resource->id, 'title' => $resource->title]);
                $sellerId = $resource->user_id;
                break;

            case 'forum_comment':
                // 验证resourceId是否存在（这里resourceId是用户ID）
                if (empty($resourceId)) {
                    return response()->json([
                        'code' => 400,
                        'msg' => '用户ID不能为空'
                    ]);
                }

                // 验证用户是否存在
                $resource = User::find((int)$resourceId);
                if (!$resource) {
                    return response()->json([
                        'code' => 404,
                        'msg' => '用户不存在'
                    ]);
                }
                $sellerId = $resource->id;
                break;

            default:
                return response()->json([
                    'code' => 400,
                    'msg' => '不支持的资源类型'
                ]);
        }

        // 防止与自己聊天
        if ($sellerId == $user->id) {
            return response()->json([
                'code' => 400,
                'msg' => '不能与自己聊天'
            ]);
        }

        // 查找是否已存在会话
        $query = Conversation::where('user_id', $user->id)
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId);

        // 对于商品类型，保持向后兼容
        if ($resourceType == 'product') {
            $query->orWhere(function($q) use ($user, $productId) {
                $q->where('user_id', $user->id)
                  ->where('product_id', $productId)
                  ->whereNull('resource_type');
            });
        }

        $conversation = $query->first();

        // 如果不存在，创建新会话
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'seller_id' => $sellerId,
                'product_id' => $resourceType == 'product' ? $resourceId : null,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId
            ]);
        }

        // 加载关联数据
        $conversation->load('seller');

        // 简单处理数据，避免关联问题
        if ($conversation->product_id) {
            $conversation->product = [
                'id' => $conversation->product_id
            ];
        }
        // 移除可能导致问题的关联
        unset($conversation->lostFound);
        unset($conversation->resource);

        return response()->json([
            'code' => 200,
            'data' => $conversation,
            'msg' => '获取会话成功'
        ]);
    }

    // 获取会话的消息列表
    public function getMessages(Request $request, $conversationId)
    {
        $user = $request->user();

        // 验证会话是否存在且用户有权限（买家或卖家）
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // 作为买家
                      ->orWhere('seller_id', $user->id); // 作为卖家
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'code' => 404,
                'msg' => '会话不存在或无权限'
            ]);
        }

        // 获取消息列表
        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // 更新未读消息状态
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 重置会话的未读消息数
        $conversation->update(['unread_count' => 0]);

        return response()->json([
            'code' => 200,
            'data' => $messages,
            'msg' => '获取消息列表成功'
        ]);
    }

    // 发送消息
    public function sendMessage(Request $request, $conversationId)
    {
        $user = $request->user();
        $content = $request->input('content');

        // 验证消息内容
        if (empty($content)) {
            return response()->json([
                'code' => 400,
                'msg' => '消息内容不能为空'
            ]);
        }

        // 验证会话是否存在且用户有权限（买家或卖家）
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // 作为买家
                      ->orWhere('seller_id', $user->id); // 作为卖家
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'code' => 404,
                'msg' => '会话不存在或无权限'
            ]);
        }

        // 创建消息
        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $user->id,
            'content' => $content
        ]);

        // 更新会话的最后消息信息
        $conversation->update([
            'last_message' => $content,
            'last_message_time' => now(),
            'unread_count' => $conversation->unread_count + 1
        ]);

        // 加载发送者信息
        $message->load('sender');

        // 暂时注释掉广播事件，避免Redis依赖问题
        // event(new ppventsessageSent($message->toArray(), $conversationId));

        return response()->json([
            'code' => 200,
            'data' => $message,
            'msg' => '发送消息成功'
        ]);
    }
}
