<?php

namespace App\Http\Controllers\Platform\Api\Finance;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Mini ERP Finance AI API",
    version: "1.0.0",
    description: "Finance and AI Analytics API for Mini ERP App - Includes FP&A, Supply Chain AI, and Cash Forecasting",
    contact: new OA\Contact(email: "finance@example.com")
)]
#[OA\Server(
    url: "/",
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
abstract class FinanceBaseController extends Controller
{
    protected function success($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function error($message = 'Error', $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }
}
