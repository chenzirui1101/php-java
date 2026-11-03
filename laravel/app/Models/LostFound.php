<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LostFound extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lost_found';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'type',
        'status',
        'location',
        'cover_img',
        'description',
        'user_id',
        'moderation_status',
        'moderated_at',
        'moderated_by',
        'reject_reason',
    ];

    /**
     * Get the user who created the lost/found item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
