<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Exception;

use InvalidArgumentException;
use function sprintf;

final class InvalidAnnotationTarget extends InvalidArgumentException
{
    public static function fromInvalidBitmask(int $bitmask) : self
    {
        return new self(sprintf('Annotation target "%d" is not valid bitmask of allowed targets.', $bitmask));
    }
}
