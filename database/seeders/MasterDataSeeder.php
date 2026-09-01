<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Domain\HRM\Models\Shift;
use App\Domain\HRM\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed RBAC Roles & Permissions
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Ensure Passport Personal Access Client exists
        if (\Illuminate\Support\Facades\Schema::hasTable('oauth_clients')) {
            $hasPersonalClient = \Illuminate\Support\Facades\DB::table('oauth_clients')
                ->where('grant_types', 'like', '%personal_access%')
                ->exists();

            if (!$hasPersonalClient) {
                \Illuminate\Support\Facades\Artisan::call('passport:client', [
                    '--personal' => true,
                    '--name' => 'MiniERP Personal Access Client',
                    '--provider' => 'users',
                    '--no-interaction' => true,
                ]);
            }
        }

        // 1. Create Default Super Admin Account Only
        $admin = User::updateOrCreate(
            ['email' => 'admin@erp.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super-admin');

        // 2. Seed Shifts: Morning, Day, Night
        $shifts = [
            [
                'name'        => 'Morning Shift',
                'start_time'  => '06:00:00',
                'end_time'    => '14:00:00',
                'description' => 'Morning shift duty (06:00 - 14:00)',
            ],
            [
                'name'        => 'Day Shift',
                'start_time'  => '08:00:00',
                'end_time'    => '17:00:00',
                'description' => 'Regular day shift duty (08:00 - 17:00)',
            ],
            [
                'name'        => 'Night Shift',
                'start_time'  => '22:00:00',
                'end_time'    => '06:00:00',
                'description' => 'Night shift duty (22:00 - 06:00)',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::firstOrCreate(
                ['name' => $shift['name']],
                $shift
            );
        }

        // 3. Seed Payroll Periods with salary cut-off/payment dates (Tanggal 25, Tanggal 28, Tanggal 1)
        // e.g. Periode Gajian 25 (26 prev month - 25 current month)
        // e.g. Periode Gajian 28 (29 prev month - 28 current month)
        // e.g. Periode Gajian 1 (2 prev month - 1 current month / 1st - end of month)
        $currentMonth = Carbon::now()->format('F Y');
        $now = Carbon::now();

        $periods = [
            [
                'name'       => "Payroll Period Tgl 25 ({$currentMonth})",
                'start_date' => $now->copy()->subMonth()->setDay(26)->toDateString(),
                'end_date'   => $now->copy()->setDay(25)->toDateString(),
                'status'     => 'draft',
            ],
            [
                'name'       => "Payroll Period Tgl 28 ({$currentMonth})",
                'start_date' => $now->copy()->subMonth()->setDay(29)->toDateString(),
                'end_date'   => $now->copy()->setDay(28)->toDateString(),
                'status'     => 'draft',
            ],
            [
                'name'       => "Payroll Period Tgl 1 ({$currentMonth})",
                'start_date' => $now->copy()->startOfMonth()->toDateString(),
                'end_date'   => $now->copy()->endOfMonth()->toDateString(),
                'status'     => 'draft',
            ],
        ];

        foreach ($periods as $period) {
            PayrollPeriod::firstOrCreate(
                ['name' => $period['name']],
                $period
            );
        }
    }
}

