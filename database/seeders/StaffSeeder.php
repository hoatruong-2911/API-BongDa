<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Xóa dữ liệu cũ để tránh trùng lặp khi chạy lại lệnh seed
        Staff::truncate();

        // 2. Lấy danh sách các phòng ban đã tạo từ DepartmentSeeder
        $deptVanhAnh = Department::where('name', 'Vận hành')->first();
        $deptBanHang = Department::where('name', 'Bán hàng')->first();
        $deptAnNinh  = Department::where('name', 'An ninh')->first();
        $deptFnB     = Department::where('name', 'F&B')->first();
        $deptKyThuat = Department::where('name', 'Kỹ thuật')->first();

        // 3. Danh sách nhân viên mẫu (Khớp với dữ liệu tĩnh của bạn)
        $staffData = [
            [
                'name'     => 'Nguyễn Văn Nam',
                'email'         => 'nam@stadium.com',
                'phone'         => '0901111111',
                'position'      => 'Quản lý sân',
                'department_id' => $deptVanhAnh->id ?? 1,
                'salary'        => 12000000,
                'bonus'         => 1000000,
                'join_date'     => '2023-01-10',
                'shift'         => 'Ca hành chính',
                'status'        => 'active',
            ],
            [
                'name'     => 'Trần Thị Hoa',
                'email'         => 'hoa@stadium.com',
                'phone'         => '0902222222',
                'position'      => 'Thu ngân',
                'department_id' => $deptBanHang->id ?? 2,
                'salary'        => 8000000,
                'bonus'         => 500000,
                'join_date'     => '2023-05-15',
                'shift'         => 'Ca sáng (08:00 - 16:00)',
                'status'        => 'active',
            ],
            [
                'name'     => 'Lê Văn Minh',
                'email'         => 'minh@stadium.com',
                'phone'         => '0903333333',
                'position'      => 'Bảo vệ',
                'department_id' => $deptAnNinh->id ?? 3,
                'salary'        => 7000000,
                'bonus'         => 200000,
                'join_date'     => '2023-02-20',
                'shift'         => 'Ca đêm (22:00 - 06:00)',
                'status'        => 'active',
            ],
            [
                'name'     => 'Phạm Thị Lan',
                'email'         => 'lan@stadium.com',
                'phone'         => '0904444444',
                'position'      => 'Phục vụ',
                'department_id' => $deptFnB->id ?? 4,
                'salary'        => 6500000,
                'bonus'         => 300000,
                'join_date'     => '2024-01-05',
                'shift'         => 'Ca chiều (14:00 - 22:00)',
                'status'        => 'off',
            ],
            [
                'name'     => 'Hoàng Văn Đức',
                'email'         => 'duc@stadium.com',
                'phone'         => '0905555555',
                'position'      => 'Kỹ thuật',
                'department_id' => $deptKyThuat->id ?? 5,
                'salary'        => 10000000,
                'bonus'         => 800000,
                'join_date'     => '2023-03-12',
                'shift'         => 'Ca xoay',
                'status'        => 'active',
            ],
        ];

        // 4. Lặp và chèn vào Database
        foreach ($staffData as $staff) {
            Staff::create([
                'name'     => $staff['name'],
                'email'         => $staff['email'],
                'phone'         => $staff['phone'],
                'position'      => $staff['position'],
                'department_id' => $staff['department_id'],
                'salary'        => $staff['salary'],
                'bonus'         => $staff['bonus'],
                'join_date'     => $staff['join_date'],
                'shift'         => $staff['shift'],
                'status'        => $staff['status'],
                'avatar'        => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . Str::random(5),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);
        }
    }
}