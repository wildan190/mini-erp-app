<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'employees',
            'departments',
            'designations',
            'shifts',
            'attendances',
            'leave_requests',
            'leave_types',
            'payrolls',
            'payroll_periods',
            'salary_components',
            'reimbursements',
            'resignations',
            'office_locations',
            'employee_documents'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });

            // Generate UUIDs for existing records
            DB::table($table)->whereNull('uuid')->get()->each(function ($record) use ($table) {
                DB::table($table)->where('id', $record->id)->update(['uuid' => Str::uuid()]);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'users',
            'employees',
            'departments',
            'designations',
            'shifts',
            'attendances',
            'leave_requests',
            'leave_types',
            'payrolls',
            'payroll_periods',
            'salary_components',
            'reimbursements',
            'resignations',
            'office_locations',
            'employee_documents'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
