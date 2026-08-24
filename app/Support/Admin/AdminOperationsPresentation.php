<?php

declare(strict_types=1);

namespace App\Support\Admin;

use BackedEnum;

final class AdminOperationsPresentation
{
    /** @return array{label:string,tone:string} */
    public function providerStatus(mixed $status, bool $enabled): array
    {
        $value = $this->value($status);

        if (! $enabled && $value !== 'retired') {
            return ['label' => 'Đang tạm ngừng', 'tone' => 'warning'];
        }

        return match ($value) {
            'active', 'approved' => ['label' => 'Hoạt động', 'tone' => 'success'],
            'sandbox' => ['label' => 'Đang thử nghiệm', 'tone' => 'info'],
            'retired' => ['label' => 'Đã ngừng sử dụng', 'tone' => 'secondary'],
            default => ['label' => 'Cần kiểm tra', 'tone' => 'warning'],
        };
    }

    /** @return array{label:string,tone:string} */
    public function importStatus(mixed $status): array
    {
        return match ($this->value($status)) {
            'queued' => ['label' => 'Đang chờ', 'tone' => 'info'],
            'running', 'retrying' => ['label' => 'Đang xử lý', 'tone' => 'warning'],
            'paused' => ['label' => 'Đang tạm dừng', 'tone' => 'warning'],
            'completed' => ['label' => 'Hoàn tất', 'tone' => 'success'],
            'completed_with_errors' => ['label' => 'Hoàn tất, có vấn đề', 'tone' => 'warning'],
            'failed' => ['label' => 'Thất bại', 'tone' => 'danger'],
            'cancelled', 'canceled' => ['label' => 'Đã dừng', 'tone' => 'secondary'],
            default => ['label' => 'Cần kiểm tra', 'tone' => 'warning'],
        };
    }

    public function providerAction(string $action): string
    {
        return match ($action) {
            'enable' => 'Tiếp tục sử dụng nguồn',
            'disable' => 'Tạm ngừng nguồn',
            'retire' => 'Ngừng sử dụng vĩnh viễn',
            default => str($action)->replace('_', ' ')->headline()->toString(),
        };
    }

    public function recoveryAction(string $action): string
    {
        return match ($action) {
            'retry' => 'Thử lại',
            'resume' => 'Tiếp tục từ điểm đã lưu',
            'cancel' => 'Dừng tiến trình',
            default => str($action)->replace('_', ' ')->headline()->toString(),
        };
    }

    public function operation(string $operation): string
    {
        return str($operation)->replace(['_', '-'], ' ')->headline()->toString();
    }

    private function value(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return is_string($value) ? $value : 'unknown';
    }
}
