<?php

namespace App\Http\Controllers\Api;

use App\DataResources\InvoiceMedia\InvoiceMediaResource;
use App\Exceptions\Business\ActionFailException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\ErrorCodes;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Helpers\Utils\StorageHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\DefaultRestActions;
use App\Http\Requests\InvoiceMedia\InvoiceMediaCreateRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaReadRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaSearchRequest;
use App\Http\Requests\InvoiceMedia\InvoiceMediaUpdateRequest;
use App\Services\Company\ICompanyService;
use App\Services\IService;
use App\Services\InvoiceMedia\IInvoiceMediaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class InvoiceMediaController extends ApiController
{
    use DefaultRestActions;

    private const DEFAULT_FOLDER_UPLOAD_FILE = 'upload/pdf';
    private const ENDPOINT_PDF_TABLE = 'https://pdftables.com/api';
    private IInvoiceMediaService $invoiceMediaService;
    private ICompanyService $companyService;

    public function __construct(IInvoiceMediaService $service, ICompanyService $companyService)
    {
        $this->invoiceMediaService = $service;
        $this->companyService = $companyService;
    }

    /**
     * Register default routes
     * @param string|null $invoiceMedia
     * @return void
     */
    public static function registerRoutes(string $role = null): void
    {
        $root = 'invoice-media';
        if ($role == UserRoles::ADMINISTRATOR) {
            Route::post($root . '/search', [InvoiceMediaController::class, 'search']);
            Route::get($root . '/{id}', [InvoiceMediaController::class, 'getSingleObject']);
            Route::post($root, [InvoiceMediaController::class, 'create']);
            Route::put($root . '/{id}', [InvoiceMediaController::class, 'update']);
            Route::delete($root . '/{id}', [InvoiceMediaController::class, 'delete']);

            Route::post($root . '/import-pdf', [InvoiceMediaController::class, 'importPDF']);
            Route::get($root . '/file/{slug}', [InvoiceMediaController::class, 'getFile'])->where('slug', '.*');
            Route::post($root . '/read-pdf', [InvoiceMediaController::class, 'readPdf']);
        }
    }

    public function getService(): IService
    {
        return $this->invoiceMediaService;
    }

    public function getRelatedFields(string $actionName): array
    {
        return [];
    }

    public function getCurrentMetaInfo(): MetaInfo
    {
        return $this->currentMetaInfo();
    }

    public function isTranslatable(): bool
    {
        return false;
    }

    public function getDataResourceClass(): string
    {
        return InvoiceMediaResource::class;
    }

    public function getDataResourceExtraFields(string $actionName): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param string $actionName
     * @return Request
     */
    public function validateRequest(Request $request, string $actionName): Request
    {
        switch ($actionName) {
            case 'search':
                $vRequest = InvoiceMediaSearchRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'create':
                $vRequest = InvoiceMediaCreateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'update':
                $vRequest = InvoiceMediaUpdateRequest::createFrom($request);
                $vRequest->validate();
                return $vRequest;
            case 'getSingleObject':
                return $request;
            case 'delete':
                return $request;
            default:
                return $request;
        }
    }

    /**
     * Import files
     */
    public function importPDF(InvoiceMediaCreateRequest $request): mixed
    {
        $root = self::DEFAULT_FOLDER_UPLOAD_FILE;
        $company_id = $request->company_id;
        $year = $request->year;
        # Send response using the predefined format
        $response = ApiResponse::v1();

        if ($request->hasFile('file')) {
            # Check company
            $com = $this->companyService->getSingleObject($company_id);
            if (empty($com)) throw new ActionFailException(code: ErrorCodes::ERR_RECORD_NOT_FOUND);
            $company_taxcode = $com->tax_code;

            # Upload
            $storage = Storage::disk(StorageHelper::TMP_DISK_NAME);
            $checkDirectory = $storage->exists($root);
            if (!$checkDirectory) {
                $storage->makeDirectory($root);
            }
            $result = $storage->put("$root/$year/$company_taxcode", $request->file('file'), 'public');
            if (!empty($result)) {
                $params = [
                    'company_id' => $com->id,
                    'year' => $year,
                    'path' => $result,
                ];

                $record = $this->invoiceMediaService->create($params, $this->getCurrentMetaInfo());
                return $response->send($record);
            }
            throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_FILE);
        }
        throw new ActionFailException(code: ErrorCodes::ERROR_CANNOT_UPLOAD_FILE);
    }

    /**
     * Get the system file
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function getFile(string $slug): HttpResponse
    {
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);

        if (!$disk->exists($slug)) {
            $filePath = null;
        } else {
            $filePath = $disk->path($slug);
        }

        $filePath = $filePath ?? resource_path() . '/images/default/default-thumbnail.jpg';

        $file = File::get($filePath);
        $type = File::mimeType($filePath);

        // $response = Response::make($file, 200);
        // $response->header("Content-Type", $type);

        # TODO: convert data:base64
        $fileBase64Data = base64_encode($file);
        $fileBase64Uri = "data:$type;base64,$fileBase64Data";
        return $this->getResponseHandler()->send([
            'type' => $type,
            'uri' => $fileBase64Uri,
        ]);
    }

    /**
     * Read file pdf with api pdf-table
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function readPDF(InvoiceMediaReadRequest $request): HttpResponse
    {
        $uri = self::ENDPOINT_PDF_TABLE;
        $id = $request->id;
        $key = $request->key;
        $format = $request->format ?? 'html';
        $record = $this->invoiceMediaService->getSingleObject($id);
        $slug = $record->path ?? 'xxx';
        $disk = Storage::disk(StorageHelper::TMP_DISK_NAME);

        if (!$disk->exists($slug)) {
            $filePath = null;
        } else {
            $filePath = $disk->path($slug);
        }

        $filePath = $filePath ?? resource_path() . '/images/default/default-thumbnail.jpg';

        $file = File::get($filePath);
        $type = File::mimeType($filePath);
        $extension = File::extension($filePath);

        # Fetch api post pdf-table
        $headers = [];
        $response = Http::withHeaders($headers)
            ->attach('file', $file, "filename.$extension")
            ->post("$uri?key=$key&format=$format");

        # Handle body
        $listItem = array();
        $remaining = array();
        if ($response->status() == 200) {
            $html = str_get_html($response->body());
            $numberPage = count($html->find('table'));
            for ($i = 0; $i < $numberPage; $i++) {
                $table = $html->find('table', $i);
                $rowData = array();

                foreach ($table->find('tr') as $row) {
                    $keeper = array();
                    foreach ($row->find('td, th') as $cell) {
                        if (trim($cell->plaintext) != '') $keeper[] = $cell->plaintext;
                    }
                    $rowData[] = $keeper;
                }
                # Index position column
                $idxItem = 1;
                $idxUnit = 2;
                $idxAmount = 3;
                $idxPrice = 4;
                $idxTotal = 5;

                foreach ((array)$rowData as $row) {
                    if (is_numeric($row[0]) && $row[1] != '2') {
                        $checkPrice = explode(' ', str_replace('.', '', $row[intval($idxPrice)]));
                        $checkTotal = str_replace('.', '', $row[intval($idxTotal)]);
                        if (is_numeric(str_replace(',', '.', $checkTotal))) {
                            $price = $this->filterNumber($row[intval($idxPrice)]);
                            $total = $this->filterNumber($row[intval($idxTotal)]);
                            if (count($checkPrice) > 1) {
                                $price = $this->filterNumber($checkPrice[0]);
                                $total = $this->filterNumber($checkPrice[1]);
                            }
                            $value = [
                                'product' => $row[intval($idxItem)],
                                'unit' => $row[intval($idxUnit)],
                                'amount' => $this->filterNumber($row[intval($idxAmount)]),
                                'price' => $price,
                                'total' => $total,
                            ];
                            array_push($listItem, $value);
                        }
                    }
                }
            }

            # Get remaining
            $remaining = $this->countRemainingPdfTable($key);
        }

        return $this->getResponseHandler()->send([
            'status' => $response->status(),
            'rows' => $listItem,
            'remaining' => $remaining,
        ]);
    }

    function filterNumber($number)
    {
        $num = str_replace('.', '', $number);
        return str_replace(',', '.', $num);
    }

    /**
     * Get remaining api pdf-table
     * @param string $id
     * @return array
     */
    public function countRemainingPdfTable(string $key): array//HttpResponse
    {
        $uri = self::ENDPOINT_PDF_TABLE;
        # Fetch api get remaining pdf-table
        $response = Http::get("$uri/remaining?key=$key");
        return [
            'status' => $response->status(),
            'count' => json_decode($response->body()),
        ];
    }
}
