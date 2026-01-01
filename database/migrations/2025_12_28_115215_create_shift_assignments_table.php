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
        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->date('work_date');    // Ngày cụ thể: 2024-01-15
            $table->text('note')->nullable();
            $table->timestamps();

            // Chặn trùng lặp: Một người không thể làm 2 ca giống hệt nhau trong 1 ngày
            $table->unique(['staff_id', 'shift_id', 'work_date'], 'staff_shift_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_assignments');
    }
};
