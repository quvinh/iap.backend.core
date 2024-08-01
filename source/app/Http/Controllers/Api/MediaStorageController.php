<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Business\ActionFailException;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Responses\HttpStatuses;
use App\Helpers\Utils\StorageHelper;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\MediaStorage\MediaGetRequest;
use App\Http\Requests\MediaStorage\MediaStoreRequest;
use App\Services\GoogleDriveService\GoogleDriveService;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStorageController extends ApiController
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

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
            Route::match(['get'], $root . '/images', [MediaStorageController::class, 'getImage'])->withoutMiddleware(['auth.channel']);
            Route::post($root . '/upload-google', [MediaStorageController::class, 'upload']);
        }

        Route::get($root . '/{slug}', [MediaStorageController::class, 'retrieveFile'])->where('slug', '.*');
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
        $url = $request->url;
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);
        if ($disk->exists($url)) return $disk->response($url);
        // abort(HttpStatuses::HTTP_NOT_FOUND);
        throw new ActionFailException(code: ErrorCodes::ERR_FILE_NOT_FOUND);
    }

    /**
     * Get the system images
     * @param Request $request
     * @param string $slug
     * @return Response
     */
    public function retrieveFile(Request $request, string $slug): HttpResponse
    {
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);
        if ($disk->exists($slug)) {
            $filePath = $disk->path($slug);
        }

        $filePath = $filePath ?? public_path() . '/images/default-thumbnail.jpg';
        $file = File::get($filePath);
        $type = File::mimeType($filePath);

        $response = FacadesResponse::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

    public function uploadGoogle(HttpRequest $request)
    {
        $date = Carbon::now()->format('Ymd');
        $storage = Storage::disk(StorageHelper::CLOUD_DISK_NAME);
        $response = ApiResponse::v1();
        if ($request->hasFile('file')) {
            $folder = "excel/$date";

            if ($file = $storage->put($folder, $request->file('file'))) {
                $dir = "/$folder";
                $recursive = false; // Có lấy file trong các thư mục con không?
                $contents = collect($storage->listContents($dir, $recursive));

                # Return
                return $response->send([
                    'file' => $file,
                    'list' => $contents, //->sortByDesc('last_modified'),
                ]);
            }
            return $response->fail(['msg' => 'Cannot upload file!']);
        } else return $response->fail(['msg' => 'File not found!']);
    }

    public function upload(HttpRequest $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $date = Carbon::now()->format('Ymd');
        $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
        $folder = "excel/$date";

        $file = $request->file('file');
        $filePath = $storage->put($folder, $file);
        $fileName = $file->getClientOriginalName();

        $uploadResult = $this->googleDriveService->uploadFile($filePath, $fileName);

        if ($uploadResult) {
            $convertResult = $this->googleDriveService->convertToSpreadsheet($uploadResult->id);

            return response()->json([
                'message' => 'File uploaded and converted successfully.',
                'file_id' => $convertResult->id,
                'mimeType' => $convertResult->mimeType
            ]);
        }

        return response()->json(['message' => 'File upload failed.'], 500);
    }
}
