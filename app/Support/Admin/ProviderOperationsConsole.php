<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportFailure;
use App\Models\Providers\Ingestion\ProviderImportItem;
use App\Models\Providers\Ingestion\ProviderImportRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProviderOperationsConsole
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function providers(array $filters): array
    {
        $term = trim((string) ($filters['q'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $enabled = trim((string) ($filters['enabled'] ?? ''));

        $query = Provider::query()
            ->select(['id', 'name', 'slug', 'category', 'status', 'is_enabled', 'policy_reviewed_at', 'configuration'])
            ->withCount('capabilities');
        if ($term !== '') {
            $query->where(function ($nested) use ($term): void {
                $nested->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%");
            });
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if (in_array($enabled, ['0', '1'], true)) {
            $query->where('is_enabled', $enabled === '1');
        }
        $query->orderBy('name');

        /** @var LengthAwarePaginator<int, Provider> $providers */
        $providers = $query->paginate(25)->withQueryString();

        return [
            'activeAdminNav' => 'providers',
            'title' => 'Nguồn dữ liệu',
            'description' => 'Theo dõi các nguồn dữ liệu, trạng thái hoạt động và những nguồn cần chú ý.',
            'providers' => $providers,
            'filters' => compact('term', 'status', 'enabled'),
            'metrics' => [
                ['label' => 'Đã đăng ký', 'value' => $this->count('providers')],
                ['label' => 'Đang bật', 'value' => $this->countWhere('providers', 'is_enabled', true)],
                ['label' => 'Provider entities', 'value' => $this->count('provider_entities')],
                ['label' => 'Sync runs', 'value' => $this->count('provider_sync_runs')],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function provider(string $id): array
    {
        $provider = Provider::query()
            ->select(['id', 'name', 'slug', 'status', 'is_enabled', 'feature_flag', 'official_docs_url', 'policy_reviewed_at'])
            ->with('capabilities:id,provider_id,capability,status,requires_user_consent,market_dependent,cache_ttl_seconds')
            ->find($id);
        if (! $provider) {
            throw new NotFoundHttpException;
        }

        $syncRuns = DB::table('provider_sync_runs')
            ->select(['id', 'provider_id', 'operation', 'status', 'processed_count', 'failed_count', 'error_summary', 'created_at'])
            ->where('provider_id', $id)
            ->latest('created_at')
            ->limit(25)
            ->get();
        $importRuns = ProviderImportRun::query()
            ->select(['id', 'provider_id', 'operation', 'status', 'attempts', 'error_summary', 'started_at', 'created_at'])
            ->where('provider_id', $id)
            ->latest()
            ->limit(25)
            ->get();

        $operationAudits = DB::table('provider_operation_audits')->select(['action', 'rationale', 'occurred_at'])->where('provider_id', $id)->latest('occurred_at')->limit(25)->get();

        return [
            'activeAdminNav' => 'providers',
            'title' => $provider->name,
            'description' => 'Tình trạng nguồn dữ liệu, hoạt động gần đây và các thao tác vận hành an toàn.',
            'provider' => $provider,
            'syncRuns' => $syncRuns,
            'importRuns' => $importRuns,
            'operationAudits' => $operationAudits,
            'metrics' => [
                ['label' => 'Capabilities', 'value' => $provider->capabilities->count()],
                ['label' => 'Provider entities', 'value' => $this->countWhere('provider_entities', 'provider_id', $id)],
                ['label' => 'Import runs', 'value' => $this->countWhere('provider_import_runs', 'provider_id', $id)],
                ['label' => 'Sync failures', 'value' => $this->countWhere('provider_sync_runs', 'provider_id', $id, ['status' => 'failed'])],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function imports(array $filters): array
    {
        $providerId = trim((string) ($filters['provider'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));
        $operation = trim((string) ($filters['operation'] ?? ''));

        $query = ProviderImportRun::query()
            ->select(['id', 'provider_id', 'operation', 'status', 'attempts', 'error_summary', 'started_at', 'created_at'])
            ->with('provider:id,name,slug');
        if ($providerId !== '') {
            $query->where('provider_id', $providerId);
        }
        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($operation !== '') {
            $query->where('operation', $operation);
        }
        $query->latest();

        /** @var LengthAwarePaginator<int, ProviderImportRun> $runs */
        $runs = $query->paginate(25)->withQueryString();

        $providers = Provider::query()->orderBy('name')->get(['id', 'name']);

        return [
            'activeAdminNav' => 'imports',
            'title' => 'Tác vụ dữ liệu',
            'description' => 'Theo dõi các lần nhập và đồng bộ dữ liệu, tiến độ và vấn đề cần xử lý.',
            'runs' => $runs,
            'providers' => $providers,
            'filters' => compact('providerId', 'status', 'operation'),
            'metrics' => [
                ['label' => 'Tổng runs', 'value' => $this->count('provider_import_runs')],
                ['label' => 'Running', 'value' => $this->countWhere('provider_import_runs', 'status', 'running')],
                ['label' => 'Completed', 'value' => $this->countWhere('provider_import_runs', 'status', 'completed')],
                ['label' => 'Failures', 'value' => $this->count('provider_import_failures')],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function importRun(string $id): array
    {
        $run = ProviderImportRun::query()
            ->select(['id', 'provider_id', 'operation', 'status', 'attempts', 'error_summary', 'started_at', 'finished_at', 'heartbeat_at', 'resume_after'])
            ->with('provider:id,name,slug')
            ->find($id);
        if (! $run) {
            throw new NotFoundHttpException;
        }

        $items = ProviderImportItem::query()
            ->select(['id', 'provider_import_run_id', 'provider_entity_id', 'provider_entity_type', 'canonical_entity_id', 'canonical_entity_type', 'status', 'attempts', 'processed_at', 'created_at'])
            ->where('provider_import_run_id', $id)
            ->latest()
            ->paginate(25, ['*'], 'items_page')
            ->withQueryString();
        $failures = ProviderImportFailure::query()
            ->select(['id', 'provider_import_run_id', 'kind', 'stage', 'error_code', 'message', 'retryable', 'occurred_at'])
            ->where('provider_import_run_id', $id)
            ->latest('occurred_at')
            ->limit(50)
            ->get();
        $checkpoints = DB::table('provider_import_checkpoints')
            ->select(['id', 'provider_import_run_id', 'checkpoint_key', 'committed_at'])
            ->where('provider_import_run_id', $id)
            ->orderByDesc('committed_at')
            ->limit(50)
            ->get();
        $requests = DB::table('provider_import_requests')
            ->select(['id', 'provider_import_run_id', 'sequence', 'method', 'endpoint', 'response_status', 'duration_ms'])
            ->where('provider_import_run_id', $id)
            ->orderByDesc('sequence')
            ->limit(50)
            ->get();

        $operationAudits = DB::table('provider_operation_audits')->select(['action', 'rationale', 'occurred_at'])->where('provider_import_run_id', $id)->latest('occurred_at')->limit(25)->get();

        return [
            'activeAdminNav' => 'imports',
            'title' => 'Chi tiết tác vụ dữ liệu',
            'description' => 'Theo dõi tiến trình, lỗi và các lựa chọn khôi phục an toàn cho tác vụ này.',
            'run' => $run,
            'items' => $items,
            'failures' => $failures,
            'checkpoints' => $checkpoints,
            'requests' => $requests,
            'operationAudits' => $operationAudits,
            'metrics' => [
                ['label' => 'Items', 'value' => $this->countWhere('provider_import_items', 'provider_import_run_id', $id)],
                ['label' => 'Failures', 'value' => $failures->count()],
                ['label' => 'Checkpoints', 'value' => $checkpoints->count()],
                ['label' => 'Requests', 'value' => $requests->count()],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function quarantine(array $filters): array
    {
        $providerId = trim((string) ($filters['provider'] ?? ''));
        $entityType = trim((string) ($filters['entity_type'] ?? ''));

        $query = ProviderImportItem::query()
            ->select(['id', 'provider_import_run_id', 'provider_entity_id', 'provider_entity_type', 'status', 'attempts', 'processed_at', 'created_at'])
            ->with('run:id,provider_id', 'run.provider:id,name,slug')
            ->where('status', 'quarantined');
        if ($providerId !== '') {
            $query->whereRelation('run', 'provider_id', $providerId);
        }
        if ($entityType !== '') {
            $query->where('provider_entity_type', $entityType);
        }
        $query->latest();

        /** @var LengthAwarePaginator<int, ProviderImportItem> $items */
        $items = $query->paginate(25)->withQueryString();
        $providers = Provider::query()->orderBy('name')->get(['id', 'name']);

        return [
            'activeAdminNav' => 'quarantine',
            'title' => 'Quarantine operations',
            'description' => 'Điều tra item bị cách ly; Stage 16.6 không cung cấp retry/requeue mutation.',
            'items' => $items,
            'providers' => $providers,
            'filters' => compact('providerId', 'entityType'),
            'metrics' => [
                ['label' => 'Đang cách ly', 'value' => $this->countWhere('provider_import_items', 'status', 'quarantined')],
                ['label' => 'Retryable failures', 'value' => $this->countWhere('provider_import_failures', 'retryable', true)],
                ['label' => 'Pending items', 'value' => $this->countWhere('provider_import_items', 'status', 'pending')],
                ['label' => 'Failed items', 'value' => $this->countWhere('provider_import_items', 'status', 'failed')],
            ],
        ];
    }

    private function count(string $table): int
    {
        return DB::table($table)->count();
    }

    /** @param array<string, scalar|null> $additional */
    private function countWhere(string $table, string $column, string|bool $value, array $additional = []): int
    {
        $query = DB::table($table)->where($column, $value);
        foreach ($additional as $key => $constraint) {
            $query->where($key, $constraint);
        }

        return $query->count();
    }
}
