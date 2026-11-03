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
        if (!Schema::hasTable('albums')) {
            Schema::create('albums', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title', 255)->default('我的纪念册');
                // 合成图片在 public 磁盘中的相对路径，例如 albums/xxx.png
                $table->string('cover_image', 500)->nullable();
                // 画布元素配置（JSON 字符串），用于二次编辑
                $table->json('canvas_data')->nullable();
                $table->unsignedInteger('width')->default(800);
                $table->unsignedInteger('height')->default(600);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
