<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            ['name' => 'Ca sáng', 'start_time' => '06:00', 'end_time' => '14:00'],
            ['name' => 'Ca chiều', 'start_time' => '14:00', 'end_time' => '22:00'],
            ['name' => 'Ca tối', 'start_time' => '22:00', 'end_time' => '06:00'],
            ['name' => 'Ca full', 'start_time' => '08:00', 'end_time' => '17:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
