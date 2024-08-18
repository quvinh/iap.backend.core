<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobHistory extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;
    public const STATUS_PROCESSING = 1;
    public const STATUS_DONE = 2;
    public const STATUS_ERROR = 3;

    protected $fillable = [
        'company_id',
        'job_id',
        'file_name',
        'note',
        'path',
        'status',
    ];

    /**
     * @return HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }
}
