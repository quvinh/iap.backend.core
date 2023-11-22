<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalanceVat extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_detail_id',
        'count_month',
        'start_month',
        'end_month',
        'money',
        'meta',
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

    /**
     * @return BelongsTo
     */
    public function company_detail(): BelongsTo
    {
        return $this->belongsTo(CompanyDetail::class, 'company_detail_id', 'id');
    }
}
