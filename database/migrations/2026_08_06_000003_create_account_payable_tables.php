<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. AP VENDORS ─────────────────────────────────────────────────────
        Schema::create('ap_vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bank_code');             // e.g. bca, bni, bri, mandiri
            $table->string('bank_account_number');
            $table->string('bank_account_name');
            $table->boolean('is_active')->default(true);
            $table->string('midtrans_beneficiary_alias')->nullable(); // registered alias in Iris
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 2. AP BILLS ───────────────────────────────────────────────────────
        Schema::create('ap_bills', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vendor_id')->constrained('ap_vendors')->onDelete('restrict');
            $table->string('bill_number')->unique();
            $table->string('reference')->nullable(); // PO / Invoice number from supplier
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'approved', 'partial', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── 3. AP BILL ITEMS ──────────────────────────────────────────────────
        Schema::create('ap_bill_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ap_bill_id')->constrained('ap_bills')->onDelete('cascade');
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('account_uuid')->nullable(); // link to Chart of Accounts
            $table->timestamps();
        });

        // ── 4. AP PAYMENTS ────────────────────────────────────────────────────
        Schema::create('ap_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ap_bill_id')->constrained('ap_bills')->onDelete('restrict');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['bank_transfer', 'midtrans_iris', 'cash', 'other'])->default('midtrans_iris');
            $table->string('midtrans_reference_no')->nullable();  // Iris transaction reference
            $table->string('midtrans_beneficiary_alias')->nullable();
            $table->enum('midtrans_status', ['pending', 'queued', 'processed', 'failed'])->nullable();
            $table->json('midtrans_response')->nullable(); // full raw response from Iris API
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_payments');
        Schema::dropIfExists('ap_bill_items');
        Schema::dropIfExists('ap_bills');
        Schema::dropIfExists('ap_vendors');
    }
};
