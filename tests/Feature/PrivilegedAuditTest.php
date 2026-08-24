<?php

declare(strict_types=1);

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\User;
use App\Support\Admin\PrivilegedUserAdministration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

it('records explicit privileged activity with ULID actor and subject', function (): void {
    $actor = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $provider = Provider::query()->create([
        'slug' => 'audit-fixture',
        'name' => 'Audit Fixture',
        'category' => 'metadata',
        'status' => 'approved',
        'is_enabled' => true,
    ]);

    app(PrivilegedAuditLogger::class)->record(
        event: 'provider.disable',
        description: 'Provider disabled.',
        subject: $provider,
        actor: $actor,
        before: ['is_enabled' => true],
        after: ['is_enabled' => false],
        rationale: 'Provider contract is temporarily unavailable.',
    );

    $activity = Activity::query()->where('event', 'provider.disable')->sole();

    expect($activity->log_name)->toBe('privileged')
        ->and((string) $activity->causer_id)->toBe((string) $actor->getKey())
        ->and((string) $activity->subject_id)->toBe((string) $provider->getKey())
        ->and($activity->getProperty('rationale'))->toBe('Provider contract is temporarily unavailable.');
});

it('protects the last active super administrator from deactivation', function (): void {
    $actor = User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    expect(fn () => app(PrivilegedUserAdministration::class)->setActive(
        $actor,
        false,
        $actor,
        'Routine privileged account lifecycle verification.',
    ))->toThrow(LogicException::class);
});

it('allows a system operator to change activation but not user roles through capability gates', function (): void {
    $operator = User::factory()->create([
        'role' => UserRole::SystemOperator,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $target = User::factory()->create([
        'role' => UserRole::User,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    app(PrivilegedUserAdministration::class)->setActive(
        $target,
        false,
        $operator,
        'Operational account suspension for authorization verification.',
    );

    expect($target->fresh()->is_active)->toBeFalse()
        ->and(fn () => app(PrivilegedUserAdministration::class)->setRole(
            $target,
            UserRole::Reviewer,
            $operator,
            'Role escalation must remain restricted to super administrators.',
        ))->toThrow(LogicException::class);
});
