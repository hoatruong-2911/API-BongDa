<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Vận hành', 'description' => 'Quản lý và điều hành sân bóng'],
            ['name' => 'Bán hàng', 'description' => 'Thu ngân và tư vấn khách hàng'],
            ['name' => 'An ninh', 'description' => 'Bảo vệ và an toàn sân bãi'],
            ['name' => 'F&B', 'description' => 'Dịch vụ đồ ăn và thức uống'],
            ['name' => 'Kỹ thuật', 'description' => 'Bảo trì sân và hệ thống điện'],
        ];

        foreach ($departments as $dept) {
            Department::create([
                'name' => $dept['name'],
                'slug' => Str::slug($dept['name']),
                'description' => $dept['description'],
                'is_active' => true
            ]);
        }
    }
}
