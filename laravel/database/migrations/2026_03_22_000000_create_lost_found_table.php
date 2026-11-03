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
        Schema::create('lost_found', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // lost 寻物启事，found 失物招领
            $table->string('status'); // looking 寻找中，found 已找到
            $table->string('location');
            $table->string('cover_img')->nullable();
            $table->text('description');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_found');
    }
};
