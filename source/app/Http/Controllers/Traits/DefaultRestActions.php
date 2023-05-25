<?php

namespace App\Http\Controllers\Traits;

use App\DataResources\BaseDataResource;
use App\DataResources\Category\FAQResource;
use App\Exceptions\Business\InvalidModelInstanceException;
use App\Exceptions\Request\InvalidPaginationInfoException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\SupportedLanguages;
use App\Helpers\Enums\UserRoles;
use App\Helpers\Responses\ApiResponse;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Requests\Category\CategorySearchRequest;
use App\Http\Requests\DefaultIdSlugRequest;
use App\Http\Requests\DefaultSearchRequest;
use App\Services\IService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Nette\Schema\ValidationException;
use Spatie\FlareClient\Api;

trait DefaultRestActions
{
    use ResponseHandlerTrait;

    /**
     * Get current service
     * @return mixed
     */
    public abstract function getService(): IService;

    /**
     * return related fields should be fetched togher
     * @param string $actionName
     * @return string[]
     */
    public abstract function getRelatedFields(string $actionName): array;

    /**
     * return current command meta information
     * @return mixed
     */
    public abstract function getCurrentMetaInfo(): MetaInfo;

    /**
     * turn translator on or off
     * @return mixed
     */
    public abstract function isTranslatable(): bool;

    /**
     * return data resource class
     * @return string
     */
    public abstract function getDataResourceClass(): string;

    /**
     * return field names should be generated on the date resource
     * @return string[]
     */
    public abstract function getDataResourceExtraFields(string $actionName): array;

    /**
     * @param Request $request
     * @param string $actionName
     * @return void
     * @throws ValidationException
     */
    public abstract function validateRequest(Request $request, string $actionName): \Illuminate\Http\Request;

    /**
     * Get a single object by id
     * @param DefaultIdSlugRequest $request
     * @return Response
     * @throws InvalidModelInstanceException
     */
    public function getSingleObject(DefaultIdSlugRequest $request): Response
    {
        # 1. Call business processes
        $request = $this->validateRequest($request, 'getSingleObject');
        $id = $request->input('id');
        $meta = $this->getCurrentMetaInfo();
        $requireToTranslate = false;
        $withs = $request->input('withs')?? [];
        if ($this->isTranslatable() && $meta->lang !== SupportedLanguages::DEFAULT_LOCALE) {
            $requireToTranslate = true;
            $withs = array_merge($withs, $this->getRelatedFields('getSingleObject'));
        }
        $record = $this->getService()->getSingleObject($id, $withs);

        # 2. Translate if required
        if ($requireToTranslate) {
            $record->translate($meta->lang);
        }

        # 3. Convert result to output resource
        $extraFields = array_merge($withs, $this->getDataResourceExtraFields('getSingleObject'));
        $resourceClass = $this->getDataResourceClass();
        $ret = new $resourceClass($record, $extraFields);
        # 4. Send response using the predefined format
        return $this->getResponseHandler()->send($ret);
    }

    /**
     * Search list of categories
     * @param Request $request
     * @return Response
     * @throws InvalidPaginationInfoException
     */
    public function search(Request $request): Response
    {
        # 1. get validated payload
        $request = $this->validateRequest($request, 'search');
        $payload = $request->input();
        $meta = $this->getCurrentMetaInfo();
        $requireToTranslate = false;
        $withs = $payload['withs']?? [];
        if ($this->isTranslatable() && $meta->lang !== SupportedLanguages::DEFAULT_LOCALE) {
            $requireToTranslate = true;
            $withs = array_merge($withs, $this->getRelatedFields('search'));
        }
        # 2. get pagination if any
        $paging = $request->getPaginationInfo();

        # 3. Call business processes
        $result = $this->getService()->search($payload, paging: $paging, withs: $withs);

        # 4. Translate if required
        if ($requireToTranslate) {
            foreach ($result as $item) $item->translate($meta->lang);
        }

        # 5. Convert result to output resource
        $resourceClass = $this->getDataResourceClass();
        $result = BaseDataResource::generateResources($result, $resourceClass, $withs);

        # 6. Send response using the predefined format
        $response = $this->getResponseHandler();
        if (!is_null($paging)) $response = $response->withTotalPages($paging->lastPage, $paging->total);
        return $response->send($result);
    }

    /**
     * Create new object
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        # 1. get validated payload
        $request = $this->validateRequest($request, 'create');
        $payload = $request->input();
        # 2. Call business processes
        $result = $this->getService()->create($payload, $this->getCurrentMetaInfo());
        # 3. Send response using the predefined format
        $resourceClass = $this->getDataResourceClass();
        $ret = new $resourceClass($result);
        return $this->getResponseHandler()->send($ret);
    }

    /**
     * Update object
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        # 1. get validated payload
        $request = $this->validateRequest($request, 'update');
        $payload = $request->input();
        $meta = $this->getCurrentMetaInfo();
        $requireToTranslate = false;
        $withs = [];
        if ($this->isTranslatable() && $meta->lang !== SupportedLanguages::DEFAULT_LOCALE) {
            $requireToTranslate = true;
            $withs = $this->getRelatedFields('update');
        }
        # 2. Call business processes
        $record = $this->getService()->update($id, $payload, $this->getCurrentMetaInfo());
        # 3. Translate if required
        if ($requireToTranslate) {
            $record->translate($meta->lang);
        }
        # 4. Convert result to output resource
        $extraFields = $this->getDataResourceExtraFields('update');
        $resourceClass = $this->getDataResourceClass();
        $ret = new $resourceClass($record, $extraFields);
        # 4. Send response using the predefined format
        return $this->getResponseHandler()->send($ret);
    }

    /**
     * Delete object
     * @param DefaultIdSlugRequest $request
     * @return Response
     */
    public function delete(DefaultIdSlugRequest $request): mixed
    {
        $request = $this->validateRequest($request, 'delete');
        $ret = $this->getService()->delete($request->input('id'), commandMetaInfo: $this->getCurrentMetaInfo());
        return $this->getResponseHandler()->report($ret);
    }
}
