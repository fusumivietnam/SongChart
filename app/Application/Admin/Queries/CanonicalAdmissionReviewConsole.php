<?php

declare(strict_types=1);

namespace App\Application\Admin\Queries;

use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\MetadataAssertion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class CanonicalAdmissionReviewConsole
{
    /**
     * @return array{
     *     decisions: LengthAwarePaginator<int, CanonicalAdmissionDecision>,
     *     unstaged: Collection<int, MetadataAssertion>,
     *     status: string
     * }
     */
    public function index(string $requestedStatus): array
    {
        $status = in_array($requestedStatus, array_column(CanonicalAdmissionStatus::cases(), 'value'), true)
            ? $requestedStatus
            : CanonicalAdmissionStatus::Pending->value;

        $decisions = CanonicalAdmissionDecision::query()
            ->with(['assertion.source', 'reviewer'])
            ->where('status', $status)
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $unstaged = MetadataAssertion::query()
            ->with('source')
            ->where('verification_state', 'candidate')
            ->whereDoesntHave('canonicalAdmissionDecision')
            ->latest('observed_at')
            ->limit(50)
            ->get();

        return compact('decisions', 'unstaged', 'status');
    }

    public function show(CanonicalAdmissionDecision $admission): CanonicalAdmissionDecision
    {
        return $admission->loadMissing(['assertion.source', 'reviewer']);
    }
}
