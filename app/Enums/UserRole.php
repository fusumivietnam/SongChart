<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Reviewer = 'reviewer';
    case Editor = 'editor';
    case ProviderManager = 'provider_manager';
    case SystemOperator = 'system_operator';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return str($this->value)->replace('_', ' ')->title()->toString();
    }
}
