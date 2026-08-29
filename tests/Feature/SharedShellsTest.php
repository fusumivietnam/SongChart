<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('renders the public frontend shell', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Điều hướng chính', false)
        ->assertSee('Điều hướng di động', false)
        ->assertSee('Tìm kiếm toàn cục', false)
        ->assertSee('aria-current="page"', false);

    expect(substr_count($response->getContent(), 'aria-current="page"'))->toBe(2);
});

it('renders the admin shell for an admin user', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->withConfirmedTwoFactorAuthentication()->create([
        'role' => 'super_admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('SongChart Admin')->assertSee('Cần xử lý');
});
