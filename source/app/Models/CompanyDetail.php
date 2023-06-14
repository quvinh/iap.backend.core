<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;

class CompanyDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_type_id',
        'description',
        'year'
    ];

    public $timestamps = false;

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function type(): HasOne
    {
        return $this->hasOne(CompanyType::class, 'id', 'company_type_id');
    }

    /**
     * Meta info
     */
    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo('');
    }
}
