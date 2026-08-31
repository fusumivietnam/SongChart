<?php

declare(strict_types=1);

namespace App\Domain\Providers\Operations;

use App\Domain\Providers\Enums\ProviderOperationalState;
use App\Domain\Providers\Enums\ProviderRuntimeIssueCode;

final readonly class ProviderOperationalAssessment
{
    /**
     * @param  list<ProviderRuntimeIssueCode>  $issues
     */
    public function __construct(
        public ProviderOperationalState $state,
        public array $issues,
    ) {}

    /** @return list<string> */
    public function issueCodes(): array
    {
        return array_map(
            static fn (ProviderRuntimeIssueCode $issue): string => $issue->value,
            $this->issues,
        );
    }
}
