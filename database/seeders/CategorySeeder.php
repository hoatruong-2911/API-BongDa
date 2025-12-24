<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Thêm dòng này

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Truncate để tránh lỗi duplicate khi chạy lại seeder
        DB::table('categories')->delete();

        $categories = [
            ['name' => 'Giày Đá Bóng', 'sort_order' => 1],
            ['name' => 'Áo Đấu CLB', 'sort_order' => 2],
            ['name' => 'Nước Giải Khát', 'sort_order' => 3],
            ['name' => 'Nước Tăng Lực', 'sort_order' => 4],
            ['name' => 'Thức Ăn Nhanh', 'sort_order' => 5],
            ['name' => 'Găng Tay Thủ Môn', 'sort_order' => 6],
            ['name' => 'Tất & Phụ Kiện', 'sort_order' => 7],
            ['name' => 'Quả Bóng Đá', 'sort_order' => 8],
            ['name' => 'Băng Cuốn Cơ', 'sort_order' => 9],
            ['name' => 'Túi Đựng Giày', 'sort_order' => 10],
            ['name' => 'Quần Áo Tập', 'sort_order' => 11],
            ['name' => 'Bình Nước Thể Thao', 'sort_order' => 12],
            ['name' => 'Áo Khoác Gió', 'sort_order' => 13],
            ['name' => 'Dụng Cụ Tập Luyện', 'sort_order' => 14],
            ['name' => 'Đồ Lưu Niệm', 'sort_order' => 15],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']), // Tạo slug: giay-da-bong...
                'sort_order' => $cat['sort_order'],
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
