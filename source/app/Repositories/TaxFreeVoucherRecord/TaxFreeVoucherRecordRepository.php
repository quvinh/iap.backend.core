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
     * meta: {
     *  year: 2023,
     *  data: [ // tax_free_voucher_records table
     *      { id:number, account_number:string, name:string, value:number },
     *  ]
     * }
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
            $meta_mutate = self::computeMeta([], $com->year);
            if ($count_month < 0) throw new \Exception(message: "Count month must be greater than 0");
            // if ($count_month == 0) {}
            if ($count_month > 0) {
                # Search entities
                $entities = TaxFreeVoucherRecord::query()
                    ->where([
                        ['company_detail_id', '=', $com->id],
                        ['count_month', '=', 0],
                        ['start_month', '>=', $params['start_month']],
                        ['end_month', '<=', $params['end_month']],
                    ])->get();
                $meta_mutate = self::computeMeta($entities, $com->year);
            }
            if (empty($record)) {
                # Create new record
                $result = new TaxFreeVoucherRecord();
                $result->company_detail_id = $com->id;
                $result->count_month = $count_month;
                $result->start_month = $params['start_month'];
                $result->end_month = $params['end_month'];
                $result->meta = json_encode($meta_mutate);
                if ($result->save()) return $result;
            } else {
                if ($reset) $record->meta = json_encode($meta_mutate);
                if ($record->save()) return $record;
            }
        }
        # Return
        return $record;
    }

    /**
     * Compute meta
     */
    function computeMeta($entities, $year): array
    {
        $meta_initialize = [
            'year' => $year ?? date('Y'),
        ];
        # Get meta
        if (count($entities) == 0) return $meta_initialize;
        $data = array();
        foreach ($entities as $entity) {
            $meta = json_decode($entity->meta);
            if (!empty($meta->data) && count($meta->data) > 0) {
                foreach ($meta->data as $row) {
                    if (empty($row->id)) continue;
                    $idCheck = $row->id;
                    $filter = array_filter($data, function ($item) use ($idCheck) {
                        return !empty($item->id) && $item->id == $idCheck;
                    });
                    if (empty($filter)) {
                        $data[] = $row;
                    } else {
                        $postion = array_search($idCheck, array_column($data, 'id'));
                        $data[$postion] = (object)array_merge((array) $row, [
                            'value' => floatval($data[$postion]->value + $row->value),
                        ]);
                    }
                }
            }
        }
        if (count($data) > 0) $meta_initialize['data'] = $data;
        # Return
        return $meta_initialize;
    }
}
