<?php

declare(strict_types=1);

namespace App\Support\Providers\Ingestion;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPlan;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPreview;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use RuntimeException;

final readonly class ProviderImportSelectionBuilder
{
    public function __construct(
        private ProviderCatalogAdapterRegistry $adapters,
        private ProviderRuntimeConfiguration $runtimeConfiguration,
        private ProviderImportPreviewBuilder $previewBuilder,
        private ProviderImportPlanBuilder $planBuilder,
    ) {}

    /** @return array{payload:ProviderPayload,preview:ProviderImportPreview,plan:ProviderImportPlan} */
    public function build(string $providerSlug, EntityType $entityType, string $externalId): array
    {
        $this->runtimeConfiguration->apply($providerSlug);
        $adapter = $this->adapters->for($providerSlug);
        if ($adapter === null) {
            throw new RuntimeException('Nguồn dữ liệu này chưa có bộ kết nối nhập dữ liệu được hỗ trợ.');
        }

        $page = $adapter->fetchPage(new ProviderImportContext(
            runId: 'admin-import-selection-preview',
            entityType: $entityType,
            externalId: trim($externalId),
            pageSize: 1,
        ));
        $payload = $page->items[0] ?? null;
        if (! $payload instanceof ProviderPayload) {
            throw new RuntimeException('Không thể tải dữ liệu chi tiết cho kết quả đã chọn.');
        }

        $preview = $this->previewBuilder->build($payload);
        $plan = $this->planBuilder->build($payload, $preview);

        return compact('payload', 'preview', 'plan');
    }
}
