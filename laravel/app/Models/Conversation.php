<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LostFound;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'resource_type',
        'resource_id',
        'last_message',
        'last_message_time',
        'unread_count'
    ];

    // 关联用户（买家）
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 关联卖家
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // 关联商品
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 关联失物招领
    public function lostFound()
    {
        return $this->belongsTo(LostFound::class, 'resource_id')->where('resource_type', 'lost_found');
    }

    // 动态关联资源
    public function resource()
    {
        return $this->morphTo(__FUNCTION__, 'resource_type', 'resource_id');
    }

    // 关联消息
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
