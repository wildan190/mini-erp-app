<?php

namespace App\Services\HRM;

use App\Models\HRM\Shift;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ShiftService
{
    /**
     * Get all shifts with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllShifts(int $perPage = 15): LengthAwarePaginator
    {
        return Shift::latest()->paginate($perPage);
    }

    /**
     * Get all active shifts (for dropdowns).
     *
     * @return Collection
     */
    public function getActiveShifts(): Collection
    {
        return Shift::all();
    }

    /**
     * Create a new shift.
     *
     * @param array $data
     * @return Shift
     */
    public function createShift(array $data): Shift
    {
        return Shift::create($data);
    }

    /**
     * Update a shift.
     *
     * @param Shift $shift
     * @param array $data
     * @return Shift
     */
    public function updateShift(Shift $shift, array $data): Shift
    {
        $shift->update($data);
        return $shift;
    }

    /**
     * Delete a shift.
     *
     * @param Shift $shift
     * @return bool|null
     */
    public function deleteShift(Shift $shift): ?bool
    {
        return $shift->delete();
    }

    /**
     * Find shift by ID or UUID.
     *
     * @param string|int $id
     * @return Shift|null
     */
    public function findShift(string|int $id): ?Shift
    {
        if (is_numeric($id)) {
            return Shift::find($id);
        }
        if (Str::isUuid($id)) {
            return Shift::where('uuid', $id)->first();
        }
        return null;
    }
}
