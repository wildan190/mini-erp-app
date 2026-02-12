<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Mini ERP CRM API",
    version: "1.0.0",
    description: "API Documentation for CRM Module in Mini ERP App",
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
abstract class Controller
{
    //
}
