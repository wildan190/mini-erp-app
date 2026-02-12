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
        Schema::table('sales_pipelines', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->after('id');
            $table->foreignId('user_id')->nullable()->after('prospect_id')->constrained()->nullOnDelete();
            $table->text('notes')->nullable()->after('stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_pipelines', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['uuid', 'user_id', 'notes']);
        });
    }
};
