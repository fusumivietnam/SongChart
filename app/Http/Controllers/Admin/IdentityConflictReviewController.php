<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictDecisionAction;
use App\Http\Controllers\Controller;
use App\Models\Providers\Identity\IdentityConflictReview;
use App\Models\User;
use App\Support\Admin\IdentityConflictReviewConsole;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class IdentityConflictReviewController extends Controller
{
    public const INDEX_USE_CASE = 'admin.identity-conflicts.index';

    public const SHOW_USE_CASE = 'admin.identity-conflicts.show';

    public const DECIDE_USE_CASE = 'admin.identity-conflicts.decide';

    public function index(Request $request, IdentityConflictReviewConsole $console): View
    {
        return view('admin.identity-conflicts.index', $console->index($request->query()));
    }

    public function show(string $review, IdentityConflictReviewConsole $console): View
    {
        return view('admin.identity-conflicts.show', $console->show($review));
    }

    public function decide(Request $request, IdentityConflictReview $review, IdentityConflictReviewService $reviews, PrivilegedAuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::enum(IdentityConflictDecisionAction::class)],
            'selected_entity_id' => ['nullable', 'string', 'size:26'],
            'rationale' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $action = IdentityConflictDecisionAction::from((string) $validated['action']);
        $selectedEntityId = isset($validated['selected_entity_id']) && is_string($validated['selected_entity_id'])
            ? $validated['selected_entity_id']
            : null;
        $rationale = (string) $validated['rationale'];
        /** @var User $actor */
        $actor = $request->user();
        $actorId = (string) $actor->getAuthIdentifier();
        $before = [
            'status' => (string) $review->getRawOriginal('status'),
            'resolution' => $review->getRawOriginal('resolution'),
            'resolved_entity_id' => $review->getRawOriginal('resolved_entity_id'),
        ];

        if (in_array($action, [IdentityConflictDecisionAction::ApproveMatch, IdentityConflictDecisionAction::RejectCandidate], true) && $selectedEntityId === null) {
            throw ValidationException::withMessages(['selected_entity_id' => 'A canonical candidate is required for this decision.']);
        }
        if (! in_array($action, [IdentityConflictDecisionAction::ApproveMatch, IdentityConflictDecisionAction::RejectCandidate], true) && $selectedEntityId !== null) {
            throw ValidationException::withMessages(['selected_entity_id' => 'This decision does not accept a canonical candidate.']);
        }

        match ($action) {
            IdentityConflictDecisionAction::ApproveMatch => $reviews->approveMatch($review, (string) $selectedEntityId, $actorId, $rationale),
            IdentityConflictDecisionAction::RejectCandidate => $reviews->rejectCandidate($review, (string) $selectedEntityId, $actorId, $rationale),
            IdentityConflictDecisionAction::KeepSeparate => $reviews->keepSeparate($review, $actorId, $rationale),
            IdentityConflictDecisionAction::DeferMerge => $reviews->deferMerge($review, $actorId, $rationale),
            IdentityConflictDecisionAction::Reopen => $reviews->reopen($review, $actorId, $rationale),
        };

        $review->refresh();
        $audit->record(
            event: 'identity-conflict.'.$action->value,
            description: 'Identity conflict decision recorded.',
            subject: $review,
            actor: $actor,
            before: $before,
            after: [
                'status' => (string) $review->getRawOriginal('status'),
                'resolution' => $review->getRawOriginal('resolution'),
                'resolved_entity_id' => $review->getRawOriginal('resolved_entity_id'),
            ],
            context: ['selected_entity_id' => $selectedEntityId],
            rationale: $rationale,
        );

        return redirect()->route('admin.identity-conflicts.show', $review)->with('status', 'Identity conflict decision recorded.');
    }
}
