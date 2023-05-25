<?php


namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;
/**
 * @property string $name
 */
final class ErrorCodes extends Enum
{
    # 1xxx DB
    const ERR_RECORD_NOT_FOUND =  1001;
    const ERR_DB_HAS_CHANGED = 1002;
    const ERR_RECORD_HAS_RELATION = 1003;

    const ERR_CANNOT_SAVE_TO_DB = 1004;
    const ERR_CANNOT_DELETE_RECORD = 1005;
    const ERR_CANNOT_UPDATE_RECORD = 1006;
    const ERR_CANNOT_CREATE_RELATED_DATA = 1007;
    const ERR_CANNOT_UPDATE_RELATED_RECORDS = 1008;
    const ERR_CANNOT_DELETE_RELATED_RECORDS = 1009;

    # 2xxx: data type
    const ERR_SUBMITTED_DATA_IS_INVALID = 2000;
    const ERR_INVALID_DATETIME_INPUT_DATA = 2001;
    const ERR_DATE_RANGE_INPUT_DATA = 2002;
    const ERR_PAGINATION_INPUT_DATA = 2003;
    const ERR_SORT_INPUT_DATA  = 2005;
    const ERR_ID_IS_NOT_PROVIDED = 2006;

    const ERR_MODEL_CLASS_NOT_EXISTS = 2007;
    const ERR_INVALID_SETTING = 2008;

    # 3xxx Action response
    const ERR_ACTION_FAIL = 3001;
    const ERROR_CANNOT_UPLOAD_IMAGE_FILE = 3002;
    const ERROR_CANNOT_EXPORT_PDF = 3003;
    const ERR_FILE_NOT_FOUND = 3004;
    # 4xxx Authorization
    const ERR_AUTHORIZATION_HEADER_NOT_FOUND = 4001;
    const ERR_INVALID_AUTHORIZATION = 4002;
    const ERR_SERVICE_UNAVAILABLE = 4003;
    const ERR_NO_PERMISSION = 4004;
    const ERR_INVALID_CREDENTIALS = 4005;

    # 5xxx Others
    const ERR_INVALID_URL = 5001;

    # HTTP CODES
    const ERR_INTERNAL_SERVER_ERROR = 503;
}
