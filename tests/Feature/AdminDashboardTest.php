<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('protects the admin dashboard with authentication', function (): void {
    /** @var TestCase $this */
    $this->get('/admin')->assertRedirect('/login');
});

it('rejects a verified non admin user', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('rejects an inactive admin through the shared gate', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->create([
        'role' => 'super_admin',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors();
});

it('renders the attention-first dashboard for an admin', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->withConfirmedTwoFactorAuthentication()->create(['role' => 'super_admin']);

    $this->actingAs($admin)->get('/admin')
        ->assertOk()
        ->assertSee('data-admin-dashboard="attention-first"', false)
        ->assertSee('data-dashboard-section="attention-center"', false)
        ->assertSee('data-dashboard-section="metrics"', false)
        ->assertSee('data-admin-technical-details', false)
        ->assertSee('data-development-intelligence="stale"', false)
        ->assertSee('Chưa có snapshot Development Intelligence');
});

it('reads a completed development intelligence snapshot without rebuilding source', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->withConfirmedTwoFactorAuthentication()->create(['role' => 'super_admin']);
    $directory = storage_path('project-intelligence/test-snapshot-sha');

    File::ensureDirectoryExists($directory);
    file_put_contents($directory.'/architecture-graph.json', json_encode([
        'snapshot' => [
            'head_sha' => '1234567890abcdef1234567890abcdef12345678',
            'branch' => 'main',
            'status' => 'fresh',
        ],
        'metrics' => [
            'class_nodes' => 321,
            'route_nodes' => 45,
            'edges' => 678,
        ],
    ], JSON_THROW_ON_ERROR));
    Cache::forget('project-intelligence:latest');

    try {
        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee('data-development-intelligence="fresh"', false)
            ->assertSee('12345678')
            ->assertSee('321')
            ->assertSee('45')
            ->assertSee('678');
    } finally {
        Cache::forget('project-intelligence:latest');
        File::deleteDirectory($directory);
    }
});

it('keeps the admin controller on the shared controller foundation', function (): void {
    $parent = (new ReflectionClass(DashboardController::class))->getParentClass();

    expect($parent->getName())->toBe(Controller::class);
});

it('does not hard code fake catalog totals in the dashboard view', function (): void {
    $view = file_get_contents(resource_path('views/admin/dashboard.blade.php'));

    expect(str_contains((string) $view, "'value' => '0'"))->toBeFalse()
        ->and(str_contains((string) $view, 'lượt nghe'))->toBeFalse()
        ->and(str_contains((string) $view, 'popularity'))->toBeFalse();
});

it('allows admin readers to inspect extensions but not mutate them', function (): void {
    /** @var TestCase $this */
    $reviewer = User::factory()->withConfirmedTwoFactorAuthentication()->create(['role' => 'reviewer']);

    $this->actingAs($reviewer)->get('/admin/extensions')->assertOk();
    $this->actingAs($reviewer)->post('/admin/extensions/install', [])->assertForbidden();
});

it('allows system operators to manage extensions through the shared gate', function (): void {
    $operator = User::factory()->create(['role' => 'system_operator']);

    expect($operator->can('manage-extensions'))->toBeTrue();
});

it('denies admin abilities when role or activity attributes are not hydrated', function (): void {
    $created = User::factory()->create(['role' => 'super_admin']);
    $partial = User::query()->select(['id', 'email'])->findOrFail($created->id);

    expect(Gate::forUser($partial)->allows('access-admin'))->toBeFalse()
        ->and(Gate::forUser($partial)->allows('manage-extensions'))->toBeFalse();
});
