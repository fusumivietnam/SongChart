<?php

declare(strict_types=1);

it('keeps data fusion on existing provenance tables instead of creating provider-specific canonical stores', function (): void {
    $root = dirname(__DIR__, 2);
    $config = require $root.'/config/data-fusion.php';
    $schema = (string) file_get_contents($root.'/docs/project/domain/domain-contracts.json');

    expect($config)->toHaveKeys(['default', 'sources'])
        ->and($config['sources'])->toHaveKeys(['provider:musicbrainz', 'provider:youtube', 'songchart:editorial'])
        ->and($schema)->toContain('"metadata_assertions_table": "metadata_assertions"')
        ->and($schema)->toContain('"fusion"');
});
