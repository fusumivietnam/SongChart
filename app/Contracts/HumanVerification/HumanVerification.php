<?php

declare(strict_types=1);

namespace App\Contracts\HumanVerification;

interface HumanVerification
{
    public function verify(string $token, string $ipAddress, string $action): bool;
}
