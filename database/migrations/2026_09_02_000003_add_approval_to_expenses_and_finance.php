<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add approval columns to financial_records table
        Schema::table('financial_records', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('status');
            $table->string('approved_by_name')->nullable()->after('approved_by_user_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by_name');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });

        // 2. Add approval columns to project_costs table
        Schema::table('project_costs', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('status');
            $table->string('approved_by_name')->nullable()->after('approved_by_user_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by_name');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('financial_records', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by_user_id', 'approved_by_name', 'approved_at', 'rejection_reason']);
        });

        Schema::table('project_costs', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by_user_id', 'approved_by_name', 'approved_at', 'rejection_reason']);
        });
    }
};
