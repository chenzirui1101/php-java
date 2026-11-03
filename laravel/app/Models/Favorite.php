<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = ['user_id', 'product_id'];

    /**
     * 与用户的关联关系
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 与商品的关联关系
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
