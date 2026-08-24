<?php

declare(strict_types=1);

use App\Support\Engineering\PhpSemanticFingerprint;

it('ignores formatter comments whitespace and line ending differences', function (): void {
    $a = <<<'PHP'
<?php

Schema::table('activity_log', function (Blueprint $table): void {
    $table->json('attribute_changes')->nullable();
});
PHP;

    $b = "<?php\r\n// formatter-only comment\r\nSchema::table( 'activity_log', function(Blueprint \$table): void {\r\n\t\$table->json('attribute_changes')->nullable();\r\n});\r\n";

    expect(PhpSemanticFingerprint::fromSource($a))
        ->toBe(PhpSemanticFingerprint::fromSource($b));
});

it('changes when migration semantics change', function (): void {
    $before = <<<'PHP'
<?php
$table->json('attribute_changes')->nullable();
PHP;

    $after = <<<'PHP'
<?php
$table->text('attribute_changes')->nullable();
PHP;

    expect(PhpSemanticFingerprint::fromSource($before) !== PhpSemanticFingerprint::fromSource($after))
        ->toBeTrue();
});
