<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'is_read'
    ];

    // 关联会话
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // 关联发送者
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
