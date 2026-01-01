<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ShiftAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $staffs = Staff::all();
        $shifts = Shift::all();

        if ($staffs->isEmpty() || $shifts->isEmpty()) {
            return;
        }

        // Lấy ngày bắt đầu tuần hiện tại (Thứ 2)
        $startOfWeek = Carbon::now()->startOfWeek();

        foreach ($staffs as $staff) {
            // Duyệt qua 7 ngày trong tuần
            for ($i = 0; $i < 7; $i++) {
                $currentDate = $startOfWeek->copy()->addDays($i);

                // Tỉ lệ 80% có ca làm, 20% là ngày nghỉ (để giống thực tế)
                if (rand(1, 10) <= 8) {
                    ShiftAssignment::create([
                        'staff_id'   => $staff->id,
                        'shift_id'   => $shifts->random()->id,
                        'work_date'  => $currentDate->format('Y-m-d'),
                        'note'       => 'Lịch làm việc dự kiến',
                    ]);
                }
            }
        }
    }
}
