<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\ValueObjects;

use App\Domain\Providers\Normalization\Enums\FieldPresence;
use InvalidArgumentException;

final readonly class ProviderField
{
    private function __construct(
        public FieldPresence $presence,
        public mixed $value = null,
    ) {
        if ($presence !== FieldPresence::Provided && $value !== null) {
            throw new InvalidArgumentException('Only provided fields may carry a value.');
        }

        if ($presence === FieldPresence::Provided && $value === null) {
            throw new InvalidArgumentException('Use explicitNull() when a provider supplied null.');
        }
    }

    public static function missing(): self
    {
        return new self(FieldPresence::Missing);
    }

    public static function unknown(): self
    {
        return new self(FieldPresence::Unknown);
    }

    public static function explicitNull(): self
    {
        return new self(FieldPresence::ExplicitNull);
    }

    public static function provided(mixed $value): self
    {
        return new self(FieldPresence::Provided, $value);
    }

    /** @return array{presence: string, value?: mixed} */
    public function toArray(): array
    {
        $result = ['presence' => $this->presence->value];
        if ($this->presence === FieldPresence::Provided) {
            $result['value'] = $this->value;
        }

        return $result;
    }

    /** @param array{presence: string, value?: mixed} $data */
    public static function fromArray(array $data): self
    {
        $presence = FieldPresence::from($data['presence']);

        return match ($presence) {
            FieldPresence::Missing => self::missing(),
            FieldPresence::Unknown => self::unknown(),
            FieldPresence::ExplicitNull => self::explicitNull(),
            FieldPresence::Provided => array_key_exists('value', $data)
                ? self::provided($data['value'])
                : throw new InvalidArgumentException('Provided fields require a value key.'),
        };
    }
}
