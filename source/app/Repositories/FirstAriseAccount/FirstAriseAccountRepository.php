<?php

namespace App\Repositories\FirstAriseAccount;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\FirstAriseAccount;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class FirstAriseAccountRepository extends BaseRepository implements IFirstAriseAccountRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return FirstAriseAccount::class;
    }

    /**
     * Get all arise accounts
     */
    public function getAllAriseAccounts(): Collection
    {
        $accounts = FirstAriseAccount::where('status', 1)->orderByDesc('number_percent')->orderBy('name')->get();
        return $accounts;
    }
}
