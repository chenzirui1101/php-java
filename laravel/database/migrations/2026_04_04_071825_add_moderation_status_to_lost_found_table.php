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
        Schema::table('lost_found', function (Blueprint $table) {
            if (!Schema::hasColumn('lost_found', 'moderation_status')) {
                $table->string('moderation_status', 20)->default('pending')->after('status')->comment('审核状态:pending待审核,approved已通过,rejected已拒绝');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lost_found', function (Blueprint $table) {
            $table->dropColumnIfExists('moderation_status');
        });
    }
};
