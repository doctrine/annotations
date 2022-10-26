<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithEnumAnnotations
{
    /** @AnnotationWithEnumProperty */
    public mixed $annotationWithDefaults;

    /** @AnnotationWithEnumProperty(suit=Suit::Spades) */
    public mixed $annotationWithSpades;
}
