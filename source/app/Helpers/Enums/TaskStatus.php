<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class TaskStatus extends Enum
{
    const NOT_YET_STARTED = 'not_yet_started';
    const IN_PROGRESS = 'in_progress';
    const COMPLETED = 'completed';
}
