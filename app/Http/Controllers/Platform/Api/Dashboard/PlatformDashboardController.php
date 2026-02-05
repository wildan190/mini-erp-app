<?php

namespace App\Http\Controllers\Platform\Api\Dashboard;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class PlatformDashboardController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Welcome to platform dashboard',
            'user' => $request->user()
        ]);
    }
}
