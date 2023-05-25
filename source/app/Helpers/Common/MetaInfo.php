<?php
namespace App\Helpers\Common;

use App\Helpers\Enums\SupportedLanguages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MetaInfo
{
    public string $identifier;

    public string $name;

    public Carbon $time;

    public string $signature;

    public string $lang;

    function __construct(string $connectionIdentifier, string $name = "", string $signature = "", string $lang = SupportedLanguages::VIETNAMESE)
    {
        $this->identifier = $connectionIdentifier;
        $this->name = $name;
        $this->time = Carbon::now();
        $this->signature = $signature;
        $this->lang = $lang;
    }

    static function doneBy(string $connectionIdentifier, string $name, string $signature = ""): MetaInfo
    {
        return new MetaInfo(connectionIdentifier: $connectionIdentifier, name: $name, signature: $signature);
    }

    static function parseWebRequest(string $signature): MetaInfo
    {
        $user = Auth::user();
        $author = $user? $user->getAuthIdentifierName() : 'web';
        return MetaInfo::doneBy($author, $signature);
    }

    function refreshTime(): void
    {
        $this->time = Carbon::now();
    }
}
