<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;

class PdfTableKey extends Model
{
    use HasFactory;

    /**
     * Store key-api: https://pdftables.com
     */
    protected $fillable = [
        'key',
        'amount',
        'email',
    ];

    public $timestamps = false;

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }
}
