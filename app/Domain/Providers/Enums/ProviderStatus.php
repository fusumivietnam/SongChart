<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderStatus: string
{
    case Research = 'research';
    case Blocked = 'blocked';
    case Sandbox = 'sandbox';
    case Approved = 'approved';
    case Degraded = 'degraded';
    case Suspended = 'suspended';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Research => 'Đang nghiên cứu',
            self::Blocked => 'Chưa thể triển khai',
            self::Sandbox => 'Đang thử nghiệm',
            self::Approved => 'Đã phê duyệt',
            self::Degraded => 'Hoạt động không ổn định',
            self::Suspended => 'Tạm ngừng',
            self::Retired => 'Đã ngừng sử dụng',
        };
    }
}
