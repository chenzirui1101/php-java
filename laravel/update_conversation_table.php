<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // 直接使用SQL语句修改product_id字段为允许为空
    DB::statement('ALTER TABLE conversations MODIFY product_id BIGINT UNSIGNED NULL');

    echo "Successfully updated conversations table: product_id is now nullable.\n";

    // 验证修改是否成功
    $columns = DB::select('SHOW COLUMNS FROM conversations WHERE Field = "product_id"');
    if ($columns[0]->Null === 'YES') {
        echo "Verification successful: product_id is nullable.\n";
    } else {
        echo "Verification failed: product_id is still not nullable.\n";
    }

} catch (Exception $e) {
    echo "Error updating conversations table: " . $e->getMessage() . "\n";
}
