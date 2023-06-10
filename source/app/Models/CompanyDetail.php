<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanyDetail extends BaseModel
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

    public function companyType(): HasOne
    {
        return $this->hasOne(CompanyType::class, 'id', 'company_type_id');
    }
}
