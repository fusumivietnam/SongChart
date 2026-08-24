<?php

declare(strict_types=1);

use Spatie\Activitylog\Actions\CleanActivityLogAction;
use Spatie\Activitylog\Actions\LogActivityAction;
use Spatie\Activitylog\Models\Activity;

return [
    'enabled' => (bool) env('ACTIVITYLOG_ENABLED', true),
    'clean_after_days' => (int) env('ACTIVITYLOG_CLEAN_AFTER_DAYS', 365),
    'default_log_name' => 'privileged',
    'default_auth_driver' => null,
    'include_soft_deleted_subjects' => false,
    'activity_model' => Activity::class,
    'default_except_attributes' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],
    'actions' => [
        'log_activity' => LogActivityAction::class,
        'clean_log' => CleanActivityLogAction::class,
    ],
];
