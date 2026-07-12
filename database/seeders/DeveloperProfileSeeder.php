<?php

namespace Database\Seeders;

use App\Models\DeveloperProfile;
use Illuminate\Database\Seeder;

class DeveloperProfileSeeder extends Seeder
{
    public function run(): void
    {
        DeveloperProfile::firstOrCreate(
            ['name' => 'Ma. Angelica F. Manahan'],
            [
                'section' => 'BSIT 2-1',
                'module_name' => 'Human Resources',
                'professor' => 'Mr. Marc Elvin Cerezo',
                'github_url' => null, // add your repo URL later via the Edit page
                'summary' => 'This ERP System is a Human Resources module developed for ITEC 75A '
                    . '(System Integration and Architecture I) at Cavite State University, Don '
                    . 'Severino de las Alas Campus. It covers Employee Records, Payroll and '
                    . 'Compensation, Recruitment and Onboarding, and Time, Attendance, and Leave '
                    . 'Management - built to demonstrate a working, integrated HR system as part '
                    . 'of a larger group ERP project.',
            ]
        );
    }
}
