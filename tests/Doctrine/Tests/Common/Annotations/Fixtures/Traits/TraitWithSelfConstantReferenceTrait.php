<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationWithConstants;

trait TraitWithSelfConstantReferenceTrait
{
    /**
     * @var mixed
     * @AnnotationWithConstants(self::VALUE_FOR_TRAIT)
     */
    private $traitProperty;

    /** @AnnotationWithConstants(self::VALUE_FOR_TRAIT) */
    public function traitMethod(): void
    {
    }
}
