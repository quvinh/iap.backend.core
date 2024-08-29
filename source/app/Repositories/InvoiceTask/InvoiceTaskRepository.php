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
use App\Models\Invoice;
use App\Models\InvoiceMedia;
use Carbon\Carbon;
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

        $cRecord = $record;
        $firstMonthOfTheYear = array_shift($cRecord);
        $initMeta = (object) array();
        $mutateMeta = (object) array();
        $dataInYear = (object) array();
        if (!empty($firstMonthOfTheYear['meta'])) {
            $arrMetaFirstMonth = (array) json_decode($firstMonthOfTheYear['meta']);
            foreach ($arrMetaFirstMonth as $row) {
                $field = "f_{$row->formula_id}";
                # Ending balance value: Cuoi ky = Mua vao + Ton - Gia von
                $cost_price_sold = $row->sold_money * $row->sum_avg * 0.01;
                $end = $row->purchase_money + $row->opening_balance_value - $cost_price_sold;
                $r = (object) [
                    'month' => $firstMonthOfTheYear['month_of_year'],
                    'start' => $row->opening_balance_value,
                    'end' => $end,
                    'cost_price_sold' => $cost_price_sold,
                ];
                $mutateMeta->{$field} = $r;
                $initMeta->{$field} = $r;

                # Data in year
                $dataInYear->{$field} = (object) [
                    'formula_id' => $row->formula_id,
                    'formula_name' => $row->formula_name,
                    'year' => $year,
                    'start' => $row->opening_balance_value,
                    'purchase' => $row->purchase_money,
                    'sold' => $row->sold_money,
                    'cost_price_sold' => 0,
                    'percent_avg' => 0,
                    'end' => $end,
                ];
            }
        }
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
                # TODO: Add field meta -> get formulas's money (not finished yet)
                $entity = array_values($arr)[0];
                $meta = json_decode($entity['meta'] ?? "[]");
                if ($entity['id'] != $firstMonthOfTheYear['id']) {
                    $newMeta = (object) array();
                    foreach ($meta as $row) {
                        $field = "f_{$row->formula_id}";
                        # Get opening balance of last month
                        $openingBalanceValue = $mutateMeta->{$field}->end ?? 0;
                        $cost_price_sold = $row->sold_money * $row->sum_avg * 0.01;
                        $end_money = $row->purchase_money + $openingBalanceValue - $cost_price_sold;
                        $newMeta->{$field} = (object) [
                            'month' => $entity['month_of_year'],
                            'start' => $openingBalanceValue,
                            'end' => $end_money,
                            'cost_price_sold' => $cost_price_sold,
                        ];
                        # Update value
                        $mutateMeta->{$field} = $newMeta->{$field};

                        if (!empty($dataInYear->{$field})) {
                            $dataInYear->{$field}->purchase += $row->purchase_money;
                            $dataInYear->{$field}->sold += $row->sold_money;
                            $dataInYear->{$field}->cost_price_sold += $row->sold_money * $row->sum_avg * 0.01;
                            $dataInYear->{$field}->percent_avg = $row->sum_avg;
                            $dataInYear->{$field}->end = $end_money;
                        }
                    }

                    # Load meta again
                    if (!empty($arrMetaFirstMonth)) {
                        foreach ($arrMetaFirstMonth as $row) {
                            $field = "f_{$row->formula_id}";
                            if (empty($newMeta->{$field})) {
                                $newMeta->{$field} = (object) [
                                    'month' => $entity['month_of_year'],
                                    'start' => $mutateMeta->{$field}->end ?? 0,
                                    'end' => $mutateMeta->{$field}->end ?? 0,
                                    'cost_price_sold' => $mutateMeta->{$field}->cost_price_sold ?? 0,
                                ];
                            }
                        }
                    }
                    $result[] = array_merge($entity, [
                        'meta' => $meta,
                        'property' => $newMeta,
                    ]);
                } else {
                    $result[] = array_merge($entity, [
                        'meta' => $meta,
                        'property' => $initMeta,
                    ]);
                }
            }
        }

        return [
            'monthly' => $result,
            'year' => $dataInYear,
        ];
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
        $record = InvoiceTask::query()->where('month_of_year', 'like', "%$year")->select(
            DB::raw('COUNT(id) as total_tasks'),
            DB::raw('SUM(CASE WHEN task_progress = "completed" THEN 1 ELSE 0 END) as completed_tasks'),
            DB::raw('SUM(CASE WHEN task_progress = "in_progress" THEN 1 ELSE 0 END) as in_progress_tasks'),
            'month_of_year'
        )
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
                    'total_tasks' => '0',
                    'completed_tasks' => '0',
                    'in_progress_tasks' => '0',
                ];
            } else {
                $result[] = array_values($arr)[0];
            }
        }

        return $result;
    }

    /**
     * Force delete inovices with task
     */
    public function forceDeleteInvoiceWithTask(int $task_id, string $invoice_type): bool
    {
        Invoice::query()->where([
            ['invoice_task_id', '=', $task_id],
            ['type', '=', $invoice_type],
        ])->forceDelete();
        return true;
    }

    /**
     * Get monthly invoice
     */
    public function monthlyInvoice(): array
    {
        $year = date('Y');
        $record = Invoice::query()->select(
            DB::raw('DATE_FORMAT(date, "%m/%Y") as month'),
            DB::raw('SUM(CASE WHEN type = "purchase" THEN 1 ELSE 0 END) as total_purchase'),
            DB::raw('SUM(CASE WHEN type = "sold" THEN 1 ELSE 0 END) as total_sold')
        )
            ->where('locked', 0)
            ->whereYear('date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->orderBy(DB::raw('MONTH(date)'))
            ->get()->toArray();

        $result = array();
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $arr = array_filter($record, function ($value) use ($month, $year) {
                if (isset($value['month'])) {
                    return $value['month'] == "$month/$year";
                }
                return false;
            });
            if (empty($arr)) {
                $result[] = [
                    'month' => "$month/$year",
                    'total_purchase' => '0',
                    'total_sold' => '0',
                ];
            } else {
                $result[] = array_values($arr)[0];
            }
        }

        return $result;
    }

    public function invoiceMediaNotCompleted(): int
    {
        return InvoiceMedia::query()->where('status', 0)->count();
    }
}
