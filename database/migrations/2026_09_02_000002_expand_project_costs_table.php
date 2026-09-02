<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_costs', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('type'); // beli apa (nama barang/jasa)
            $table->integer('quantity')->default(1)->after('item_name'); // jumlah item
            $table->decimal('unit_price', 20, 2)->nullable()->after('quantity'); // harga satuan
            $table->text('purpose')->nullable()->after('description'); // keperluan/tujuan pengeluaran
            $table->uuid('requested_by_employee_uuid')->nullable()->after('purpose'); // siapa yang mengajukan (employee)
            $table->string('requested_by_name')->nullable()->after('requested_by_employee_uuid'); // nama pemohon
            $table->string('receipt_attachment_path')->nullable()->after('requested_by_name'); // bukti struk/nota
            $table->uuid('finance_record_uuid')->nullable()->after('reference_uuid'); // sync to finance module
        });
    }

    public function down(): void
    {
        Schema::table('project_costs', function (Blueprint $table) {
            $table->dropColumn([
                'item_name',
                'quantity',
                'unit_price',
                'purpose',
                'requested_by_employee_uuid',
                'requested_by_name',
                'receipt_attachment_path',
                'finance_record_uuid',
            ]);
        });
    }
};
