<?php

declare(strict_types=1);

namespace App\Modules\Patients\Domain\Enums;

enum BiologicalSex: string
{
    case Male = 'male';
    case Female = 'female';
}
