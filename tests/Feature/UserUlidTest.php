<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user model automatically generates a ulid primary key', function (): void {
    $user = User::factory()->create();

    expect($user->getKey())
        ->toBeString()
        ->toHaveLength(26);

    $this->assertDatabaseHas('users', [
        'id' => $user->getKey(),
        'email' => $user->email,
    ]);
});
