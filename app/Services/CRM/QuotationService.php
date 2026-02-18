<?php

namespace App\Services\CRM;

use App\Models\CRM\Quotation;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuotationService
{
    public function index()
    {
        return Quotation::with('customer', 'items')->latest()->paginate(10);
    }

    public function show($id): Quotation
    {
        if (is_numeric($id)) {
            return Quotation::with(['customer', 'items'])->findOrFail($id);
        }
        if (Str::isUuid($id)) {
            return Quotation::with(['customer', 'items'])->where('uuid', $id)->firstOrFail();
        }
        abort(404);
    }

    public function create(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $subtotal = 0;
            $taxAmount = 0;

            if (empty($data['quotation_number'])) {
                $data['quotation_number'] = 'QUO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            }

            if (isset($data['customer_id'])) {
                if (Str::isUuid($data['customer_id'])) {
                    $data['customer_id'] = Customer::where('uuid', $data['customer_id'])->value('id');
                }
            }

            $quotation = Quotation::create(array_merge($data, [
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'status' => $data['status'] ?? 'draft'
            ]));

            foreach ($items as $itemData) {
                $qty = $itemData['quantity'] ?? 1;
                $price = $itemData['unit_price'] ?? 0;
                $itemSubtotal = $qty * $price;

                $taxRate = $itemData['tax_rate'] ?? 0;
                $itemTax = ($itemSubtotal * $taxRate) / 100;
                $itemTotal = $itemSubtotal + $itemTax;

                $quotation->items()->create([
                    'description' => $itemData['description'] ?? 'Item',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $itemTax,
                    'subtotal' => $itemSubtotal,
                    'total' => $itemTotal,
                ]);

                $subtotal += $itemSubtotal;
                $taxAmount += $itemTax;
            }

            $discount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discount;

            $quotation->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount
            ]);

            return $quotation->load('items');
        });
    }

    public function update($id, array $data): Quotation
    {
        return DB::transaction(function () use ($id, $data) {
            if (is_numeric($id)) {
                $quotation = Quotation::findOrFail($id);
            } elseif (Str::isUuid($id)) {
                $quotation = Quotation::where('uuid', $id)->firstOrFail();
            } else {
                abort(404);
            }
            $items = $data['items'] ?? null;

            if ($items !== null) {
                $quotation->items()->delete();
                $subtotal = 0;
                $taxAmount = 0;

                foreach ($items as $itemData) {
                    $qty = $itemData['quantity'] ?? 1;
                    $price = $itemData['unit_price'] ?? 0;
                    $itemSubtotal = $qty * $price;

                    $taxRate = $itemData['tax_rate'] ?? 0;
                    $itemTax = ($itemSubtotal * $taxRate) / 100;
                    $itemTotal = $itemSubtotal + $itemTax;

                    $quotation->items()->create([
                        'description' => $itemData['description'] ?? 'Item',
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $itemTax,
                        'subtotal' => $itemSubtotal,
                        'total' => $itemTotal,
                    ]);

                    $subtotal += $itemSubtotal;
                    $taxAmount += $itemTax;
                }

                $data['subtotal'] = $subtotal;
                $data['tax_amount'] = $taxAmount;
                $discount = $data['discount_amount'] ?? $quotation->discount_amount;
                $data['total_amount'] = $subtotal + $taxAmount - $discount;
            }

            if (isset($data['customer_id'])) {
                if (Str::isUuid($data['customer_id'])) {
                    $data['customer_id'] = Customer::where('uuid', $data['customer_id'])->value('id');
                }
            }

            unset($data['items']);
            $quotation->update($data);

            return $quotation->load('items');
        });
    }

    public function delete($id): bool
    {
        if (is_numeric($id)) {
            $quotation = Quotation::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $quotation = Quotation::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        return $quotation->delete();
    }
}