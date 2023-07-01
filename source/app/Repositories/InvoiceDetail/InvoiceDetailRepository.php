<?php

namespace App\Repositories\InvoiceDetail;

use App\Exceptions\Business\ActionFailException;
use App\Exceptions\DB\CannotSaveToDBException;
use App\Exceptions\DB\IdIsNotProvidedException;
use App\Helpers\Common\MetaInfo;
use App\Models\InvoiceDetail;
use App\Repositories\BaseRepository;
use App\Exceptions\DB\RecordIsNotFoundException as DBRecordIsNotFoundException;
use App\Models\Invoice;

use function Spatie\SslCertificate\starts_with;

class InvoiceDetailRepository extends BaseRepository implements IInvoiceDetailRepository
{
    /**
     * get corresponding model class name
     * @return string
     */
    function getRepositoryModelClass(): string
    {
        return InvoiceDetail::class;
    }

    /**
     * Update formula for inovice detail
     * @return bool
     */
    public function updateProgressByFormula(array $form, MetaInfo $meta = null): bool
    {
        foreach ($form['invoice_details'] as $item) {
            # 1.Check invoice detail
            $detail = $this->getSingleObject($item['invoice_detail_id'])->first();
            if (empty($detail)) throw new ActionFailException();

            # 2.Check invoice
            $invoice = Invoice::find($detail->invoice_id);
            if (empty($invoice)) throw new ActionFailException();
            if ($invoice->invoice_task_id != $form['invoice_task_id']) throw new ActionFailException();

            # 3.Update
            $detail->formula_path_id = $item['formula_path_id'];
            $detail->warehouse = $item['warehouse'];
            $detail->formula_commodity_id = $item['formula_commodity_id'] ?? null;
            $detail->formula_material_id = $item['formula_material_id'] ?? null;
            if (!$detail->save()) throw new ActionFailException();
        }
        return true;
    }
}
