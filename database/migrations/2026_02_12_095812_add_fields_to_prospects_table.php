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
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->decimal('expected_value', 15, 2)->nullable()->after('status');
            $table->date('expected_closing_date')->nullable()->after('expected_value');
            $table->integer('probability')->nullable()->after('expected_closing_date');
            $table->text('notes')->nullable()->after('probability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn(['title', 'expected_value', 'expected_closing_date', 'probability', 'notes']);
        });
    }
};
