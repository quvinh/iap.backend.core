<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'business_object',
    ];

    /**
     * @return HasManyThrough
     */
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

    /**
     * @return HasMany
     */
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

    public function contract()
    {
        return $this->documents()->where('is_contract', 1)->whereNotNull('signature_date')->orderByDesc('created_at');
    }

    public function user_companies()
    {
        return $this->hasMany(UserCompany::class, 'company_id', 'id');
    }

    /**
     * @return HasManyThrough
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            UserCompany::class,
            'company_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * @return MorphMany
     */
    public function userAssignments(): MorphMany
    {
        return $this->morphMany(UserAssignment::class, 'assignable');
    }
}
