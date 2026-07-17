<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates the very first login so you're not locked out once every
     * route requires authentication. Not tied to any employee record -
     * this is a system-level account.
     *
     * IMPORTANT: change or remove this account before anyone outside your
     * own testing sees this system - it's a known, publicly-documented
     * default credential.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@erp-hr.test'],
            [
                'name' => 'Admin User',
                'password' => 'angelmanahan',
                'role' => 'admin',
                'employee_id' => null,
            ]
        );
    }
}
