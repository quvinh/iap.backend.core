<?php

namespace App\Repositories\InvoiceTask;

use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceTask;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use function Spatie\SslCertificate\starts_with;
use App\Helpers\Enums\TaskStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceTaskRepository extends BaseRepository implements IInvoiceTaskRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return InvoiceTask::class;
    }

    /**
     * get money of month
     * @param $company_id
     * @param $year
     * @param $opening_balance_value
     */
    public function getMoneyOfMonths(int $company_id, int $year): array
    {
        $record = (new InvoiceTask())->query()->where([
            ['company_id', '=', $company_id],
            ['month_of_year', 'like', "%$year"],
            ['task_progress', '<>', TaskStatus::NOT_YET_STARTED]
        ])->orderBy('month_of_year')
            ->get()->toArray();

        $result = array();
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $arr = array_filter($record, function ($value) use ($month, $year) {
                return $value['month_of_year'] == "$month/$year";
            });
            if (empty($arr)) {
                $result[] = [
                    'company_id' => $company_id,
                    'month_of_year' => "$month/$year",
                    'opening_balance_value' => 0,
                    'total_money_sold' => 0,
                    'total_money_purchase' => 0,
                ];
            } else {
                # TODO: Add field meta -> get formulas's money
                $result[] = array_merge(array_values($arr)[0], [
                    'meta' => [],
                ]);
            }
        }

        return $result;
    }

    /**
     * Get task not process in this month
     */
    public function getTaskNotProcess(): Collection
    {
        $tasks = InvoiceTask::query()->where([
            ['task_progress', '=', TaskStatus::NOT_YET_STARTED],
            ['month_of_year', '=', date('m/Y')]
        ])->get();
        return $tasks;
    }

    /**
     * Get monthly task
     */
    public function monthlyTask(): array
    {
        # Get current year
        $year = date('Y');
        $record = (new InvoiceTask())->query()->where([
            ['month_of_year', 'like', "%$year"],
            ['task_progress', '<>', TaskStatus::NOT_YET_STARTED]
        ])->select(DB::raw('COUNT(id) as amount'),'month_of_year')
            ->groupBy('month_of_year')
            ->get()->toArray();
        $result = array();
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $arr = array_filter($record, function ($value) use ($month, $year) {
                return $value['month_of_year'] == "$month/$year";
            });
            if (empty($arr)) {
                $result[] = [
                    'month_of_year' => "$month/$year",
                    'amount' => 0,
                ];
            } else {
                $result[] = array_values($arr)[0];
            }
        }

        return $result;
    }
}
