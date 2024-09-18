<?php

namespace App\DataResources\Company;

use App\DataResources\BaseDataResource;
use App\DataResources\CompanyDetail\CompanyDetailResource;
use App\DataResources\CompanyDocument\CompanyDocumentResource;
use App\DataResources\CompanyType\CompanyTypeResource;
use App\DataResources\User\UserAssignmentResource;
use App\DataResources\User\UserResource;
use App\Models\Company;

class CompanyResource extends BaseDataResource
{
    protected $types;
    protected $years;
    protected $documents;
    protected $contract;
    protected $users;
    protected $userAssignments;
    
    /**
     * @var array|string[]
     */
    protected array $fields = [
        'id',
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
        'created_by'
    ];

    /**
     * Return the model class of this resource
     */
    public function modelClass(): string
    {
        return Company::class;
    }

    /**
     * Load data for output
     * @param Company $obj
     * @return void
     */
    public function load(mixed $obj): void
    {
        parent::copy($obj, $this->fields);

        if (in_array('types', $this->fields)) {
            $this->types = BaseDataResource::generateResources($obj->types, CompanyTypeResource::class);
        }

        if (in_array('years', $this->fields)) {
            $this->years = BaseDataResource::generateResources($obj->years, CompanyDetailResource::class);
        }

        if (in_array('documents', $this->fields)) {
            $this->documents = BaseDataResource::generateResources($obj->documents, CompanyDocumentResource::class);
        }

        if (in_array('contract', $this->fields)) {
            $this->contract = new CompanyDocumentResource($obj->contract()->first());
        }

        if (in_array('users', $this->fields)) {
            $this->users = BaseDataResource::generateResources($obj->users()->orderByDesc('id')->get(), UserResource::class);
        }

        if (in_array('userAssignments', $this->fields)) {
            $this->userAssignments = BaseDataResource::generateResources($obj->userAssignments, UserAssignmentResource::class, ['user']);
        }
    }
}
