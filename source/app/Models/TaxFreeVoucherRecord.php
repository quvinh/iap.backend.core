<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxFreeVoucherRecord extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // 'tax_free_voucher_id',
        'company_detail_id',
        // 'user_id',
        'count_month',
        'start_month',
        'end_month',
        'meta',
    ];

    /**
     * @return HasOne
     */
    // public function user(): HasOne
    // {
    //     return $this->hasOne(User::class, 'id', 'user_id');
    // }
}
