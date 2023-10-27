<?php

namespace App\Repositories\PdfTableKey;

use App\Helpers\Common\MetaInfo;
use App\Models\PdfTableKey;
use App\Repositories\IRepository;

interface IPdfTableKeyRepository extends IRepository
{
    public function findByKey(string $key): PdfTableKey | null;
    public function getKey(): PdfTableKey | null;
}
