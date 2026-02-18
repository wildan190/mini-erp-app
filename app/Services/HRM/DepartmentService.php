<?php

namespace App\Services\HRM;

use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class DepartmentService
{
    /**
     * Get all departments.
     *
     * @return Collection
     */
    public function getAllDepartments(): Collection
    {
        return Department::all();
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Update a department.
     *
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    /**
     * Find department by ID or UUID.
     *
     * @param string|int $id
     * @return Department|null
     */
    public function findDepartment(string|int $id): ?Department
    {
        if (is_numeric($id)) {
            return Department::find($id);
        }
        if (Str::isUuid($id)) {
            return Department::where('uuid', $id)->first();
        }
        return null;
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool|null
     */
    public function deleteDepartment(Department $department): ?bool
    {
        return $department->delete();
    }
}
