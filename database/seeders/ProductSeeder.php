<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Áo đấu Manchester United',
                'category' => 'apparel',
                'price' => 250000.00,
                'stock' => 20,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Áo đấu CLB Manchester United chính hãng',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Áo đấu Real Madrid',
                'category' => 'apparel',
                'price' => 250000.00,
                'stock' => 18,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Áo đấu CLB Real Madrid chính hãng',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Quần đùi thể thao',
                'category' => 'apparel',
                'price' => 120000.00,
                'stock' => 40,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Quần đùi thể thao cao cấp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Tất đá bóng Nike',
                'category' => 'apparel',
                'price' => 45000.00,
                'stock' => 60,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Tất đá bóng Nike chống trượt',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // --- Đồ Uống (Drink) ---
            [
                'name' => 'Aquafina',
                'category' => 'drink',
                'price' => 8000.00,
                'stock' => 100,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Nước suối tinh khiết',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Coca Cola',
                'category' => 'drink',
                'price' => 15000.00,
                'stock' => 120,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Nước ngọt có ga Coca Cola',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Nước tăng lực Red Bull',
                'category' => 'drink',
                'price' => 18000.00,
                'stock' => 90,
                'unit' => 'lon',
                'available' => true,
                'description' => 'Nước tăng lực Red Bull',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pepsi',
                'category' => 'drink',
                'price' => 15000.00,
                'stock' => 100,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Nước ngọt có ga Pepsi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Sting dâu',
                'category' => 'drink',
                'price' => 12000.00,
                'stock' => 110,
                'unit' => 'chai',
                'available' => true,
                'description' => 'Nước tăng lực vị dâu',
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // --- Đồ Ăn (Food) ---
            [
                'name' => 'Bánh mì thịt nướng',
                'category' => 'food',
                'price' => 25000.00,
                'stock' => 50,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Bánh mì thịt nướng thơm ngon',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mì tôm trứng',
                'category' => 'food',
                'price' => 20000.00,
                'stock' => 30,
                'unit' => 'tô',
                'available' => true,
                'description' => 'Mì tôm trứng nóng hổi',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Snack khoai tây',
                'category' => 'food',
                'price' => 10000.00,
                'stock' => 80,
                'unit' => 'gói',
                'available' => true,
                'description' => 'Snack khoai tây giòn tan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Xúc xích nướng',
                'category' => 'food',
                'price' => 15000.00,
                'stock' => 100,
                'unit' => 'cái',
                'available' => true,
                'description' => 'Xúc xích nướng than hoa',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // --- Phụ kiện (Accessories) ---
            [
                'name' => 'Bảo vệ ống đồng',
                'category' => 'accessories',
                'price' => 65000.00,
                'stock' => 45,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Bảo vệ ống đồng chống chấn thương',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Bóng đá Nike',
                'category' => 'accessories',
                'price' => 350000.00,
                'stock' => 30,
                'unit' => 'quả',
                'available' => true,
                'description' => 'Bóng đá Nike Premier League',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Găng tay thủ môn',
                'category' => 'accessories',
                'price' => 180000.00,
                'stock' => 25,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Găng tay thủ môn chuyên nghiệp',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Giày đá bóng Adidas Predator',
                'category' => 'accessories',
                'price' => 850000.00,
                'stock' => 15,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Giày đá bóng Adidas Predator',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Giày đá bóng Nike Mercurial',
                'category' => 'accessories',
                'price' => 920000.00,
                'stock' => 12,
                'unit' => 'đôi',
                'available' => true,
                'description' => 'Giày đá bóng Nike Mercurial',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
