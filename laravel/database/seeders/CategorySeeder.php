<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 活动分类
        $activityCategories = [
            ['name' => '学术活动', 'type' => 'activity'],
            ['name' => '文体活动', 'type' => 'activity'],
            ['name' => '志愿服务', 'type' => 'activity'],
            ['name' => '社团活动', 'type' => 'activity'],
            ['name' => '其他活动', 'type' => 'activity'],
        ];

        foreach ($activityCategories as $category) {
            Category::create($category);
        }
    }
};