<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Employee;
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
    public function getAllEmployees(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Employee::with(['user.roles', 'department', 'designation', 'shift']);

        if (isset($filters['department_uuid'])) {
            $departmentId = \App\Domain\HRM\Models\Department::where('uuid', $filters['department_uuid'])->value('id');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
        }

        if (isset($filters['designation_uuid'])) {
            $designationId = \App\Domain\HRM\Models\Designation::where('uuid', $filters['designation_uuid'])->value('id');
            if ($designationId) {
                $query->where('designation_id', $designationId);
            }
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('emp_code', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'ilike', "%{$search}%")
                         ->orWhere('email', 'ilike', "%{$search}%");
                  });
            });
        }

        $today = now()->toDateString();
        $query->with(['leaveRequests' => function ($lq) use ($today) {
            $lq->where('status', 'approved')
               ->whereDate('start_date', '<=', $today)
               ->whereDate('end_date', '>=', $today)
               ->with('leaveType');
        }]);

        return $query->latest()->paginate($perPage);
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
            // Resolve UUIDs
            if (isset($data['user_uuid'])) {
                $data['user_id'] = \App\Models\User::where('uuid', $data['user_uuid'])->value('id');
            }
            if (isset($data['department_uuid'])) {
                $data['department_id'] = \App\Domain\HRM\Models\Department::where('uuid', $data['department_uuid'])->value('id');
            }
            if (isset($data['designation_uuid'])) {
                $data['designation_id'] = \App\Domain\HRM\Models\Designation::where('uuid', $data['designation_uuid'])->value('id');
            }
            if (isset($data['shift_uuid'])) {
                $data['shift_id'] = \App\Domain\HRM\Models\Shift::where('uuid', $data['shift_uuid'])->value('id');
            }

            if (empty($data['user_id'])) {
                $user = \App\Models\User::create([
                    'name' => trim($data['first_name'] . ' ' . ($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'password' => $data['password'],
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
        // Resolve UUIDs
        if (isset($data['user_uuid'])) {
            $data['user_id'] = \App\Models\User::where('uuid', $data['user_uuid'])->value('id');
        }
        if (isset($data['department_uuid'])) {
            $data['department_id'] = \App\Domain\HRM\Models\Department::where('uuid', $data['department_uuid'])->value('id');
        }
        if (isset($data['designation_uuid'])) {
            $data['designation_id'] = \App\Domain\HRM\Models\Designation::where('uuid', $data['designation_uuid'])->value('id');
        }
        if (isset($data['shift_uuid'])) {
            $data['shift_id'] = \App\Domain\HRM\Models\Shift::where('uuid', $data['shift_uuid'])->value('id');
        }

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
        $query = Employee::with(['user.roles', 'department', 'designation', 'shift']);
        if (is_numeric($id)) {
            return $query->find($id);
        }
        if (Str::isUuid($id)) {
            return $query->where('uuid', $id)->first();
        }
        return null;
    }
}
