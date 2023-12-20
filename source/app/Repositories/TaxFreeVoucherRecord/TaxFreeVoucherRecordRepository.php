<?php

namespace App\Repositories\TaxFreeVoucherRecord;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\TaxFreeVoucherRecord;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\CompanyDetail;

use function Spatie\SslCertificate\starts_with;

class TaxFreeVoucherRecordRepository extends BaseRepository implements ITaxFreeVoucherRecordRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return TaxFreeVoucherRecord::class;
    }

    /**
     * Find tax free voucher records by month
     * @param array $params
     * @return mixed
     */
    public function find(array $params): mixed
    {
        $reset = $params['reset'] == 1 ? true : false;
        $com = CompanyDetail::find($params['company_detail_id']);
        if (empty($com)) throw new \Exception(message: 'Company detail not found');
        $record = TaxFreeVoucherRecord::query()
            ->where([
                ['company_detail_id', '=', $com->id],
                ['start_month', '=', $params['start_month']],
                ['end_month', '=', $params['end_month']],
            ])->first();
        if (empty($record) || $reset) {
            $count_month = $params['end_month'] - $params['start_month'];
            if ($count_month < 0) throw new \Exception(message: "Count month must be greater than 0");
            if ($count_month == 0) {
                # Not finished yet
                $entities = null;
            }
            if ($count_month > 0) {
                # Not finished yet
                $entities = null;
            }
            if (empty($record)) {
                # Create new record
                $result = new TaxFreeVoucherRecord();
                $result->company_detail_id = $com->id;
                $result->count_month = $count_month;
                $result->start_month = $params['start_month'];
                $result->end_month = $params['end_month'];
                $result->meta = json_encode([
                    'year' => $com->year,
                    # Todo: add payload tax_free_voucher {id, account_number, value}
                ]);
                if ($result->save()) {
                    $result->entities = $entities;
                    return $result;
                }
            } else {
                if ($record->save()) {
                    $record->entities = $entities;
                    return $record;
                }
            }
        }
        # Return
        return $record;
    }
}
