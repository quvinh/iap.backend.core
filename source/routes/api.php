<?php

use App\Helpers\Enums\UserRoles;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\CategoryPurchaseController;
use App\Http\Controllers\Api\CategorySoldController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CompanyDetailController;
use App\Http\Controllers\Api\CompanyDetailTaxFreeVoucherController;
use App\Http\Controllers\Api\CompanyTypeController;
use App\Http\Controllers\Api\FirstAriseAccountController;
use App\Http\Controllers\Api\FormulaCategoryPurchaseController;
use App\Http\Controllers\Api\FormulaCategorySoldController;
use App\Http\Controllers\Api\FormulaCommodityController;
use App\Http\Controllers\Api\FormulaController;
use App\Http\Controllers\Api\FormulaMaterialController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceDetailController;
use App\Http\Controllers\Api\InvoiceTaskController;
use App\Http\Controllers\Api\ItemCodeController;
use App\Http\Controllers\Api\ItemGroupController;
use App\Http\Controllers\Api\MediaStorageController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TaxFreeVoucherController;
use App\Http\Controllers\Api\TaxFreeVoucherRecordController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

function registerResourceRoutes(string $group = UserRoles::ADMINISTRATOR): void
{
    MediaStorageController::registerRoutes($group);

    AuthenticationController::registerRoutes($group);
    UserController::registerRoutes($group);
    RoleController::registerRoutes($group);
    PermissionController::registerRoutes($group);
    CompanyController::registerRoutes($group);
    CompanyTypeController::registerRoutes($group);
    CompanyDetailController::registerRoutes($group);
    FirstAriseAccountController::registerRoutes($group);
    CategoryPurchaseController::registerRoutes($group);
    CategorySoldController::registerRoutes($group);
    FormulaController::registerRoutes($group);
    FormulaCategoryPurchaseController::registerRoutes($group);
    FormulaCategorySoldController::registerRoutes($group);
    FormulaCommodityController::registerRoutes($group);
    FormulaMaterialController::registerRoutes($group);
    TaxFreeVoucherController::registerRoutes($group);
    CompanyDetailTaxFreeVoucherController::registerRoutes($group);
    InvoiceTaskController::registerRoutes($group);
    ItemCodeController::registerRoutes($group);
    InvoiceController::registerRoutes($group);
    InvoiceDetailController::registerRoutes($group);
    ItemGroupController::registerRoutes($group);
    TaxFreeVoucherRecordController::registerRoutes($group);

    CommandController::registerRoutes($group);
}

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'admin', 'middleware' => 'auth.admin'], function () {
    registerResourceRoutes(UserRoles::ADMINISTRATOR);
});

Route::group(['namespace' => 'unauthenticated', 'middleware' => 'unauthenticated'], function () {
    AuthenticationController::registerRoutes(UserRoles::ANONYMOUS);
});

Route::fallback(function () {
    abort(404);
});
