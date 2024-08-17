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
            Route::put($root . '/{id}', [JobHistoryController::class, 'update']);
            Route::delete($root . '/{id}', [JobHistoryController::class, 'delete']);
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

    public function update(Request $request, int $id)
    {
        # Send response using the predefined format
        $response = ApiResponse::v1();

        $record = JobHistory::find($id);
        if (empty($record)) return $response->withStatusCode(404)->fail("Record not found");

        $record->update($request->input());

        return $response->send($record);
    }

    public function delete(int $id)
    {
        # Send response using the predefined format
        $response = ApiResponse::v1();

        $record = JobHistory::find($id);
        if (empty($record)) return $response->withStatusCode(404)->fail("Record not found");

        $record->delete();

        return $response->send(true);
    }
}
