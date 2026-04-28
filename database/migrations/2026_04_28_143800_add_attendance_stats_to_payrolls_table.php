<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('expected_work_days')->default(0)->after('basic_salary');
            $table->integer('actual_presence')->default(0)->after('expected_work_days');
            $table->integer('absence_days')->default(0)->after('actual_presence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['expected_work_days', 'actual_presence', 'absence_days']);
        });
    }
};
