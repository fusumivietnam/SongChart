<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\ExtensionOperation;
use App\Models\Provider;
use App\Models\ProviderSyncRun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AdminDashboardSnapshot
{
    /** @return array<string, mixed> */
    public function build(): array
    {
        $providers = $this->providers();
        $recentSyncs = $this->recentSyncs();
        $recentOperations = $this->recentOperations();

        $providerWarnings = $providers->filter(
            fn (Provider $provider): bool => ! $provider->is_enabled || $this->providerStatusValue($provider) !== 'active'
        )->count();

        $failedSyncs = $recentSyncs->where('status', 'failed')->count();
        $pendingOperations = $recentOperations->whereIn('status', ['pending', 'running'])->count();
        $failedImports = DB::table('provider_import_runs')->whereIn('status', ['failed', 'completed_with_errors'])->count();
        $quarantinedItems = DB::table('provider_import_items')->where('status', 'quarantined')->count();
        $openIdentityConflicts = DB::table('identity_conflict_reviews')->whereIn('status', ['open', 'deferred'])->count();

        return [
            'metrics' => [
                ['key' => 'providers', 'label' => 'Nguồn dữ liệu', 'value' => $providers->count(), 'note' => 'Nguồn đã đăng ký', 'tone' => 'primary'],
                ['key' => 'failed-imports', 'label' => 'Tác vụ có vấn đề', 'value' => $failedImports, 'note' => 'Cần thử lại hoặc kiểm tra', 'tone' => $failedImports > 0 ? 'danger' : 'success'],
                ['key' => 'quarantine', 'label' => 'Dữ liệu cần rà soát', 'value' => $quarantinedItems, 'note' => 'Chưa vượt qua kiểm tra tự động', 'tone' => $quarantinedItems > 0 ? 'warning' : 'success'],
                ['key' => 'identity-conflicts', 'label' => 'Xung đột định danh', 'value' => $openIdentityConflicts, 'note' => 'Đang mở hoặc hoãn xử lý', 'tone' => $openIdentityConflicts > 0 ? 'warning' : 'success'],
            ],
            'workItems' => [
                [
                    'key' => 'failed-imports',
                    'label' => 'Tác vụ dữ liệu cần xử lý',
                    'count' => $failedImports,
                    'description' => 'Mở các tác vụ thất bại hoặc hoàn tất có lỗi để xem nguyên nhân và lựa chọn khôi phục.',
                    'severity' => $failedImports > 0 ? 'danger' : 'success',
                    'href' => route('admin.imports.index', ['status' => 'failed']),
                    'action' => 'Xem tác vụ',
                ],
                [
                    'key' => 'quarantine',
                    'label' => 'Dữ liệu cần con người rà soát',
                    'count' => $quarantinedItems,
                    'description' => 'Các bản ghi chưa được chấp nhận vào dữ liệu canonical.',
                    'severity' => $quarantinedItems > 0 ? 'warning' : 'success',
                    'href' => route('admin.quarantine.index'),
                    'action' => 'Mở hàng chờ',
                ],
                [
                    'key' => 'identity-conflicts',
                    'label' => 'Xung đột định danh',
                    'count' => $openIdentityConflicts,
                    'description' => 'Các nguồn dữ liệu đang trỏ tới nhiều ứng viên canonical và cần quyết định.',
                    'severity' => $openIdentityConflicts > 0 ? 'warning' : 'success',
                    'href' => route('admin.identity-conflicts.index'),
                    'action' => 'Rà soát',
                ],
                [
                    'key' => 'provider-review',
                    'label' => 'Nguồn dữ liệu cần chú ý',
                    'count' => $providerWarnings,
                    'description' => 'Nguồn đang tạm ngừng hoặc chưa ở trạng thái hoạt động bình thường.',
                    'severity' => $providerWarnings > 0 ? 'warning' : 'success',
                    'href' => route('admin.providers.index'),
                    'action' => 'Xem nguồn',
                ],
            ],
            'providers' => $providers,
            'recentSyncs' => $recentSyncs,
            'recentOperations' => $recentOperations,
            'systemNotices' => $this->systemNotices($providers, $recentSyncs),
        ];
    }

    /** @return Collection<int, Provider> */
    private function providers(): Collection
    {

        return Provider::query()->orderBy('name')->get();
    }

    /** @return Collection<int, ProviderSyncRun> */
    private function recentSyncs(): Collection
    {

        return ProviderSyncRun::query()
            ->with('provider:id,name')
            ->latest('started_at')
            ->limit(10)
            ->get();
    }

    /** @return Collection<int, ExtensionOperation> */
    private function recentOperations(): Collection
    {

        return ExtensionOperation::query()
            ->with('extension:id,name')
            ->latest('started_at')
            ->limit(8)
            ->get();
    }

    /**
     * @param  Collection<int, Provider>  $providers
     * @param  Collection<int, ProviderSyncRun>  $recentSyncs
     * @return array<int, array{key: string, title: string, description: string, severity: string}>
     */
    private function systemNotices(Collection $providers, Collection $recentSyncs): array
    {
        $notices = [];

        if ($providers->isEmpty()) {
            $notices[] = [
                'key' => 'provider-registry-empty',
                'title' => 'Chưa có nguồn dữ liệu',
                'description' => 'Đăng ký ít nhất một nguồn dữ liệu trước khi bắt đầu nhập hoặc đồng bộ metadata.',
                'severity' => 'warning',
            ];
        }

        if ($recentSyncs->where('status', 'failed')->isNotEmpty()) {
            $notices[] = [
                'key' => 'provider-sync-failed',
                'title' => 'Có tác vụ đồng bộ thất bại',
                'description' => 'Mở tác vụ liên quan để xem nguyên nhân và lựa chọn khôi phục phù hợp.',
                'severity' => 'danger',
            ];
        }

        if ($notices === []) {
            $notices[] = [
                'key' => 'system-stable',
                'title' => 'Không có vấn đề nghiêm trọng cần xử lý',
                'description' => 'Các nguồn dữ liệu và tác vụ gần đây không có lỗi cần hành động ngay.',
                'severity' => 'success',
            ];
        }

        return $notices;
    }

    private function providerStatusValue(Provider $provider): string
    {
        $status = $provider->getAttribute('status');

        if ($status instanceof ProviderStatus) {
            return $status->value;
        }

        return is_string($status) ? $status : 'unknown';
    }

    /** @param class-string $model */
    private function count(string $table, string $model): int
    {
        return $model::query()->count();
    }
}
