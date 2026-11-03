<?php

namespace Database\Seeders;

use App\Models\LostFound;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LostFoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取所有用户
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('没有用户数据，先创建测试用户...');
            // 创建一些测试用户
            $user1 = User::create([
                'name' => '小明',
                'username' => 'xiaoming',
                'email' => 'xiaoming@example.com',
                'password' => bcrypt('123456')
            ]);

            $user2 = User::create([
                'name' => '小红',
                'username' => 'xiaohong',
                'email' => 'xiaohong@example.com',
                'password' => bcrypt('123456')
            ]);

            $user3 = User::create([
                'name' => '小刚',
                'username' => 'xiaogang',
                'email' => 'xiaogang@example.com',
                'password' => bcrypt('123456')
            ]);

            $users = collect([$user1, $user2, $user3]);
        }

        // 失物招领数据模板
        $lostFoundItems = [
            // 寻物启事
            [
                'title' => '丢失蓝色书包一个',
                'type' => 'lost',
                'status' => 'looking',
                'location' => '图书馆三楼自习室',
                'description' => '书包是蓝色的，上面有Hello Kitty图案，里面有一本高等数学教材和一个红色笔袋。最后一次见到是在图书馆三楼靠窗的位置。',
                'cover_img' => 'https://picsum.photos/800/600?random=1'
            ],
            [
                'title' => '丢失小米手环7',
                'type' => 'lost',
                'status' => 'looking',
                'location' => '体育馆篮球场',
                'description' => '黑色小米手环7，表带是蓝色的，最后一次使用是在体育馆打篮球的时候。如果有人捡到，请联系我，必有重谢！',
                'cover_img' => 'https://picsum.photos/800/600?random=2'
            ],
            [
                'title' => '丢失身份证',
                'type' => 'lost',
                'status' => 'looking',
                'location' => '食堂二楼',
                'description' => '姓名：张三，身份证号码：110101********1234，最后一次使用是在食堂二楼买饭的时候。如果捡到请联系，非常感谢！',
                'cover_img' => 'https://picsum.photos/800/600?random=3'
            ],
            [
                'title' => '丢失笔记本电脑',
                'type' => 'lost',
                'status' => 'looking',
                'location' => '教学楼302教室',
                'description' => '银色联想笔记本电脑，外壳上有贴纸，最后一次使用是在302教室上课的时候。请捡到的同学联系我，非常重要！',
                'cover_img' => 'https://picsum.photos/800/600?random=4'
            ],
            [
                'title' => '丢失雨伞',
                'type' => 'lost',
                'status' => 'looking',
                'location' => '图书馆门口',
                'description' => '黑色折叠雨伞，上面有学校的logo，最后一次使用是在图书馆门口避雨的时候忘记拿了。',
                'cover_img' => 'https://picsum.photos/800/600?random=5'
            ],
            // 失物招领
            [
                'title' => '捡到钱包一个',
                'type' => 'found',
                'status' => 'found',
                'location' => '食堂一楼',
                'description' => '在食堂一楼捡到一个黑色钱包，里面有一些现金和几张银行卡，还有一张学生证。请失主联系我领取。',
                'cover_img' => 'https://picsum.photos/800/600?random=6'
            ],
            [
                'title' => '捡到手机一部',
                'type' => 'found',
                'status' => 'found',
                'location' => '图书馆自习室',
                'description' => '在图书馆自习室捡到一部蓝色iPhone 13，屏幕有保护膜，背面有手机壳。请失主联系我，提供手机型号和锁屏密码领取。',
                'cover_img' => 'https://picsum.photos/800/600?random=7'
            ],
            [
                'title' => '捡到耳机一副',
                'type' => 'found',
                'status' => 'found',
                'location' => '教学楼201教室',
                'description' => '在教学楼201教室捡到一副白色AirPods Pro，装在充电盒里。请失主联系我领取。',
                'cover_img' => 'https://picsum.photos/800/600?random=8'
            ],
            [
                'title' => '捡到眼镜一副',
                'type' => 'found',
                'status' => 'found',
                'location' => '图书馆阅览区',
                'description' => '在图书馆阅览区捡到一副黑色框架眼镜，镜片是近视镜片。请失主联系我，提供度数和镜框形状领取。',
                'cover_img' => 'https://picsum.photos/800/600?random=9'
            ],
            [
                'title' => '捡到校园卡一张',
                'type' => 'found',
                'status' => 'found',
                'location' => '学校门口',
                'description' => '在学校门口捡到一张校园卡，姓名：李四，学号：20210001，学院：计算机学院。请失主联系我领取。',
                'cover_img' => 'https://picsum.photos/800/600?random=10'
            ]
        ];

        // 创建失物招领信息
        foreach ($lostFoundItems as $itemData) {
            LostFound::create([
                'title' => $itemData['title'],
                'type' => $itemData['type'],
                'status' => $itemData['status'],
                'location' => $itemData['location'],
                'description' => $itemData['description'],
                'cover_img' => $itemData['cover_img'],
                'user_id' => $users->random()->id
            ]);
        }

        $this->command->info('失物招领数据添加完成！');
    }
}
