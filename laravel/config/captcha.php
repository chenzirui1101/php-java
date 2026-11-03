<?php

return [
    'default' => [
        'length' => 4, // 验证码位数
        'width' => 120, // 图片宽度
        'height' => 40, // 图片高度
        'quality' => 90, // 图片质量
        'math' => false, // 关闭数学验证码，用字符
        'sensitive' => false, // 验证码不区分大小写
        'bgImage' => false, // 无背景图
        'bgColor' => '#ffffff', // 背景白色
        'fontColors' => ['#000000', '#ff0000', '#006600'], // 字体颜色
        'shadow' => true, // 文字阴影
    ],
];
