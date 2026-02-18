<?php

namespace App\Services\HRM;

use App\Models\HRM\Designation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

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

    /**
     * Find designation by ID or UUID.
     *
     * @param string|int $id
     * @return Designation|null
     */
    public function findDesignation(string|int $id): ?Designation
    {
        if (is_numeric($id)) {
            return Designation::find($id);
        }
        if (Str::isUuid($id)) {
            return Designation::where('uuid', $id)->first();
        }
        return null;
    }
}
