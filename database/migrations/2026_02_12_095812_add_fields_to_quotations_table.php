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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('quotation_number')->unique()->after('id');
            $table->string('status')->default('draft')->after('quotation_number');
            $table->decimal('subtotal', 15, 2)->default(0)->after('status');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('discount_amount');
            $table->decimal('total_amount', 15, 2)->default(0)->after('tax_amount');
            $table->text('terms')->nullable()->after('valid_until');
            $table->dropColumn('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->after('customer_id');
            $table->dropColumn(['quotation_number', 'status', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount', 'terms']);
        });
    }
};
