<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Mini ERP Microservices Core API",
    version: "1.0.0",
    description: "Comprehensive OpenApi Documentation for Mini ERP Platform Modules: HRM, CRM, Finance, & Machine Learning Face Recognition API.",
    contact: new OA\Contact(email: "admin@example.com")
)]
#[OA\Server(
    url: "/",
    description: "Primary API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "passport",
    type: "http",
    name: "Token Based",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Use a token from Passport auth"
)]
abstract class Controller
{
    //
}
