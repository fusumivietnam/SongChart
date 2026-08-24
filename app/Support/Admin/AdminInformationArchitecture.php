<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Enums\Capability;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class AdminInformationArchitecture
{
    /** @return array<string, mixed> */
    public function catalog(): array
    {
        $entities = [
            ['key' => 'artists', 'label' => 'Nghệ sĩ', 'table' => 'artists', 'routeType' => 'artist'],
            ['key' => 'works', 'label' => 'Tác phẩm', 'table' => 'works', 'routeType' => 'work'],
            ['key' => 'recordings', 'label' => 'Bản thu', 'table' => 'recordings', 'routeType' => 'recording'],
            ['key' => 'versions', 'label' => 'Phiên bản', 'table' => 'recording_versions', 'routeType' => 'version'],
            ['key' => 'releases', 'label' => 'Bản phát hành', 'table' => 'releases', 'routeType' => 'release'],
            ['key' => 'collections', 'label' => 'Bộ sưu tập', 'table' => 'collections', 'routeType' => 'collection'],
        ];

        foreach ($entities as &$entity) {
            $entity['count'] = $this->count($entity['table']);
        }

        return [
            'activeAdminNav' => 'catalog',
            'title' => 'Danh mục canonical',
            'description' => 'Quan sát các thực thể canonical và mức độ bao phủ dữ liệu hiện tại.',
            'entities' => $entities,
            'metrics' => [
                ['label' => 'Tổng thực thể', 'value' => collect($entities)->sum('count')],
                ['label' => 'External identifiers', 'value' => $this->count('external_identifiers')],
                ['label' => 'Quan hệ', 'value' => $this->count('entity_relationships')],
                ['label' => 'Metadata conflicts', 'value' => $this->count('metadata_conflicts')],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function users(): array
    {
        $users = User::query()->latest()->limit(50)->get(['id', 'name', 'email', 'role', 'is_active', 'email_verified_at', 'created_at']);

        return [
            'activeAdminNav' => 'users',
            'title' => 'Người dùng',
            'description' => 'Danh sách read-only tài khoản, trạng thái hoạt động và vai trò hiện tại.',
            'users' => $users,
            'metrics' => [
                ['label' => 'Tổng tài khoản', 'value' => $users->count()],
                ['label' => 'Đang hoạt động', 'value' => $users->where('is_active', true)->count()],
                ['label' => 'Đã xác minh email', 'value' => $users->whereNotNull('email_verified_at')->count()],
                ['label' => 'Admin', 'value' => $users->filter(fn (User $user) => Gate::forUser($user)->allows(Capability::AccessAdmin->value))->count()],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function system(): array
    {
        $failedJobs = $this->count('failed_jobs');

        return [
            'activeAdminNav' => 'system',
            'title' => 'System health',
            'description' => 'Thông tin runtime read-only phục vụ chẩn đoán vận hành.',
            'checks' => [
                ['label' => 'Environment', 'value' => app()->environment(), 'state' => app()->environment('production') ? 'production' : 'development'],
                ['label' => 'Database', 'value' => DB::connection()->getDriverName(), 'state' => 'available'],
                ['label' => 'Queue', 'value' => (string) config('queue.default'), 'state' => 'configured'],
                ['label' => 'Cache', 'value' => (string) config('cache.default'), 'state' => 'configured'],
                ['label' => 'Debug', 'value' => config('app.debug') ? 'enabled' : 'disabled', 'state' => config('app.debug') ? 'warning' : 'safe'],
                ['label' => 'Pending jobs', 'value' => (string) $this->count('jobs'), 'state' => 'observed'],
                ['label' => 'Failed jobs', 'value' => (string) $failedJobs, 'state' => $failedJobs > 0 ? 'warning' : 'clear'],
            ],
        ];
    }

    private function count(string $table): int
    {
        return DB::table($table)->count();
    }
}
