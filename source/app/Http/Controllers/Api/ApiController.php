<?php

namespace app\Http\Controllers\Api;


use App\Helpers\Common\MetaInfo;
use App\Helpers\Utils\RequestHelper;
use Illuminate\Foundation\Auth\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Default controller for the `api` module
 */
abstract class ApiController extends Controller
{
    /**
     * Get current command meta information
     * @return MetaInfo
     */
    public function currentMetaInfo(): MetaInfo{
        $identity = Auth::user();
        return RequestHelper::getMetaInfo($identity);
    }
}
