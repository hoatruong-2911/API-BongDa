<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Staff;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh sách nhân viên đang hoạt động
        $staffs = Staff::where('status', 'active')->get();
        $today = Carbon::today();

        foreach ($staffs as $index => $staff) {
            // Tạo các kịch bản chấm công khác nhau cho phong phú
            if ($index == 0) {
                // Nhân viên 1: Đi làm đúng giờ
                Attendance::create([
                    'staff_id' => $staff->id,
                    'date' => $today->format('Y-m-d'),
                    'check_in' => '07:55',
                    'check_out' => '17:05',
                    'status' => 'present',
                    'work_hours' => 9.17,
                    'overtime_hours' => 1.17,
                    'note' => 'Hoàn thành tốt công việc'
                ]);
            } elseif ($index == 1) {
                // Nhân viên 2: Đi muộn
                Attendance::create([
                    'staff_id' => $staff->id,
                    'date' => $today->format('Y-m-d'),
                    'check_in' => '08:15',
                    'check_out' => '17:00',
                    'status' => 'late',
                    'work_hours' => 8.75,
                    'overtime_hours' => 0.75,
                    'note' => 'Kẹt xe cầu Sài Gòn'
                ]);
            } elseif ($index == 2) {
                // Nhân viên 3: Vắng mặt
                Attendance::create([
                    'staff_id' => $staff->id,
                    'date' => $today->format('Y-m-d'),
                    'check_in' => null,
                    'check_out' => null,
                    'status' => 'absent',
                    'work_hours' => 0,
                    'overtime_hours' => 0,
                    'note' => 'Nghỉ không phép'
                ]);
            } else {
                // Các nhân viên còn lại mặc định có mặt bình thường
                Attendance::create([
                    'staff_id' => $staff->id,
                    'date' => $today->format('Y-m-d'),
                    'check_in' => '08:00',
                    'check_out' => '17:00',
                    'status' => 'present',
                    'work_hours' => 9,
                    'overtime_hours' => 1,
                    'note' => null
                ]);
            }
        }
    }
    
}
