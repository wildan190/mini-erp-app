<?php

namespace App\Domain\Finance\Controllers;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

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
