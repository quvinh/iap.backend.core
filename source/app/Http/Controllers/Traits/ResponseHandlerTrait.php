<?php

namespace App\Http\Controllers\Traits;

use App\Helpers\Responses\ApiResponse;

trait ResponseHandlerTrait
{
    protected ApiResponse $responseHandler;
    /**
     * return response handler
     * @return mixed
     */
    public function getResponseHandler(): ApiResponse {
        if (! isset($this->responseHandler)) {
            $this->responseHandler = ApiResponse::v1();
        }
        return $this->responseHandler;
    }
}
