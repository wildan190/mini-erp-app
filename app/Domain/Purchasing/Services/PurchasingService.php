<?php

namespace App\Domain\Purchasing\Services;

use App\Domain\Purchasing\Contracts\PurchasingServiceInterface;
use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\PurchaseOrder;

class PurchasingService implements PurchasingServiceInterface
{
    public function getSuppliers(array $filters = [], int $perPage = 15)
    {
        $query = Supplier::query();
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
        }
        return $query->paginate($perPage);
    }

    public function createPurchaseRequest(array $data): PurchaseRequest
    {
        return PurchaseRequest::create($data);
    }

    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return PurchaseOrder::create($data);
    }
}
