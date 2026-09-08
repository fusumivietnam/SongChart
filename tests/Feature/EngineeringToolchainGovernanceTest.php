<?php

declare(strict_types=1);

it('registers the SongChart doctor command', function (): void {
    $this->artisan('list')
        ->expectsOutputToContain('songchart:doctor')
        ->assertSuccessful();
});

it('exposes canonical polymorphic integrity through the read-only doctor surface', function (): void {
    $this->artisan('songchart:doctor')
        ->expectsOutputToContain('Canonical polymorphic references')
        ->assertSuccessful();
});
