<?php

declare(strict_types=1);

namespace App\Services\HumanVerification;

use App\Contracts\HumanVerification\HumanVerification;

final class NullHumanVerification implements HumanVerification
{
    public function verify(string $token, string $ipAddress, string $action): bool
    {
        return app()->environment('local', 'testing');
    }
}
