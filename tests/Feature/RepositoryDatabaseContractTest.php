<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('materializes the package adapted activity log schema on PostgreSQL', function (): void {
    expect(Schema::hasColumns('activity_log', [
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
        'batch_uuid',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    $subjectLength = DB::table('information_schema.columns')
        ->whereRaw('table_schema = current_schema()')
        ->where('table_name', 'activity_log')
        ->where('column_name', 'subject_id')
        ->value('character_maximum_length');

    $causerLength = DB::table('information_schema.columns')
        ->whereRaw('table_schema = current_schema()')
        ->where('table_name', 'activity_log')
        ->where('column_name', 'causer_id')
        ->value('character_maximum_length');

    $attributeChangesNullable = DB::table('information_schema.columns')
        ->whereRaw('table_schema = current_schema()')
        ->where('table_name', 'activity_log')
        ->where('column_name', 'attribute_changes')
        ->value('is_nullable');

    $propertiesNullable = DB::table('information_schema.columns')
        ->whereRaw('table_schema = current_schema()')
        ->where('table_name', 'activity_log')
        ->where('column_name', 'properties')
        ->value('is_nullable');

    expect((int) $subjectLength)->toBe(26)
        ->and((int) $causerLength)->toBe(26)
        ->and($attributeChangesNullable)->toBe('YES')
        ->and($propertiesNullable)->toBe('YES');
});
