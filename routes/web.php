<?php

use App\Http\Controllers\Platform\Api\CRM\AutomationSalesForce\QuotationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/crm/quotation/{id}/print', [QuotationController::class, 'print'])->name('quotation.print');

Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');