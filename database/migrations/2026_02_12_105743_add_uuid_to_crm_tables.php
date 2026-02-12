<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
        Schema::table('prospects', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
