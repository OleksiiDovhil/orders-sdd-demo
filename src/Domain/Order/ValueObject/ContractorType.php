<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObject;

enum ContractorType: int
{
    case INDIVIDUAL = 1;
    case LEGAL_ENTITY = 2;

    public function getValue(): int
    {
        return $this->value;
    }

    public function isIndividual(): bool
    {
        return $this === self::INDIVIDUAL;
    }

    public function isLegalEntity(): bool
    {
        return $this === self::LEGAL_ENTITY;
    }

    public static function fromInt(int $value): self
    {
        return self::from($value);
    }
}

