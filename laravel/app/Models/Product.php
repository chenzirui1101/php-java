<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category_id',
        'price',
        'cover_img',
        'description',
        'user_id',
        'status',
        'moderated_at',
        'moderated_by',
        'reject_reason',
    ];

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联收藏记录
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 被哪些用户收藏
     */
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * 商品的评价关联
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * 商品的平均评分
     */
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * 评价数量
     */
    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }

    /**
     * cover_img访问器，确保返回完整URL
     */
    public function getCoverImgAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // 如果已经是完整URL，直接返回
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // 确保路径格式正确，没有多余的斜杠
        $path = ltrim($value, '/');
        // 使用Laravel的URL辅助函数生成完整URL
        return url('storage/' . $path);
    }

    /**
     * 为了兼容前端，添加image访问器，返回和cover_img相同的值
     */
    public function getImageAttribute()
    {
        return $this->cover_img;
    }
}
