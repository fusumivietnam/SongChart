<?php

declare(strict_types=1);

use App\Support\Engineering\LaravelMigrationColumnExtractor;

it('expands Laravel Blueprint helpers into their semantic columns', function (): void {
    $source = <<<'PHP'
$table->id();
$table->string('log_name')->nullable()->index();
$table->text('description');
$table->nullableMorphs('subject', 'subject');
$table->string('event')->nullable();
$table->nullableMorphs('causer', 'causer');
$table->json('attribute_changes')->nullable();
$table->json('properties')->nullable();
$table->timestamps();
PHP;

    $columns = (new LaravelMigrationColumnExtractor)->extract($source);

    expect($columns)->toContain(
        'id',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'event',
        'causer_type',
        'causer_id',
        'attribute_changes',
        'properties',
        'created_at',
        'updated_at',
    )->and(in_array('batch_uuid', $columns, true))->toBeFalse();
});
