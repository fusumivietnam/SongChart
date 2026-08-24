<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\Enums;

enum ProviderRequestFailureKind: string
{
    case Authentication = 'authentication';
    case Authorization = 'authorization';
    case RateLimited = 'rate-limited';
    case Timeout = 'timeout';
    case Transport = 'transport';
    case InvalidResponse = 'invalid-response';
    case NotFound = 'not-found';
    case Unsupported = 'unsupported';

    public function retryable(): bool
    {
        return in_array($this, [self::RateLimited, self::Timeout, self::Transport], true);
    }
}
