<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JobHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class JobHistoryController extends Controller
{
    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'job-histories';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::get($root, [JobHistoryController::class, 'index']);
        }
    }

    public function index(Request $request)
    {
        $query = JobHistory::query()->orderByDesc('created_at')->with('company:id,name');

        if (isset($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        $result = $query->take(25)->get();

        # Send response using the predefined format
        $response = ApiResponse::v1();
        return $response->send($result);
    }
}
