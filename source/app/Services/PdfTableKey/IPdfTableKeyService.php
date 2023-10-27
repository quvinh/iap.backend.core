<?php

namespace App\Services\PdfTableKey;

use App\Models\PdfTableKey;
use App\Services\IService;

interface IPdfTableKeyService extends IService
{
    public function findByKey(string $key): PdfTableKey|null;
    public function getKey(): PdfTableKey|null;
}
