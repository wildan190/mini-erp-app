<?php

namespace App\Http\Controllers\Platform\Api\HRM;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Mini ERP HRM API",
    version: "1.0.0",
    description: "Human Resources Management API Documentation for Mini ERP App - Includes Employee Management, Attendance, Leave, Payroll, Reimbursements, Resignations, Office Locations, and Analytics & Reporting",
    contact: new OA\Contact(email: "admin@example.com")
)]
#[OA\Server(
    url: "http://localhost:8001",
    description: "Primary API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    name: "Token Based",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Use a token from Sanctum auth"
)]
abstract class HRMBaseController
{
    //
}
