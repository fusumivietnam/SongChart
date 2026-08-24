<?php

declare(strict_types=1);

namespace App\Models\Providers\Identity;

use App\Domain\Providers\Identity\Review\Enums\IdentityConflictDecisionAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IdentityConflictDecision extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    protected $fillable = [
        'identity_conflict_review_id', 'actor_id', 'action', 'selected_entity_id',
        'rationale', 'before_snapshot', 'after_snapshot', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => IdentityConflictDecisionAction::class,
            'before_snapshot' => 'array',
            'after_snapshot' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<IdentityConflictReview, $this> */
    public function review(): BelongsTo
    {
        return $this->belongsTo(IdentityConflictReview::class, 'identity_conflict_review_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
