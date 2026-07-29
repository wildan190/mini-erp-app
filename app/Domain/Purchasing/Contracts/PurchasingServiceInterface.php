<?php

namespace App\Domain\Purchasing\Contracts;

use App\Domain\Purchasing\Models\Supplier;
use App\Domain\Purchasing\Models\PurchaseRequest;
use App\Domain\Purchasing\Models\PurchaseOrder;

interface PurchasingServiceInterface
{
    public function getSuppliers(array $filters = [], int $perPage = 15);

    public function createPurchaseRequest(array $data): PurchaseRequest;

    public function createPurchaseOrder(array $data): PurchaseOrder;
}
