<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Application\Catalog\Admission\GovernedCanonicalAdmissionService;
use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Http\Controllers\Controller;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\MetadataAssertion;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class CanonicalAdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', CanonicalAdmissionStatus::Pending->value);
        if (! in_array($status, array_column(CanonicalAdmissionStatus::cases(), 'value'), true)) {
            $status = CanonicalAdmissionStatus::Pending->value;
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

        return view('admin.canonical-admissions.index', compact('decisions', 'unstaged', 'status'));
    }

    public function show(CanonicalAdmissionDecision $admission): View
    {
        $admission->load(['assertion.source', 'reviewer']);

        return view('admin.canonical-admissions.show', compact('admission'));
    }

    public function stage(MetadataAssertion $assertion, GovernedCanonicalAdmissionService $service): RedirectResponse
    {
        $decision = $service->stage($assertion);

        return redirect()->route('admin.canonical-admissions.show', $decision)
            ->with('status', 'Evidence staged for canonical admission review.');
    }

    public function decide(Request $request, CanonicalAdmissionDecision $admission, GovernedCanonicalAdmissionService $service, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['apply', 'reject'])],
            'rationale' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        /** @var User $actor */
        $actor = $request->user();
        $before = [
            'status' => $admission->getRawOriginal('status'),
            'reviewer_id' => $admission->getRawOriginal('reviewer_id'),
            'applied_at' => $admission->getRawOriginal('applied_at'),
        ];
        $action = (string) $validated['action'];
        $rationale = (string) $validated['rationale'];

        $decision = $action === 'apply'
            ? $service->apply($admission, $actor, $rationale)
            : $service->reject($admission, $actor, $rationale);

        $audit->record(
            event: 'canonical-admission.'.$action,
            description: 'Canonical admission decision recorded.',
            subject: $decision,
            actor: $actor,
            before: $before,
            after: [
                'status' => $decision->getRawOriginal('status'),
                'reviewer_id' => $decision->getRawOriginal('reviewer_id'),
                'applied_at' => $decision->getRawOriginal('applied_at'),
            ],
            context: [
                'metadata_assertion_id' => (string) $decision->getAttribute('metadata_assertion_id'),
                'entity_type' => (string) $decision->getRawOriginal('entity_type'),
                'entity_id' => (string) $decision->getAttribute('entity_id'),
                'field_name' => (string) $decision->getAttribute('field_name'),
            ],
            rationale: $rationale,
        );

        return redirect()->route('admin.canonical-admissions.show', $decision)
            ->with('status', 'Canonical admission decision recorded.');
    }
}
