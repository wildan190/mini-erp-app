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
        // For PostgreSQL, we need to handle the enum/check constraint update
        // Laravel's change() doesn't always handle complex enum changes well on pgsql 
        // especially with existing check constraints.

        // Face Verification Status
        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_face_verification_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN face_verification_status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_face_verification_status_check CHECK (face_verification_status IN ('verified', 'failed', 'skipped', 'pending', 'error'))");

        // Location Verification Status
        // First migrate existing 'out_of_radius' to 'outside_radius'
        DB::table('attendances')->where('location_verification_status', 'out_of_radius')->update(['location_verification_status' => 'outside_radius']);

        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_location_verification_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN location_verification_status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_location_verification_status_check CHECK (location_verification_status IN ('within_radius', 'outside_radius', 'skipped', 'pending', 'error'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Face Verification Status
        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_face_verification_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN face_verification_status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_face_verification_status_check CHECK (face_verification_status IN ('verified', 'failed', 'skipped'))");

        // Location Verification Status
        // Migrate back 'outside_radius' to 'out_of_radius'
        DB::table('attendances')->where('location_verification_status', 'outside_radius')->update(['location_verification_status' => 'out_of_radius']);

        DB::statement("ALTER TABLE attendances DROP CONSTRAINT IF EXISTS attendances_location_verification_status_check");
        DB::statement("ALTER TABLE attendances ALTER COLUMN location_verification_status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE attendances ADD CONSTRAINT attendances_location_verification_status_check CHECK (location_verification_status IN ('within_radius', 'out_of_radius', 'skipped'))");
    }
};
