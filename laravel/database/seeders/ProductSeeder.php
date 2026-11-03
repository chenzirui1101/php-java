<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取第一个用户作为商品发布者
        $user = User::first();

        if (!$user) {
            // 如果没有用户，先创建一个
            $user = User::create([
                'name' => '测试用户',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => bcrypt('123456'),
            ]);
        }

        // 模拟商品数据
        $products = [
            [
                'title' => 'iPhone 13 Pro 128GB',
                'category_id' => 1,
                'price' => 5999.00,
                'cover_img' => 'https://picsum.photos/400/300?random=1',
                'description' => '9成新，无划痕，配件齐全，保修期内',
                'user_id' => $user->id
            ],
            [
                'title' => 'AirPods Pro 2代',
                'category_id' => 1,
                'price' => 1299.00,
                'cover_img' => 'https://picsum.photos/400/300?random=2',
                'description' => '全新未拆封，支持验货',
                'user_id' => $user->id
            ],
            [
                'title' => '小米空气净化器',
                'category_id' => 2,
                'price' => 399.00,
                'cover_img' => 'https://picsum.photos/400/300?random=3',
                'description' => '使用半年，功能正常，因搬家转让',
                'user_id' => $user->id
            ],
            [
                'title' => '大学英语四级词汇书',
                'category_id' => 3,
                'price' => 20.00,
                'cover_img' => 'https://picsum.photos/400/300?random=4',
                'description' => '几乎全新，无笔记，送听力光盘',
                'user_id' => $user->id
            ],
            [
                'title' => 'Nike Air Max 270',
                'category_id' => 4,
                'price' => 499.00,
                'cover_img' => 'https://picsum.photos/400/300?random=5',
                'description' => '尺码42，穿过几次，鞋底无磨损',
                'user_id' => $user->id
            ],
            [
                'title' => 'MacBook Pro 13英寸 M1',
                'category_id' => 1,
                'price' => 7999.00,
                'cover_img' => 'https://picsum.photos/400/300?random=6',
                'description' => '2021款，8GB+256GB，电池循环次数低',
                'user_id' => $user->id
            ],
            [
                'title' => '宜家书桌',
                'category_id' => 2,
                'price' => 199.00,
                'cover_img' => 'https://picsum.photos/400/300?random=7',
                'description' => '白色，120x60cm，可拆卸，自提',
                'user_id' => $user->id
            ],
            [
                'title' => '考研政治复习资料',
                'category_id' => 3,
                'price' => 50.00,
                'cover_img' => 'https://picsum.photos/400/300?random=8',
                'description' => '全套资料，包含历年真题和模拟题',
                'user_id' => $user->id
            ],
            [
                'title' => 'Adidas运动外套',
                'category_id' => 4,
                'price' => 299.00,
                'cover_img' => 'https://picsum.photos/400/300?random=9',
                'description' => 'L码，黑色，保暖性好',
                'user_id' => $user->id
            ],
            [
                'title' => '吉他',
                'category_id' => 5,
                'price' => 699.00,
                'cover_img' => 'https://picsum.photos/400/300?random=10',
                'description' => '民谣吉他，送琴包和拨片',
                'user_id' => $user->id
            ]
        ];

        // 插入商品数据
        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
