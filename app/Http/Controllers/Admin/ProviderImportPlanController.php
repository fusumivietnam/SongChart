<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Providers\Ingestion\StartGovernedProviderImportPlan;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProviderImportPlanExecuteRequest;
use App\Support\Providers\Ingestion\ProviderImportPlanBuilder;
use App\Support\Providers\Ingestion\ProviderImportPreviewBuilder;
use DateTimeImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class ProviderImportPlanController extends Controller
{
    public function execute(
        ProviderImportPlanExecuteRequest $request,
        ProviderImportPreviewBuilder $previewBuilder,
        ProviderImportPlanBuilder $planBuilder,
        StartGovernedProviderImportPlan $starter,
    ): RedirectResponse {
        $validated = $request->validated();
        $decoded = json_decode((string) $validated['payload_json'], true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages([
                'payload_json' => 'Dữ liệu JSON phải là một object của provider, không phải danh sách hoặc giá trị đơn.',
            ]);
        }

        /** @var array<string, mixed> $decoded */
        $payload = new ProviderPayload(
            providerSlug: (string) $validated['provider_slug'],
            entityType: EntityType::from((string) $validated['entity_type']),
            externalId: (string) $validated['external_id'],
            data: $decoded,
            receivedAt: new DateTimeImmutable('now'),
        );

        $preview = $previewBuilder->build($payload);
        $plan = $planBuilder->build($payload, $preview);

        if (! hash_equals($plan->fingerprint, (string) $validated['plan_fingerprint'])) {
            throw ValidationException::withMessages([
                'plan_fingerprint' => 'Kế hoạch nhập đã thay đổi. Vui lòng xem trước lại dữ liệu trước khi tạo tác vụ nhập.',
            ]);
        }

        try {
            $run = $starter->execute($plan);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'provider_slug' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('admin.imports.show', ['run' => $run->getKey()])
            ->with('status', 'Đã tạo tác vụ nhập có kiểm soát. SongChart sẽ tiếp tục qua pipeline xác minh và duyệt dữ liệu chuẩn.');
    }
}
