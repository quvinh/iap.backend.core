<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Company\ICompanyService;
use App\Services\Invoice\IInvoiceService;
use App\Services\InvoiceTask\IInvoiceTaskService;
use App\Services\User\IUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
{
    private ICompanyService $companyService;
    private IInvoiceTaskService $inoviceTaskService;
    private IUserService $userService;
    private IInvoiceService $inoviceService;

    public function __construct(
        ICompanyService $companyService,
        IInvoiceTaskService $inoviceTaskService,
        IUserService $userService,
        IInvoiceService $inoviceService
    ) {
        $this->companyService = $companyService;
        $this->inoviceTaskService = $inoviceTaskService;
        $this->userService = $userService;
        $this->inoviceService = $inoviceService;
    }

    /**
     * Register default routes
     * @param string|null $pdfTableKey
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'dashboard';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::get($root . '/amount-companies', [DashboardController::class, 'amountCompanies']);
            Route::get($root . '/task-not-process', [DashboardController::class, 'taskNotProcess']);
            Route::get($root . '/amount-users', [DashboardController::class, 'amountUsers']);
            Route::get($root . '/monthly-invoice', [DashboardController::class, 'monthlyInvoice']);
            Route::get($root . '/monthly-task', [DashboardController::class, 'monthlyTask']);
            Route::get($root . '/overview', [DashboardController::class, 'overview']);
        }
    }

    /**
     * Amount companies
     */
    public function amountCompanies()
    {
        $record = $this->companyService->getAllCompanies();
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send(['amount' => count($record)]);
    }

    /**
     * Get task not process in this month
     */
    public function taskNotProcess()
    {
        $record = $this->inoviceTaskService->getTaskNotProcess();
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send(['amount' => count($record)]);
    }

    /**
     * Amount users
     */
    public function amountUsers()
    {
        $record = $this->userService->getAllUsers();
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send(['amount' => count($record)]);
    }

    /**
     * Monthly invoice
     */
    public function monthlyInvoice()
    {
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send(false);
    }

    /**
     * Monthly task
     */
    public function monthlyTask()
    {
        $record = $this->inoviceTaskService->monthlyTask();
        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($record);
    }

    public function overview()
    {
        $result = [
            'companies' => count($this->companyService->getAllCompanies()),
            'task_not_process' => count($this->inoviceTaskService->getTaskNotProcess()),
            'users' => count($this->userService->getAllUsers()),
            'invoice_media_not_completed' => $this->inoviceTaskService->invoiceMediaNotCompleted(),

            'month_invoice' => $this->inoviceTaskService->monthlyInvoice(),
            'month_task' => $this->inoviceTaskService->monthlyTask(),
        ];

        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
