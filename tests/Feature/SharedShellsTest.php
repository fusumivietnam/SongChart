<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('renders the public frontend shell', function (): void {
    /** @var TestCase $this */
    $this->get('/')->assertOk()->assertSee('Điều hướng chính', false)->assertSee('Tìm kiếm toàn cục', false);
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
