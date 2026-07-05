<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Vacation Leave', 'default_days_per_year' => 15],
            ['name' => 'Sick Leave', 'default_days_per_year' => 10],
            ['name' => 'Emergency Leave', 'default_days_per_year' => 5],
            ['name' => 'Maternity Leave', 'default_days_per_year' => 60],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
