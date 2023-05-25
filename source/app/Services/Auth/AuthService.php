<?php

namespace App\Services\Auth;

use App\DataResources\PaginationInfo;
use App\Exceptions\NotImplementedException;
use App\Helpers\Common\MetaInfo;
use App\Helpers\AuthorizationSubject;
use App\Helpers\Utils\RequestHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class AuthService extends \App\Services\BaseService implements IAuthService
{

    /**
     * Generate handshake data
     * @param string $code
     * @param MetaInfo $meta
     * @return AuthorizationSubject|null
     */
    function handshake(string $code, MetaInfo $meta): AuthorizationSubject | null
    {
        try {
            if (empty($meta->identifier)) return null;
            $hook = RequestHelper::findHanshakeHook($code);
            if (is_null($hook)) return null;
            $prefix = config('channel.key');
            $clientSignature = RequestHelper::getDeviceId($prefix);
            $serverSignature = config('channel.signature');
            $sub = new AuthorizationSubject();
            $sub->claims($hook['type'], $meta->identifier, $code, $serverSignature . $clientSignature);
            return $sub;
        }catch (\Exception $ex){
            return null;
        }
    }

    function verifyCommunicationCert(MetaInfo $meta): MetaInfo|null
    {
        return $meta;
    }

    /**
     * @throws NotImplementedException
     */
    public function getSingleObject(int $id, array $withs = []): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function search(array $rawConditions, PaginationInfo &$paging = null, array $withs = []): Collection
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function create(array $param, MetaInfo $commandMetaInfo = null): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function update(int $catId, array $param, MetaInfo $commandMetaInfo = null): Model
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    public function delete(int $id, bool $softDelete = true, MetaInfo $commandMetaInfo = null): bool
    {
        throw new NotImplementedException();
    }
}
