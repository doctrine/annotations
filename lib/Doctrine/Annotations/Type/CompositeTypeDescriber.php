<?php

declare(strict_types=1);

namespace Doctrine\Annotations\Type;

use function array_map;
use function implode;
use function sprintf;

/**
 * @internal
 */
final class CompositeTypeDescriber
{
    private function __construct()
    {
    }

    /**
     * @param Type[] $subTypes
     */
    public static function describe(string $separator, $subTypes) : string
    {
        return implode(
            $separator,
            array_map(
                static function (Type $subType) : string {
                    if ($subType instanceof CompositeType) {
                        return sprintf('(%s)', $subType->describe());
                    }

                    return $subType->describe();
                },
                $subTypes
            )
        );
    }
}
