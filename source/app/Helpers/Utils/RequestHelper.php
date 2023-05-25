<?php
namespace App\Helpers\Utils;

use App\Helpers\Common\MetaInfo;
use App\Helpers\Enums\SupportedLanguages;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class RequestHelper
{
    /**
     * Detect and return command meta information
     * @param Authenticatable|null $identity
     * @return MetaInfo
     */
    public static function getMetaInfo(Authenticatable|null $identity = null): MetaInfo
    {
        $ip = RequestHelper::getUserIpAddress();
        $name = ($identity != null)?
            $identity->id."|".$identity->name : null;
        $browser =  $_SERVER["HTTP_USER_AGENT"];
        $identifier = $_SERVER["HTTP_X_IDENTIFIER"] ?? '';
        $signature = $ip."|".$browser;
        $meta = MetaInfo::doneBy(connectionIdentifier: $identifier, name: ($name)? $name: "web", signature: $signature);
        $meta->lang = $_SERVER['HTTP_X_LANG'] ?? SupportedLanguages::VIETNAMESE;
        $meta->refreshTime();
        return $meta;
    }

    protected static function getUserIpAddress(){
        if(!empty( $_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function getDeviceId($prefix = null){
        $browser =  $_SERVER["HTTP_USER_AGENT"];
        $signature = $prefix."|".config("app.name")."|".$browser;
        return md5($signature);
    }

    public static function findHanshakeHook(string $hash){
        if (! isset($_SERVER["HTTP_X_IDENTIFIER"])) return null;
        $identifier = $_SERVER["HTTP_X_IDENTIFIER"];
        $hooks = config('channel.hooks');
        foreach ($hooks as $hook){
            $trialConnectionCredential = $hook['value'].$identifier;
            if (Hash::check($trialConnectionCredential, $hash)){
                return $hook;
            }
        }
        return null;
    }

    public static function getClaims(){
        try {
            $jwt = App::make('tymon.jwt.auth');
            $payload = $jwt->parseToken()->getPayload();
            return $payload->getClaims();
        } catch (\Exception $e){
            return null;
        }
    }

}
?>
