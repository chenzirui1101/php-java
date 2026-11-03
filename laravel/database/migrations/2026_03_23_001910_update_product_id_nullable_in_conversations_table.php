<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 先删除旧列，再添加新列（允许为空）
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
        
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });
        
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('id');
        });
    }
};
