<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 可批量赋值的字段
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'role',
    ];

    /**
     * 隐藏的敏感字段
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 字段类型转换 - 关键修复
     * 对于 Laravel 9 及以下版本，不要使用 'hashed'
     * 让 Laravel 自动处理密码加密
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 删除这一行，或者注释掉
        // 'password' => 'hashed',
    ];

    /**
     * 如果需要，可以自定义密码获取器
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * 收藏关联
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * 收藏的商品关联（通过中间表）
     */
    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * 用户的评价关联
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * 用户发布的商品关联
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * 检查用户是否是管理员
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * 头像访问器，确保返回完整URL
     */
    public function getAvatarAttribute($value)
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
}
