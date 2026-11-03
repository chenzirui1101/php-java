<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 获取表结构信息
$columns = DB::select('SHOW COLUMNS FROM conversations');
echo "Conversations Table Structure:\n";
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n";
echo "| Field            | Type                            | Null | Key | Default | Extra          |\n";
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n";

foreach ($columns as $column) {
    printf("| %-16s | %-31s | %-4s | %-3s | %-7s | %-16s |\n",
        $column->Field,
        $column->Type,
        $column->Null,
        $column->Key,
        $column->Default ?? '',
        $column->Extra
    );
}
echo "+------------------+---------------------------------+------+-----+---------+----------------+\n\n";

// 获取唯一索引信息
$indexes = DB::select('SHOW INDEX FROM conversations WHERE Non_unique = 0');
echo "Unique Indexes:\n";
foreach ($indexes as $index) {
    echo "Index Name: {$index->Key_name}, Columns: {$index->Column_name}\n";
}
