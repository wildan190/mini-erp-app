<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payroll_periods DROP CONSTRAINT IF EXISTS payroll_periods_status_check');
            DB::statement("ALTER TABLE payroll_periods ADD CONSTRAINT payroll_periods_status_check CHECK (status::text = ANY (ARRAY['draft'::character varying, 'processing'::character varying, 'closed'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payroll_periods DROP CONSTRAINT IF EXISTS payroll_periods_status_check');
            DB::statement("ALTER TABLE payroll_periods ADD CONSTRAINT payroll_periods_status_check CHECK (status::text = ANY (ARRAY['draft'::character varying, 'closed'::character varying]::text[]))");
        }
    }
};
