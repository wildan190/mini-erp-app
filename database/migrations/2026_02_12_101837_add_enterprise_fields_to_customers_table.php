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
            $table->string('company_name')->nullable()->after('name');
            $table->string('customer_type')->default('corporate')->after('company_name');
            $table->string('tax_id')->nullable()->after('customer_type');
            $table->string('industry')->nullable()->after('tax_id');
            $table->string('website')->nullable()->after('industry');

            $table->string('phone')->nullable()->after('email');
            $table->string('alt_phone')->nullable()->after('phone');
            $table->string('department')->nullable()->after('alt_phone');

            $table->text('billing_address')->nullable()->after('department');
            $table->text('shipping_address')->nullable()->after('billing_address');
            $table->string('city')->nullable()->after('shipping_address');
            $table->string('province')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('province');
            $table->string('country')->default('Indonesia')->after('postal_code');

            $table->decimal('credit_limit', 15, 2)->default(0)->after('country');
            $table->string('payment_terms')->nullable()->after('credit_limit');
            $table->string('currency')->default('IDR')->after('payment_terms');

            $table->string('segment')->nullable()->after('currency');
            $table->string('status')->default('active')->after('segment');
            $table->text('notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'customer_type',
                'tax_id',
                'industry',
                'website',
                'phone',
                'alt_phone',
                'department',
                'billing_address',
                'shipping_address',
                'city',
                'province',
                'postal_code',
                'country',
                'credit_limit',
                'payment_terms',
                'currency',
                'segment',
                'status',
                'notes'
            ]);
        });
    }
};
