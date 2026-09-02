<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. AR Invoices Table
        Schema::create('ar_invoices', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('invoice_number', 50)->unique();
            $table->uuid('customer_uuid')->nullable()->index();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('reference', 100)->nullable();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('payment_terms', 50)->default('net_30'); // due_on_receipt, net_15, net_30, net_60, custom
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // in percent e.g. 11%
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('draft'); // draft, sent, partial, paid, overdue, cancelled
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->unsignedBigInteger('issued_by_user_id')->nullable();
            $table->string('issued_by_name')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->uuid('finance_record_uuid')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. AR Invoice Items Table
        Schema::create('ar_invoice_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('ar_invoice_uuid')->index();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 30)->default('pcs');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('ar_invoice_uuid')->references('uuid')->on('ar_invoices')->onDelete('cascade');
        });

        // 3. AR Payments Table (Customer receipts)
        Schema::create('ar_payments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('ar_invoice_uuid')->index();
            $table->string('payment_number', 50)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, cheque, midtrans_va, qris, other
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_account', 100)->nullable();
            $table->string('receipt_attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('status', 30)->default('verified'); // verified, pending, rejected
            $table->uuid('finance_record_uuid')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ar_invoice_uuid')->references('uuid')->on('ar_invoices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_payments');
        Schema::dropIfExists('ar_invoice_items');
        Schema::dropIfExists('ar_invoices');
    }
};
