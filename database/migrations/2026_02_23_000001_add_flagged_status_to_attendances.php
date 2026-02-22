<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'flagged' to the status check constraint for PostgreSQL
        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'late', 'absent', 'half_day', 'leave', 'flagged'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'flagged' (migrate to 'absent' if needed)
        DB::table('attendances')->where('status', 'flagged')->update(['status' => 'absent']);

        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_status_check CHECK (status IN ('present', 'late', 'absent', 'half_day', 'leave'))");
    }
};
