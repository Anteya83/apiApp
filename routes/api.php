<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeDocumentController;
use App\Http\Controllers\OutDocumentController;
use App\Http\Controllers\InventoryDocumentController;
use App\Http\Controllers\HistoryDocumentController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Маршрут для создания прихода
Route::apiResource('income-documents', IncomeDocumentController::class);

// Маршрут для создания расхода
Route::apiResource('out-documents', OutDocumentController::class);

// Маршрут для создания инвент-ций
Route::apiResource('inventory-documents', InventoryDocumentController::class);

// Маршрут для получени результатов инв-ции по дате
Route::get('inventory-results', [InventoryDocumentController::class, 'getInventoryResults']);

// Маршрут для получени истории всех док-ов
Route::get('documents-history', [HistoryDocumentController::class, 'index']);

