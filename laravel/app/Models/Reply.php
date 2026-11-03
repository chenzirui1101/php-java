<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_id',
        'content',
        'status',
        'moderated_at',
        'moderated_by',
        'reject_reason',
    ];

    /**
     * 回复所属话题
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * 回复所属用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
