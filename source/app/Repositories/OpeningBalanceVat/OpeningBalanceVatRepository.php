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
        $com = CompanyDetail::find($params['company_detail_id']);
        if (empty($com)) throw new \Exception(message: 'Company detail not found');
        $record = OpeningBalanceVat::query()
            ->where([
                ['company_detail_id', '=', $com->id],
                ['start_month', '=', $params['start_month']],
                ['end_month', '=', $params['end_month']],
            ])->first();
        if (empty($record)) {
            $count_month = $params['start_month'] == $params['end_month'] ? 1 : $params['end_month'] - $params['start_month'];
            $money = 0;
            if ($count_month > 1) {
                # Search total money
                $entities = OpeningBalanceVat::query()
                    ->where([
                        ['company_detail_id', '=', $com->id],
                        ['count_month', '=', 1],
                        ['start_month', '>=', $params['start_month']],
                        ['end_month', '<=', $params['end_month']],
                    ])->get();
                foreach ($entities as $entity) {
                    $money += $entity->money;
                }
            }
            # Create new record
            $result = new OpeningBalanceVat();
            $result->count_month = $count_month;
            $result->start_month = $params['start_month'];
            $result->end_month = $params['end_month'];
            $result->money = $money;
            $result->meta = json_encode([
                'year' => $com->year,
            ]);
            if ($result->save()) return $result;
            throw new \Exception(message: "Cannot save DB");
        }
        return $record;
    }
}
