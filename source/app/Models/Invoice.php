<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $sum_money_no_vat
 * @property float $sum_money_vat
 * @property float $sum_money_discount
 * @property float $sum_money
 */
class Invoice extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'invoice_task_id',
        'type',
        'date',
        'invoice_symbol',
        'invoice_number_form',
        'invoice_number',
        'property',
        'note',
        'partner_name',
        'partner_tax_code',
        'partner_address',
        'currency',
        'currency_price',
        'sum_money_no_vat',
        'sum_money_vat',
        'sum_money_discount',
        'sum_money',
        'rounding',
        'payment_method',
        'verification_code',
        'json',
        'status',
    ];

    public function plusMoneyInvoice(float $total_money_no_vat, int $vat, float $discount = 0): void
    {
        $vat_money = $this->getVatMoneyInvoiceDetail($total_money_no_vat, $vat);
        $this->sum_money_no_vat += $total_money_no_vat;
        $this->sum_money_vat += $vat_money;
        $this->sum_money_discount += $discount;
        $this->sum_money += ($total_money_no_vat + $vat_money);
    }

    public function getVatMoneyInvoiceDetail(float $total_money_no_vat, int $_vat): float
    {
        try {
            $vat = $_vat;
            if ($_vat < 0) $vat = 0; // Exception vat=-1; vat=-2
            $vat_money = $total_money_no_vat * ($vat / 100);
            return round($vat_money, 2);
        } catch (\Exception) {
            return 0;
        }
    }

    /**
     * @return HasMany
     */
    public function invoice_details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'id');
    }
}
