<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Merge ap_vendors into suppliers.
 *
 * Steps:
 * 1. Add bank/payment columns to suppliers
 * 2. Migrate ap_vendors rows into suppliers (match by name+npwp, or insert new)
 * 3. Drop FK ap_bills.vendor_id → ap_vendors, add FK → suppliers
 * 4. Drop ap_vendors table
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── STEP 1: Add bank/AP columns to suppliers ───────────────────────────
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('contact');
            $table->string('bank_code')->nullable()->after('currency_code');
            $table->string('bank_account_number')->nullable()->after('bank_code');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->string('midtrans_beneficiary_alias')->nullable()->after('bank_account_name');
            $table->text('notes')->nullable()->after('midtrans_beneficiary_alias');
            $table->softDeletes();
        });

        // ── STEP 2: Migrate existing ap_vendors data into suppliers ───────────
        $apVendors = DB::table('ap_vendors')->whereNull('deleted_at')->get();

        foreach ($apVendors as $vendor) {
            // Try to match by name + npwp to avoid duplicates
            $existing = DB::table('suppliers')
                ->where('name', $vendor->name)
                ->when($vendor->npwp, fn($q) => $q->where('npwp', $vendor->npwp))
                ->first();

            if ($existing) {
                // Update existing supplier with bank info
                DB::table('suppliers')->where('id', $existing->id)->update([
                    'email'                      => $existing->email ?? $vendor->email,
                    'bank_code'                  => $vendor->bank_code,
                    'bank_account_number'        => $vendor->bank_account_number,
                    'bank_account_name'          => $vendor->bank_account_name,
                    'midtrans_beneficiary_alias' => $vendor->midtrans_beneficiary_alias,
                    'notes'                      => $vendor->notes,
                    'updated_at'                 => now(),
                ]);

                // Map old ap_vendor id → supplier id for bill migration
                DB::table('ap_vendors')->where('id', $vendor->id)->update([
                    'notes' => '__supplier_id:' . $existing->id, // temp marker
                ]);
            } else {
                // Insert as new supplier
                $newId = DB::table('suppliers')->insertGetId([
                    'uuid'                       => $vendor->uuid,
                    'name'                       => $vendor->name,
                    'email'                      => $vendor->email,
                    'contact'                    => $vendor->phone ?? null,
                    'npwp'                       => $vendor->npwp,
                    'bank_code'                  => $vendor->bank_code,
                    'bank_account_number'        => $vendor->bank_account_number,
                    'bank_account_name'          => $vendor->bank_account_name,
                    'midtrans_beneficiary_alias' => $vendor->midtrans_beneficiary_alias,
                    'notes'                      => $vendor->notes,
                    'is_active'                  => $vendor->is_active,
                    'created_at'                 => $vendor->created_at,
                    'updated_at'                 => $vendor->updated_at,
                ]);

                DB::table('ap_vendors')->where('id', $vendor->id)->update([
                    'notes' => '__supplier_id:' . $newId,
                ]);
            }
        }

        // ── STEP 3: Update ap_bills.vendor_id to point to suppliers.id ────────
        // Read the mapping we stored in ap_vendors.notes
        $vendorMap = DB::table('ap_vendors')
            ->whereNotNull('notes')
            ->where('notes', 'like', '__supplier_id:%')
            ->pluck('notes', 'id');

        foreach ($vendorMap as $apVendorId => $noteValue) {
            $supplierId = (int) str_replace('__supplier_id:', '', $noteValue);
            DB::table('ap_bills')
                ->where('vendor_id', $apVendorId)
                ->update(['vendor_id' => $supplierId]);
        }

        // ── STEP 4: Drop FK ap_bills.vendor_id → ap_vendors, point to suppliers
        Schema::table('ap_bills', function (Blueprint $table) {
            // Drop old FK constraint (name may vary — try both conventions)
            try {
                $table->dropForeign(['vendor_id']);
            } catch (\Exception $e) {
                // FK may already be named differently, ignore
            }

            // Add new FK pointing to suppliers
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('restrict');
        });

        // ── STEP 5: Drop ap_vendors table ─────────────────────────────────────
        Schema::dropIfExists('ap_vendors');
    }

    public function down(): void
    {
        // Recreate ap_vendors and revert FK — data restoration not included
        Schema::create('ap_vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bank_code');
            $table->string('bank_account_number');
            $table->string('bank_account_name');
            $table->boolean('is_active')->default(true);
            $table->string('midtrans_beneficiary_alias')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('ap_bills', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->foreign('vendor_id')
                  ->references('id')
                  ->on('ap_vendors')
                  ->onDelete('restrict');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'bank_code',
                'bank_account_number',
                'bank_account_name',
                'midtrans_beneficiary_alias',
                'notes',
                'deleted_at',
            ]);
        });
    }
};
