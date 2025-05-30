<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDocument extends BaseModel
{
    use HasFactory, SoftDeletes;

    const DRIVE_LOCAL = 'local';
    const DRIVE_GOOGLE = 'drive';

    protected $fillable = [
        'company_id',
        'name',
        'year',
        'file',
        'is_contract',
        'signature_date',
        'expiry_date',
        'meta',
        'drive',
    ];
}
