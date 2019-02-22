<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Metadata\Exception;

use Doctrine\Annotations\Metadata\PropertyMetadata;
use LogicException;
use function array_map;
use function count;
use function implode;
use function sprintf;

final class TooManyDefaultProperties extends LogicException
{
    public static function new(string $name, PropertyMetadata ...$properties) : self
    {
        return new self(
            sprintf(
                'The annotation "%s" can only have at most one default property, currently has %d: "%s".',
                $name,
                count($properties),
                implode(
                    '", "',
                    array_map(
                        static function (PropertyMetadata $property) : string {
                            return $property->getName();
                        },
                        $properties
                    )
                )
            )
        );
    }
}
