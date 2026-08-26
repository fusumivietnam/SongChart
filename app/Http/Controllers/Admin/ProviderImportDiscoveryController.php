<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Catalog\Enums\EntityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProviderImportSearchRequest;
use App\Http\Requests\Admin\ProviderImportSelectionRequest;
use App\Support\Providers\Catalog\MusicBrainzArtistWorkbench;
use App\Support\Providers\Catalog\MusicBrainzRecordingWorkbench;
use App\Support\Providers\Ingestion\ProviderImportSelectionBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class ProviderImportDiscoveryController extends Controller
{
    public function search(
        ProviderImportSearchRequest $request,
        MusicBrainzArtistWorkbench $artists,
        MusicBrainzRecordingWorkbench $recordings,
    ): View {
        $validated = $request->validated();
        $intent = (string) $validated['intent'];
        $query = trim((string) $validated['query']);

        if ($intent === 'lyrics') {
            return view('admin.operations.import-preview', [
                'title' => 'Nhập dữ liệu',
                'description' => 'Tìm thông tin theo cách bạn đang biết, SongChart sẽ xử lý phần định danh kỹ thuật.',
                'intent' => $intent,
                'query' => $query,
                'results' => [],
                'searchNotice' => 'Hiện chưa có nguồn tìm kiếm lời bài hát được duyệt. Hãy thử tên nghệ sĩ/nhóm nhạc hoặc tên bài hát; SongChart sẽ bổ sung tìm theo lời khi provider phù hợp được cấu hình.',
            ]);
        }

        try {
            $results = $intent === 'artist'
                ? $artists->search($query, 12)
                : $recordings->search($query, 12);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'query' => $exception->getMessage(),
            ]);
        }

        return view('admin.operations.import-preview', [
            'title' => 'Nhập dữ liệu',
            'description' => 'Tìm thông tin theo cách bạn đang biết, SongChart sẽ xử lý phần định danh kỹ thuật.',
            'intent' => $intent,
            'query' => $query,
            'results' => $results,
            'searchNotice' => $results === [] ? 'Không tìm thấy kết quả phù hợp. Hãy thử tên ngắn hơn hoặc thêm tên nghệ sĩ/bài hát.' : null,
        ]);
    }

    public function select(
        ProviderImportSelectionRequest $request,
        ProviderImportSelectionBuilder $selectionBuilder,
    ): View {
        $validated = $request->validated();

        try {
            $selection = $selectionBuilder->build(
                providerSlug: (string) $validated['provider_slug'],
                entityType: EntityType::from((string) $validated['entity_type']),
                externalId: (string) $validated['external_id'],
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'external_id' => $exception->getMessage(),
            ]);
        }

        $payload = $selection['payload'];

        return view('admin.operations.import-plan', [
            'title' => 'Kế hoạch nhập dữ liệu',
            'description' => 'Kiểm tra dữ liệu SongChart vừa tìm được trước khi tạo tác vụ nhập có kiểm soát.',
            'preview' => $selection['preview'],
            'plan' => $selection['plan'],
            'submitted' => [
                'provider_slug' => $payload->providerSlug,
                'entity_type' => $payload->entityType->value,
                'external_id' => $payload->externalId,
                'payload_json' => json_encode($payload->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            ],
        ]);
    }
}
