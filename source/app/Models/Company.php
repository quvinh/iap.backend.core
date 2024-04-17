<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tax_code',
        'tax_password',
        'email',
        'phone',
        'address',
        'logo',
        'manager_name',
        'manager_role',
        'manager_phone',
        'manager_email',
        'status',
        'registered_date',
        'registration_file',
        'place_of_registration',
    ];

    public function types(): HasManyThrough
    {
        return $this->hasManyThrough(
            CompanyType::class,
            CompanyDetail::class,
            'company_id',
            'id',
            'id',
            'company_type_id'
        );
    }

    public function years(): HasMany
    {
        return $this->hasMany(CompanyDetail::class, 'company_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(CompanyDocument::class, 'company_id', 'id');
    }
}
