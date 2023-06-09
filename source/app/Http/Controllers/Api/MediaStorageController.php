<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Business\ActionFailException;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Responses\HttpStatuses;
use App\Helpers\Utils\StorageHelper;
use App\Http\Requests\MediaStorage\MediaGetRequest;
use App\Http\Requests\MediaStorage\MediaStoreRequest;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStorageController extends ApiController
{

    /**
     * Register default routes
     * @param string|null $role
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'media';
        if ($role != UserRoles::ANONYMOUS) {
            Route::match(['post'], $root . '/store', [MediaStorageController::class, 'storeImage']);
            Route::match(['get'], $root . '/images/{id}', [MediaStorageController::class, 'getImage'])->withoutMiddleware(['auth.channel']);
        }
    }

    /**
     * @throws ActionFailException
     */
    public function storeImage(MediaStoreRequest $request)
    {
        $response = ApiResponse::v1();
        $id = preg_replace('/-/', '', uuid_create());
        $id = $id . '.' . $request->file('image')->extension();
        $path = Carbon::now()->format('Ymd');
        if ($file = $request->file('image')->storePubliclyAs($path, $id, StorageHelper::TMP_DISK_NAME)) {
            $url = Storage::disk(StorageHelper::TMP_DISK_NAME)->url($file);
            $url = preg_replace('/\/' . $path . '\//', '/', $url);
            //            return $response->send($url);
            return $response->send($id);
        }
        throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_IMAGE_FILE);
    }

    /**
     * Return file
     * @param MediaGetRequest $request
     * @return StreamedResponse|string|null
     */
    public function getImage(MediaGetRequest $request): StreamedResponse|string|null
    {
        $id = $request->get('id');
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);
        if ($disk->exists($id)) return $disk->response($id);
        abort(HttpStatuses::HTTP_NOT_FOUND);
    }
}
