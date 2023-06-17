<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Formula extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'note',
        'company_detail_id',
        'company_type_id',
        'status'
    ];

    /**
     * @return HasOne
     */
    public function company_detail(): HasOne
    {
        return $this->hasOne(CompanyDetail::class, 'id', 'company_detail_id');
    }
}
