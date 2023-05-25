<?php

use PHPUnit\Exception;
use Spatie\SslCertificate\SslCertificate;


$https_url = env('APP_CHANNEL_HTTPS_URL', 'https://www.w3.org');
try {
    $cert = SslCertificate::createForHostName($https_url);
    $signature = json_encode([
        "issuer" => $cert->getIssuer(),
        "is_valid" => $cert->isValid(),
        "valid_from_date" => $cert->validFromDate(),
        "expiration_date" => $cert->expirationDate(),
        "fingerprint" => $cert->getFingerprint(),
        "fingerprint256" => $cert->getFingerprintSha256(),
        "organization" => $cert->getOrganization(),
        "serial_number" => ($cert->getSerialNumber())
    ]);
}catch (Exception $ex){
    $signature = "{}";
}

$channelHooks = [];
$hooks = explode(',', env('APP_CHANNEL_HOOKS', ''));
foreach ($hooks as $hook){
    $data = preg_split('/:/', $hook);
    $channelHooks[] = [
        'type' => $data[0],
        'value' => $hook
    ];
}


return [
    'https_url' =>  $https_url,
    'signature' => md5($signature),
    'key' => env('APP_CHANNEL_KEY', 'demo'),
    'hooks' => $channelHooks
];
