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
        // 为products表添加审核状态字段
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'status')) {
                $table->string('status', 20)->default('approved')->after('description')->comment('审核状态:pending待审核,approved已通过,rejected已拒绝');
            }
            if (!Schema::hasColumn('products', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status')->comment('审核时间');
            }
            if (!Schema::hasColumn('products', 'moderated_by')) {
                $table->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at')->comment('审核人ID');
            }
            if (!Schema::hasColumn('products', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('moderated_by')->comment('拒绝原因');
            }
        });
        
        // 为topics表添加审核状态字段
        Schema::table('topics', function (Blueprint $table) {
            if (!Schema::hasColumn('topics', 'status')) {
                $table->string('status', 20)->default('approved')->after('replies_count')->comment('审核状态');
            }
            if (!Schema::hasColumn('topics', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('topics', 'moderated_by')) {
                $table->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at');
            }
            if (!Schema::hasColumn('topics', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('moderated_by');
            }
        });
        
        // 为replies表添加审核状态字段
        Schema::table('replies', function (Blueprint $table) {
            if (!Schema::hasColumn('replies', 'status')) {
                $table->string('status', 20)->default('approved')->after('content')->comment('审核状态');
            }
            if (!Schema::hasColumn('replies', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('replies', 'moderated_by')) {
                $table->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at');
            }
            if (!Schema::hasColumn('replies', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('moderated_by');
            }
        });
        
        // 为lost_found表添加审核字段（status已存在）
        Schema::table('lost_found', function (Blueprint $table) {
            if (!Schema::hasColumn('lost_found', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status')->comment('审核时间');
            }
            if (!Schema::hasColumn('lost_found', 'moderated_by')) {
                $table->unsignedBigInteger('moderated_by')->nullable()->after('moderated_at')->comment('审核人ID');
            }
            if (!Schema::hasColumn('lost_found', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('moderated_by')->comment('拒绝原因');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumnIfExists('status');
            $table->dropColumnIfExists('moderated_at');
            $table->dropColumnIfExists('moderated_by');
            $table->dropColumnIfExists('reject_reason');
        });
        
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumnIfExists('status');
            $table->dropColumnIfExists('moderated_at');
            $table->dropColumnIfExists('moderated_by');
            $table->dropColumnIfExists('reject_reason');
        });
        
        Schema::table('replies', function (Blueprint $table) {
            $table->dropColumnIfExists('status');
            $table->dropColumnIfExists('moderated_at');
            $table->dropColumnIfExists('moderated_by');
            $table->dropColumnIfExists('reject_reason');
        });
        
        Schema::table('lost_found', function (Blueprint $table) {
            $table->dropColumnIfExists('moderated_at');
            $table->dropColumnIfExists('moderated_by');
            $table->dropColumnIfExists('reject_reason');
        });
    }
};
