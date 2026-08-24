<?php

declare(strict_types=1);

it('registers the SongChart doctor command', function (): void {
    $this->artisan('list')
        ->expectsOutputToContain('songchart:doctor')
        ->assertSuccessful();
});
