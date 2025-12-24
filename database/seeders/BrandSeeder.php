<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        // Sử dụng delete thay vì truncate để tránh lỗi Foreign Key nếu bro không tắt check khóa ngoại
        DB::table('brands')->delete();

        $brands = [
            ['name' => 'Nike', 'sort_order' => 1, 'website' => 'https://www.nike.com'],
            ['name' => 'Adidas', 'sort_order' => 2, 'website' => 'https://www.adidas.com.vn'],
            ['name' => 'Puma', 'sort_order' => 3, 'website' => 'https://about.puma.com'],
            ['name' => 'Mizuno', 'sort_order' => 4, 'website' => 'https://mizuno.com.vn'],
            ['name' => 'Kamito', 'sort_order' => 5, 'website' => 'https://kamito.vn'],
            ['name' => 'Coca-Cola', 'sort_order' => 6, 'website' => 'https://www.coca-cola.com.vn'],
            ['name' => 'Pepsi', 'sort_order' => 7, 'website' => 'https://www.pepsico.com'],
            ['name' => 'Red Bull', 'sort_order' => 8, 'website' => 'https://www.redbull.com'],
            ['name' => 'Aquafina', 'sort_order' => 9, 'website' => 'https://www.aquafina.com'],
            ['name' => 'Revive', 'sort_order' => 10, 'website' => 'https://www.suntorypepsico.vn/products/revive'],
            ['name' => 'Grand Sport', 'sort_order' => 11, 'website' => 'https://grandsportvietnam.com'],
            ['name' => 'Mitre', 'sort_order' => 12, 'website' => 'https://www.mitre.com'],
            ['name' => 'Molten', 'sort_order' => 13, 'website' => 'https://www.molten.co.jp/sports/en'],
            ['name' => 'Pan', 'sort_order' => 14, 'website' => 'https://pan-sportswear.com'],
            ['name' => 'Zocker', 'sort_order' => 15, 'website' => 'https://zocker.vn'],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert([
                'name'        => $brand['name'],
                'slug'        => Str::slug($brand['name']),
                'website'     => $brand['website'], // Thêm cột website vào đây
                'logo'        => null,              // Logo sẽ được admin upload sau
                'description' => 'Thương hiệu ' . $brand['name'] . ' uy tín cung cấp trang thiết bị và đồ uống thể thao chuyên nghiệp.',
                'sort_order'  => $brand['sort_order'],
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
