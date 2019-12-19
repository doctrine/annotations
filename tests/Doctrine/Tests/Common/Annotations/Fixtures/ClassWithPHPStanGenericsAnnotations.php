<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @template T
 */
interface WithPHPStanExtendsAnnotationsInterface
{

}

/**
 * @template T
 */
class ClassWithPHPStanExtendsAnnotationsGeneric
{

}

/**
 * @template T
 * @implements WithPHPStanExtendsAnnotationsInterface<int>
 * @extends ClassWithPHPStanExtendsAnnotationsGeneric<int>
 */
class ClassWithPHPStanGenericsAnnotations extends ClassWithPHPStanExtendsAnnotationsGeneric implements WithPHPStanExtendsAnnotationsInterface
{
    /**
     * @var array<T>
     */
    private $bar;

    /**
     * @param array<T> $array
     *
     * @return array<T>
     */
    public function foo($array)
    {
        return $this->bar;
    }
}
