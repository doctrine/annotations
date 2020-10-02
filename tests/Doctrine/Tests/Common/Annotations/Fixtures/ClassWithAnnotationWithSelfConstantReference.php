<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Traits\TraitWithSelfConstantReferenceTrait;

/** @AnnotationWithConstants(self::VALUE_FOR_CLASS) */
class ClassWithAnnotationWithSelfConstantReference
{
    use TraitWithSelfConstantReferenceTrait;

    public const VALUE_FOR_CLASS = 'ClassWithAnnotationWithSelfConstantReference.VALUE_FROM_CLASS';
    public const VALUE_FOR_TRAIT = 'ClassWithAnnotationWithSelfConstantReference.VALUE_FOR_TRAIT';

    /**
     * @var mixed
     * @AnnotationWithConstants(self::VALUE_FOR_CLASS)
     */
    private $classProperty;

    /**
     * @return mixed
     *
     * @AnnotationWithConstants(self::VALUE_FOR_CLASS)
     */
    public function classMethod()
    {
        return $this->classProperty;
    }
}
