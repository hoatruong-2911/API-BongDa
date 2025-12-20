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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết với bảng users (QUAN TRỌNG: onDelete('cascade'))
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Thông tin cơ bản cho Khách hàng
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();

            // Thông tin bổ sung (Dùng cho Staff/Admin)
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->date('join_date')->nullable();
            $table->decimal('salary', 12, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'off'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
