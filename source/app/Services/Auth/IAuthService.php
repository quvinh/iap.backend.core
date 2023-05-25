<?php
namespace App\Services\Auth;

use App\Helpers\Common\MetaInfo;
use App\Helpers\AuthorizationSubject;
use App\Services\IService;

interface IAuthService extends  IService{
    function handshake(string $code, MetaInfo $meta): AuthorizationSubject | null;
    function verifyCommunicationCert(MetaInfo $meta): MetaInfo | null;
}
