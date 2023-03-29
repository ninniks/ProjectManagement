<?php

namespace App\Enum;

enum CustomOrderByEnum:string
{
    case ALPHA_DESC = 'alpha_desc';
    case ALPHA_ASC = 'alpha_asc';

    case CREATE = 'create';

    case UPDATE = 'update';

}
