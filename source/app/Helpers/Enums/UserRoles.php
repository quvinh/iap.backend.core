<?php

namespace App\Helpers\Enums;

use BenSampo\Enum\Enum;

class UserRoles extends Enum
{
    const ANONYMOUS = 'anonymous';
    
    const ADMINISTRATOR = 1;
    const MODERATOR = 2;
    const MEMBER = 3;
}
