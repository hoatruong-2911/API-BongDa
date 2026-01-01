<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Mẹo cực mạnh: Tắt kiểm tra khóa ngoại trước khi chạy tổng
        Schema::disableForeignKeyConstraints();

        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            FieldSeeder::class,
            BookingSeeder::class,
            // OrderSeeder::class,
            DepartmentSeeder::class,
            StaffSeeder::class,
            ShiftSeeder::class,
            ShiftAssignmentSeeder::class,
            // UserSeeder::class,
        ]);

        // Bật lại sau khi xong
        Schema::enableForeignKeyConstraints();
    }
}
