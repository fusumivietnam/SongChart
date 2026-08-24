<?php

declare(strict_types=1);

it('keeps model-specific AI files thin and delegated to one protocol authority', function (): void {
    $root = dirname(__DIR__, 2);

    foreach (['AGENTS.md', 'CLAUDE.md', 'GEMINI.md'] as $relative) {
        $source = (string) file_get_contents($root.'/'.$relative);
        $lines = preg_split('/\R/', trim($source)) ?: [];

        expect(count($lines))->toBeLessThanOrEqual(30)
            ->and($source)->toContain(
                'PROJECT_AUTHORITY.md',
                'AI_DEVELOPMENT_PROTOCOL.md',
                'composer stage:verify',
                'composer canonical:verify',
            );
    }
});
