<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Inventory Categories
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Warehouses
        Schema::create('inventory_warehouses', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Inventory Products (SKUs)
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('category_uuid')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('uom')->default('pcs'); // pcs, box, kg, unit, set
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('reorder_level')->default(10);
            $table->integer('min_stock')->default(5);
            $table->integer('max_stock')->default(1000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_uuid')
                ->references('uuid')
                ->on('inventory_categories')
                ->onDelete('set null');
        });

        // 4. Warehouse Stock Balances
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('warehouse_uuid');
            $table->uuid('product_uuid');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->timestamps();

            $table->foreign('warehouse_uuid')
                ->references('uuid')
                ->on('inventory_warehouses')
                ->onDelete('cascade');

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('inventory_products')
                ->onDelete('cascade');

            $table->unique(['warehouse_uuid', 'product_uuid']);
        });

        // 5. Stock Movements (Ledger Audit Trail)
        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('product_uuid');
            $table->uuid('warehouse_uuid');
            $table->enum('type', ['inbound', 'outbound', 'transfer_in', 'transfer_out', 'adjustment', 'reconciliation']);
            $table->integer('quantity'); // positive or negative adjustment
            $table->integer('stock_after')->default(0);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by_uuid')->nullable();
            $table->timestamps();

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('inventory_products')
                ->onDelete('cascade');

            $table->foreign('warehouse_uuid')
                ->references('uuid')
                ->on('inventory_warehouses')
                ->onDelete('cascade');
        });

        // 6. Inter-Warehouse Transfer Orders
        Schema::create('inventory_transfer_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('transfer_number')->unique();
            $table->uuid('source_warehouse_uuid');
            $table->uuid('destination_warehouse_uuid');
            $table->enum('status', ['draft', 'in_transit', 'completed', 'cancelled'])->default('draft');
            $table->date('transfer_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by_uuid')->nullable();
            $table->timestamps();

            $table->foreign('source_warehouse_uuid')
                ->references('uuid')
                ->on('inventory_warehouses')
                ->onDelete('restrict');

            $table->foreign('destination_warehouse_uuid')
                ->references('uuid')
                ->on('inventory_warehouses')
                ->onDelete('restrict');
        });

        // 7. Transfer Order Items
        Schema::create('inventory_transfer_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('transfer_order_uuid');
            $table->uuid('product_uuid');
            $table->integer('quantity_requested');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->timestamps();

            $table->foreign('transfer_order_uuid')
                ->references('uuid')
                ->on('inventory_transfer_orders')
                ->onDelete('cascade');

            $table->foreign('product_uuid')
                ->references('uuid')
                ->on('inventory_products')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventory_transfer_order_items');
        Schema::dropIfExists('inventory_transfer_orders');
        Schema::dropIfExists('inventory_stock_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('inventory_products');
        Schema::dropIfExists('inventory_warehouses');
        Schema::dropIfExists('inventory_categories');
    }
};
