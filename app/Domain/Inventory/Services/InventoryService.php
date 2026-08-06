<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\InventoryProduct;
use App\Domain\Inventory\Models\InventoryStock;
use App\Domain\Inventory\Models\InventoryStockMovement;
use App\Domain\Inventory\Models\InventoryTransferOrder;
use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Adjust or record stock movement with double-entry ledger guarantee.
     */
    public function recordStockMovement(
        string $productUuid,
        string $warehouseUuid,
        string $type, // inbound, outbound, transfer_in, transfer_out, adjustment, reconciliation
        int $quantity,
        ?string $referenceNumber = null,
        ?string $notes = null,
        ?string $createdByUuid = null
    ): InventoryStockMovement {
        return DB::transaction(function () use ($productUuid, $warehouseUuid, $type, $quantity, $referenceNumber, $notes, $createdByUuid) {
            $stock = InventoryStock::firstOrCreate(
                [
                    'warehouse_uuid' => $warehouseUuid,
                    'product_uuid'   => $productUuid,
                ],
                [
                    'quantity_on_hand'   => 0,
                    'quantity_reserved'  => 0,
                    'quantity_available' => 0,
                ]
            );

            // Determine sign of change
            $delta = $quantity;
            if (in_array($type, ['outbound', 'transfer_out']) && $delta > 0) {
                $delta = -$delta;
            }

            $newOnHand = max(0, $stock->quantity_on_hand + $delta);
            $stock->quantity_on_hand   = $newOnHand;
            $stock->quantity_available = max(0, $newOnHand - $stock->quantity_reserved);
            $stock->save();

            // Log movement entry
            return InventoryStockMovement::create([
                'product_uuid'     => $productUuid,
                'warehouse_uuid'   => $warehouseUuid,
                'type'             => $type,
                'quantity'         => $delta,
                'stock_after'      => $newOnHand,
                'reference_number' => $referenceNumber,
                'notes'            => $notes,
                'created_by_uuid'  => $createdByUuid,
            ]);
        });
    }

    /**
     * Complete or update Inter-Warehouse Transfer Order status.
     */
    public function updateTransferOrderStatus(string $transferUuid, string $newStatus): InventoryTransferOrder
    {
        return DB::transaction(function () use ($transferUuid, $newStatus) {
            $transfer = InventoryTransferOrder::with('items')->where('uuid', $transferUuid)->firstOrFail();

            if ($transfer->status === $newStatus) {
                return $transfer;
            }

            // Execute stock shifts if transitioning to in_transit or completed
            if ($newStatus === 'in_transit' && $transfer->status === 'draft') {
                foreach ($transfer->items as $item) {
                    $this->recordStockMovement(
                        $item->product_uuid,
                        $transfer->source_warehouse_uuid,
                        'transfer_out',
                        $item->quantity_requested,
                        $transfer->transfer_number,
                        'Transfer out to warehouse ' . $transfer->destination_warehouse_uuid
                    );
                    $item->update(['quantity_shipped' => $item->quantity_requested]);
                }
            } elseif ($newStatus === 'completed' && in_array($transfer->status, ['draft', 'in_transit'])) {
                if ($transfer->status === 'draft') {
                    foreach ($transfer->items as $item) {
                        $this->recordStockMovement(
                            $item->product_uuid,
                            $transfer->source_warehouse_uuid,
                            'transfer_out',
                            $item->quantity_requested,
                            $transfer->transfer_number,
                            'Direct transfer out'
                        );
                        $item->update(['quantity_shipped' => $item->quantity_requested]);
                    }
                }

                foreach ($transfer->items as $item) {
                    $this->recordStockMovement(
                        $item->product_uuid,
                        $transfer->destination_warehouse_uuid,
                        'transfer_in',
                        $item->quantity_shipped ?: $item->quantity_requested,
                        $transfer->transfer_number,
                        'Transfer received from warehouse ' . $transfer->source_warehouse_uuid
                    );
                    $item->update(['quantity_received' => $item->quantity_shipped ?: $item->quantity_requested]);
                }
            }

            $transfer->update(['status' => $newStatus]);
            return $transfer->refresh();
        });
    }
}
