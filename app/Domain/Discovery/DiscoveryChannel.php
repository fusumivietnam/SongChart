<?php

declare(strict_types=1);

namespace App\Domain\Discovery;

use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use App\Domain\Discovery\Enums\DiscoveryChannelStatus;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;
use InvalidArgumentException;

final class DiscoveryChannel
{
    public const MIN_LIMIT = 1;

    public const MAX_LIMIT = 100;

    /**
     * @param  list<DiscoverySort>  $sorts
     */
    public function __construct(
        public readonly string $id,
        public readonly string $key,
        public string $slug,
        public string $name,
        public ?string $description,
        public readonly DiscoverableEntityType $entityType,
        public readonly DiscoveryChannelMode $mode,
        public DiscoveryChannelStatus $status,
        public ?DiscoveryRuleSet $rules,
        public array $sorts,
        public DiscoveryLayout $layout,
        public int $defaultLimit,
        public DiscoveryPublicationWindow $publication,
        public int $revision = 1,
    ) {
        if ($id === '' || $key === '' || $slug === '' || $name === '') {
            throw new InvalidArgumentException('Discovery channel identity, key, slug and name are required.');
        }

        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
            throw new InvalidArgumentException('Discovery channel key must be a stable machine identifier.');
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            throw new InvalidArgumentException('Discovery channel slug must use lowercase ASCII hyphenated form.');
        }

        if ($defaultLimit < self::MIN_LIMIT || $defaultLimit > self::MAX_LIMIT) {
            throw new InvalidArgumentException('Discovery channel default limit must be between 1 and 100.');
        }

        if (in_array($mode, [DiscoveryChannelMode::Derived, DiscoveryChannelMode::Hybrid], true) && $rules === null) {
            throw new InvalidArgumentException('Derived and hybrid discovery channels require rules.');
        }

        if ($mode === DiscoveryChannelMode::Manual && $rules !== null) {
            throw new InvalidArgumentException('Manual discovery channels do not accept derived rules.');
        }

        if ($revision < 1) {
            throw new InvalidArgumentException('Discovery channel revision must be positive.');
        }

        if (count($sorts) > 3) {
            throw new InvalidArgumentException('Discovery channels support at most three explicit sorts.');
        }
    }

    public function activate(): void
    {
        if ($this->status === DiscoveryChannelStatus::Archived) {
            throw new InvalidArgumentException('Archived discovery channels cannot be activated directly.');
        }

        $this->status = DiscoveryChannelStatus::Active;
        $this->revision++;
    }

    public function pause(): void
    {
        if ($this->status === DiscoveryChannelStatus::Archived) {
            throw new InvalidArgumentException('Archived discovery channels cannot be paused.');
        }

        $this->status = DiscoveryChannelStatus::Paused;
        $this->revision++;
    }

    public function archive(): void
    {
        $this->status = DiscoveryChannelStatus::Archived;
        $this->revision++;
    }
}
