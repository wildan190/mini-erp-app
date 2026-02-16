<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    /**
     * Get all employees with pagination and relationships.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllEmployees(int $perPage = 15): LengthAwarePaginator
    {
        return Employee::with(['user', 'department', 'designation'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new employee.
     *
     * @param array $data
     * @return Employee
     */
    public function createEmployee(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Update an employee.
     *
     * @param Employee $employee
     * @param array $data
     * @return Employee
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee;
    }

    /**
     * Delete an employee.
     *
     * @param Employee $employee
     * @return bool|null
     */
    public function deleteEmployee(Employee $employee): ?bool
    {
        return $employee->delete();
    }

    /**
     * Find employee by ID.
     *
     * @param int $id
     * @return Employee|null
     */
    public function findEmployee(int $id): ?Employee
    {
        return Employee::with(['user', 'department', 'designation'])->find($id);
    }
}
