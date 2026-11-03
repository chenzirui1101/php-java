<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // 先删除旧的唯一索引
            $table->dropUnique(['user_id', 'product_id']);

            // 添加资源类型和资源ID字段，用于支持多种类型的会话
            $table->string('resource_type')->nullable()->comment('资源类型：product, lost_found, forum等');
            $table->unsignedBigInteger('resource_id')->nullable()->comment('资源ID，根据resource_type关联不同表');

            // 添加新的唯一索引，根据资源类型和资源ID区分
            $table->unique(['user_id', 'resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // 删除新增字段
            $table->dropColumn(['resource_type', 'resource_id']);
        });
    }
};
