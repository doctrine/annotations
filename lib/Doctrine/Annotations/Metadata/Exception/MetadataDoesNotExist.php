<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Exception;

use RuntimeException;
use function sprintf;

final class MetadataDoesNotExist extends RuntimeException
{
    public static function new(string $name) : self
    {
        return new self(sprintf('Metadata for annotation "%s" does not exist.', $name));
    }
}
