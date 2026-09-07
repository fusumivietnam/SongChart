<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_relationships', function (Blueprint $table): void {
            $table->index(
                ['subject_type', 'subject_id', 'verification_state', 'relationship_type'],
                'entity_relationships_subject_public_read_idx',
            );
            $table->index(
                ['object_type', 'object_id', 'verification_state', 'relationship_type'],
                'entity_relationships_object_public_read_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('entity_relationships', function (Blueprint $table): void {
            $table->dropIndex('entity_relationships_subject_public_read_idx');
            $table->dropIndex('entity_relationships_object_public_read_idx');
        });
    }
};
