<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

/**
 * Processing status from General Department of Taxation(Tong cuc thue)
 */
class ProcessingStatusEnum extends Enum
{
    const TCT_DA_NHAN = 0;
    const DANG_TIEN_HANH_KIEM_TRA_DIEU_KIEN_CAP_MA = 1;
    const CQT_TU_CHOI_HOA_DON_THEO_TUNG_LAN_PHAT_SINH = 2;
    const HOA_DON_DU_DIEU_KIEN_CAP_MA = 3;
    const HOA_DON_KHONG_DU_DIEU_KIEN_CAP_MA = 4;
    const DA_CAP_MA_HOA_DON = 5;
    const TCT_DA_NHAN_KHONG_MA = 6;
    const DA_KIEM_TRA_DINH_KY_HDDT_KHONG_CO_MA = 7;
    const TCT_DA_NHAN_HOA_DON_CO_MA_TU_MAY_TINH_TIEN = 8;
}
