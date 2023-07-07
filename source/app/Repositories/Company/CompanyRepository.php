<?php

namespace App\Repositories\Company;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\Company;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class CompanyRepository extends BaseRepository implements ICompanyRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return Company::class;
    }

    /**
     * Get all companies
     */
    public function getAllCompanies(): Collection
    {
        $companies = Company::where('status', 1)->orderByDesc('id')->get();
        return $companies;
    }
}
