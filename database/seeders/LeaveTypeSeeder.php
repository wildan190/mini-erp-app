<?php

namespace Database\Seeders;

use App\Domain\HRM\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Annual Leave',
                'days_allowed' => 12,
                'description' => 'Standard paid annual leave allowance.',
            ],
            [
                'name' => 'Sick Leave',
                'days_allowed' => 14,
                'description' => 'Paid leave for illness or medical reasons.',
            ],
            [
                'name' => 'Maternity / Paternity Leave',
                'days_allowed' => 90,
                'description' => 'Parental leave for childbirth and child care.',
            ],
            [
                'name' => 'Unpaid Leave',
                'days_allowed' => 30,
                'description' => 'Leave taken without pay allowance.',
            ],
            [
                'name' => 'Special Leave',
                'days_allowed' => 3,
                'description' => 'Leave for special family events or bereavement.',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
