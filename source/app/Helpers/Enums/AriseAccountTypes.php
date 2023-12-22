<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class AriseAccountTypes extends Enum
{
    const NONE = 0; # Default: mua vao theo cong thuc doanh thu
    const IS_TRACKING = 1; # Mua vao can phan bo (-> danh sach theo doi TSCD, CP tra truoc)
}
