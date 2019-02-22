<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Exception;

use Doctrine\Annotations\Metadata\AnnotationMetadata;
use InvalidArgumentException;
use function sprintf;

final class MetadataAlreadyExists extends InvalidArgumentException
{
    public static function new(AnnotationMetadata $metadata) : self
    {
        return new self(sprintf('Metadata for annotation "%s" already exists.', $metadata->getName()));
    }
}
