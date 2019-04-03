<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type\Exception;

use LogicException;

final class CompositeTypeRequiresAtLeastTwoSubTypes extends LogicException
{
    public static function fromInsufficientAmount(int $count) : self
    {
        return new self('Composite types must be composed of at least two subtypes, %d given.', $count);
    }
}
