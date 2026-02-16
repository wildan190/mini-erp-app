<?php

namespace App\Services\HRM;

use App\Models\HRM\Designation;
use Illuminate\Database\Eloquent\Collection;

class DesignationService
{
    /**
     * Get all designations.
     *
     * @return Collection
     */
    public function getAllDesignations(): Collection
    {
        return Designation::all();
    }

    /**
     * Create a new designation.
     *
     * @param array $data
     * @return Designation
     */
    public function createDesignation(array $data): Designation
    {
        return Designation::create($data);
    }

    /**
     * Update a designation.
     *
     * @param Designation $designation
     * @param array $data
     * @return Designation
     */
    public function updateDesignation(Designation $designation, array $data): Designation
    {
        $designation->update($data);
        return $designation;
    }

    /**
     * Delete a designation.
     *
     * @param Designation $designation
     * @return bool|null
     */
    public function deleteDesignation(Designation $designation): ?bool
    {
        return $designation->delete();
    }
}
