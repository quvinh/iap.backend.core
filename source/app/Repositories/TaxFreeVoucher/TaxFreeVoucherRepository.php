<?php

namespace App\Repositories\TaxFreeVoucher;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucher;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use Illuminate\Support\Collection;

use function Spatie\SslCertificate\starts_with;

class TaxFreeVoucherRepository extends BaseRepository implements ITaxFreeVoucherRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return TaxFreeVoucher::class;
    }

    /**
     * Get all tax free vouchers
     */
    public function getAllTaxFreeVouchers(): Collection
    {
        $taxFreeVouchers = TaxFreeVoucher::where('status', 1)->orderByDesc('id')->get();
        return $taxFreeVouchers;
    }
}
