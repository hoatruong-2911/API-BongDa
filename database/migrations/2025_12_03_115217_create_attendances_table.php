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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->date('date'); // Ngày chấm công
            $table->time('check_in')->nullable(); // Giờ vào thực tế
            $table->time('check_out')->nullable(); // Giờ ra thực tế

            // Trạng thái: present (đúng giờ), late (đi muộn), absent (vắng), leave (nghỉ phép)
            $table->enum('status', ['present', 'late', 'absent', 'leave'])->default('present');

            $table->decimal('work_hours', 5, 2)->default(0); // Tổng giờ làm thực tế
            $table->decimal('overtime_hours', 5, 2)->default(0); // Giờ tăng ca
            $table->text('note')->nullable();
            $table->timestamps();

            // Đảm bảo 1 nhân viên chỉ có 1 bản ghi chấm công gốc mỗi ngày
            $table->unique(['staff_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
