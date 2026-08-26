<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProviderImportPreviewRequest;
use App\Support\Providers\Ingestion\ProviderImportPlanBuilder;
use App\Support\Providers\Ingestion\ProviderImportPreviewBuilder;
use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

final class ProviderImportPreviewController extends Controller
{
    public function index(): View
    {
        return view('admin.operations.import-preview', [
            'title' => 'Xem trước dữ liệu nhập',
            'description' => 'Kiểm tra cách SongChart hiểu dữ liệu từ nguồn trước khi tạo bất kỳ thay đổi nào.',
            'preview' => null,
            'plan' => null,
        ]);
    }

    public function preview(
        ProviderImportPreviewRequest $request,
        ProviderImportPreviewBuilder $previewBuilder,
        ProviderImportPlanBuilder $planBuilder,
    ): View {
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

        return view('admin.operations.import-plan', [
            'title' => 'Kế hoạch nhập dữ liệu',
            'description' => 'Xem chính xác SongChart sẽ tạo tác vụ nào trước khi xác nhận chạy pipeline có kiểm soát.',
            'preview' => $preview,
            'plan' => $plan,
            'submitted' => [
                'provider_slug' => (string) $validated['provider_slug'],
                'entity_type' => (string) $validated['entity_type'],
                'external_id' => (string) $validated['external_id'],
                'payload_json' => (string) $validated['payload_json'],
            ],
        ]);
    }
}
