<?php

declare(strict_types=1);

namespace App\Support\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

final class PrivilegedAuditConsole
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Activity>
     */
    public function activities(array $filters): LengthAwarePaginator
    {
        $query = Activity::query()
            ->where('log_name', 'privileged')
            ->with(['causer', 'subject'])
            ->latest('id');

        $event = isset($filters['event']) ? trim((string) $filters['event']) : '';
        if ($event !== '') {
            $query->where('event', $event);
        }

        $actor = isset($filters['actor']) ? trim((string) $filters['actor']) : '';
        if ($actor !== '') {
            $actorIds = User::query()
                ->select('id')
                ->where(function ($query) use ($actor): void {
                    $query->where('email', 'ilike', '%'.$actor.'%')
                        ->orWhere('name', 'ilike', '%'.$actor.'%');
                });

            $query
                ->where('causer_type', User::class)
                ->whereIn('causer_id', $actorIds);
        }

        return $query->paginate(50)->withQueryString();
    }
}
