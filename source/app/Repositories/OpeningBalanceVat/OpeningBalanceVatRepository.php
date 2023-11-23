<?php

namespace App\Repositories\OpeningBalanceVat;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\OpeningBalanceVat;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\CompanyDetail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

use function Spatie\SslCertificate\starts_with;

class OpeningBalanceVatRepository extends BaseRepository implements IOpeningBalanceVatRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return OpeningBalanceVat::class;
    }

    /**
     * Find money vat
     */
    public function find(array $params): mixed
    {
        $reset = $params['reset'] == 1 ? true : false;
        $com = CompanyDetail::find($params['company_detail_id']);
        if (empty($com)) throw new \Exception(message: 'Company detail not found');
        $record = OpeningBalanceVat::query()
            ->where([
                ['company_detail_id', '=', $com->id],
                ['start_month', '=', $params['start_month']],
                ['end_month', '=', $params['end_month']],
            ])->first();
        if (empty($record) || $reset) {
            $count_month = $params['end_month'] - $params['start_month'];
            $money = 0;
            if ($count_month < 0) throw new \Exception(message: "Count month must be greater than 0");
            if ($count_month == 0) {
                # Search last month
                $entities = OpeningBalanceVat::query()
                    ->where([
                        ['company_detail_id', '=', $com->id],
                        ['count_month', '=', 0],
                        ['end_month', '<', $params['end_month']],
                    ])->orderBy('start_month')->get();
                foreach ($entities as $entity) {
                    $money += $entity->money;
                }
            }
            if ($count_month > 0) {
                # Search total money
                $entities = OpeningBalanceVat::query()
                    ->where([
                        ['company_detail_id', '=', $com->id],
                        ['count_month', '=', 0],
                        ['start_month', '>=', $params['start_month']],
                        ['end_month', '<=', $params['end_month']],
                    ])->orderBy('start_month')->get();
                foreach ($entities as $entity) {
                    $money += $entity->money;
                }
            }
            if (empty($record)) {
                # Create new record
                $result = new OpeningBalanceVat();
                $result->company_detail_id = $com->id;
                $result->count_month = $count_month;
                $result->start_month = $params['start_month'];
                $result->end_month = $params['end_month'];
                $result->money = $money;
                $result->meta = json_encode([
                    'year' => $com->year,
                ]);
                if ($result->save()) {
                    $result->entities = $entities;
                    return $result;
                }
            } else {
                $record->money = $money;
                if ($record->save()) {
                    $record->entities = $entities;
                    return $record;
                }
            }            
            throw new \Exception(message: "Action failed");
        }
        return $record;
    }
}
