<?php

namespace App\Domain\HRM\Contracts;

use App\Domain\HRM\Models\Employee;
use App\Domain\HRM\Models\Attendance;

interface HRMServiceInterface
{
    public function getEmployeeByUuid(string $uuid): ?Employee;

    public function clockIn(Employee $employee, array $data): Attendance;

    public function clockOut(Employee $employee, array $data): Attendance;
}
