<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 * @NamedArgumentConstructor
 */
final class AnnotationWithEnumProperty
{
    public function __construct(
        public readonly Suit $suit = Suit::Hearts,
    ) {
    }
}
