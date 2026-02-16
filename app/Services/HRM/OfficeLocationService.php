<?php

namespace App\Services\HRM;

use App\Models\HRM\OfficeLocation;
use Illuminate\Pagination\LengthAwarePaginator;

class OfficeLocationService
{
    /**
     * Get office locations with filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOfficeLocations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return OfficeLocation::when(isset($filters['is_active']), function ($query) use ($filters) {
            $query->where('is_active', $filters['is_active']);
        })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create office location.
     *
     * @param array $data
     * @return OfficeLocation
     */
    public function createOfficeLocation(array $data): OfficeLocation
    {
        return OfficeLocation::create($data);
    }

    /**
     * Update office location.
     *
     * @param OfficeLocation $officeLocation
     * @param array $data
     * @return OfficeLocation
     */
    public function updateOfficeLocation(OfficeLocation $officeLocation, array $data): OfficeLocation
    {
        $officeLocation->update($data);
        return $officeLocation;
    }

    /**
     * Delete office location.
     *
     * @param OfficeLocation $officeLocation
     * @return bool
     */
    public function deleteOfficeLocation(OfficeLocation $officeLocation): bool
    {
        return $officeLocation->delete();
    }
}
