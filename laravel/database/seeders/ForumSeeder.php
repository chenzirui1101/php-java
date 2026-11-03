<?php

namespace Database\Seeders;

use App\Models\Reply;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
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

        // 话题内容模板
        $topics = [
            [
                'title' => '校园里最美的角落在哪里？',
                'content' => '大家觉得咱们校园里最美的地方是哪里呢？我觉得图书馆后面的小花园很不错！春天的时候百花齐放，夏天的时候绿树成荫，秋天的时候落叶满地，冬天的时候银装素裹，每个季节都有不同的美景！'
            ],
            [
                'title' => '推荐一下食堂的招牌菜',
                'content' => '新同学刚入学，想了解一下食堂有什么好吃的，大家推荐一下吧！'
            ],
            [
                'title' => '期末考试复习资料分享',
                'content' => '马上就要期末考试了，大家有什么好的复习资料可以分享吗？'
            ],
            [
                'title' => '学校附近有什么好玩的地方？',
                'content' => '周末想出去放松一下，学校附近有什么好玩的地方吗？'
            ],
            [
                'title' => '如何提高学习效率？',
                'content' => '最近感觉学习效率很低，大家有什么好的方法可以分享吗？'
            ]
        ];

        // 评论内容模板
        $comments = [
            '我也觉得图书馆后面的小花园很美，尤其是春天的时候，樱花盛开，非常漂亮！',
            '我喜欢学校的湖心亭，那里很安静，适合看书和思考。',
            '食堂二楼的红烧肉很不错，推荐大家去试试！',
            '我觉得学习效率低的话，可以试试番茄工作法，效果不错！',
            '学校附近有一个湿地公园，风景很好，适合周末去散步。',
            '复习资料的话，可以去图书馆借一些历年真题，很有帮助！',
            '我喜欢去学校的体育馆，那里的健身设施很齐全。',
            '食堂一楼的早餐不错，种类很多，价格也便宜。',
            '我觉得学习的时候应该远离手机，这样可以提高注意力。',
            '学校附近有一个电影院，经常有学生优惠，推荐大家去看看！'
        ];

        // 创建话题
        foreach ($topics as $topicData) {
            $topic = Topic::create([
                'title' => $topicData['title'],
                'content' => $topicData['content'],
                'user_id' => $users->random()->id
            ]);

            // 为每个话题添加2-5条评论
            $commentCount = rand(2, 5);
            for ($i = 0; $i < $commentCount; $i++) {
                Reply::create([
                    'topic_id' => $topic->id,
                    'user_id' => $users->random()->id,
                    'content' => $comments[array_rand($comments)]
                ]);
            }

            // 更新话题的回复数量
            $topic->update([
                'replies_count' => $topic->replies()->count()
            ]);
        }

        $this->command->info('论坛数据添加完成！');
    }
}
