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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('subject')->nullable(); // Chủ đề liên hệ
            $table->text('message');
            // Trạng thái: 0: Chưa đọc, 1: Đã đọc, 2: Đã phản hồi
            $table->tinyInteger('status')->default(0);
            $table->text('admin_note')->nullable(); // Ghi chú của Admin khi xử lý
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
