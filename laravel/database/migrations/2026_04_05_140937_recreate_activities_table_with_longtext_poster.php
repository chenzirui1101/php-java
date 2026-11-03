<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 删除现有的activities表
        Schema::dropIfExists('activities');
        
        // 重新创建activities表，将poster字段设置为longtext
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('poster'); // 使用longtext类型
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // 回滚时删除activities表
        Schema::dropIfExists('activities');
        
        // 重新创建原始结构的activities表
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('poster'); // 恢复为text类型
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users');
            $table->text('reject_reason')->nullable();
            $table->timestamps();
        });
    }
};