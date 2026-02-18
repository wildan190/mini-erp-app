<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            if (empty($data['user_id'])) {
                $user = \App\Models\User::create([
                    'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                ]);
                $data['user_id'] = $user->id;
            }

            return Employee::create($data);
        });
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
     * Find employee by ID or UUID.
     *
     * @param string|int $id
     * @return Employee|null
     */
    public function findEmployee(string|int $id): ?Employee
    {
        $query = Employee::with(['user', 'department', 'designation']);
        if (is_numeric($id)) {
            return $query->find($id);
        }
        if (Str::isUuid($id)) {
            return $query->where('uuid', $id)->first();
        }
        return null;
    }
}
