<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Enums;

enum CanonicalAdmissionStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Rejected = 'rejected';
}
