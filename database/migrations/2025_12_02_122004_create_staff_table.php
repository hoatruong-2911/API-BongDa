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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            // Tham chiếu tới bảng phòng ban
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');

            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('position'); // Vị trí (VD: Quản lý, Thu ngân)
            $table->string('avatar')->nullable();
            $table->decimal('salary', 15, 2); // Lương cơ bản
            $table->decimal('bonus', 15, 2)->default(0); // Thưởng
            $table->date('join_date'); // Ngày vào làm
            $table->enum('status', ['active', 'off', 'inactive'])->default('active');
            $table->string('shift')->nullable(); // Ca làm việc mặc định
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
