<?php

declare(strict_types=1);

namespace App\Application\Admin\Queries;

use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\MetadataAssertion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

final class CanonicalAdmissionReviewConsole
{
    /**
     * @return array{
     *     available: bool,
     *     decisions: LengthAwarePaginator<int, CanonicalAdmissionDecision>|null,
     *     unstaged: Collection<int, MetadataAssertion>,
     *     status: string
     * }
     */
    public function index(string $requestedStatus): array
    {
        $status = in_array($requestedStatus, array_column(CanonicalAdmissionStatus::cases(), 'value'), true)
            ? $requestedStatus
            : CanonicalAdmissionStatus::Pending->value;

        if (! Schema::hasTable('canonical_admission_decisions')) {
            return [
                'available' => false,
                'decisions' => null,
                'unstaged' => new Collection,
                'status' => $status,
            ];
        }

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

        return [
            'available' => true,
            'decisions' => $decisions,
            'unstaged' => $unstaged,
            'status' => $status,
        ];
    }

    public function show(CanonicalAdmissionDecision $admission): CanonicalAdmissionDecision
    {
        return $admission->loadMissing(['assertion.source', 'reviewer']);
    }
}
