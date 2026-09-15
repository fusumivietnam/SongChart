<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_search_daily_aggregates', function (Blueprint $table): void {
            $table->date('day');
            $table->char('query_key', 64);
            $table->string('normalized_query', 100)->nullable();
            $table->string('privacy_bucket', 32)->default('allowed');
            $table->string('entity_type_filter', 32)->default('all');
            $table->string('sort_order', 32)->default('relevance');
            $table->unsignedBigInteger('search_count')->default(0);
            $table->unsignedBigInteger('zero_result_count')->default(0);
            $table->unsignedBigInteger('result_count_sum')->default(0);
            $table->timestamps();

            $table->primary(['day', 'query_key', 'entity_type_filter', 'sort_order']);
            $table->index(['day', 'zero_result_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_search_daily_aggregates');
    }
};
