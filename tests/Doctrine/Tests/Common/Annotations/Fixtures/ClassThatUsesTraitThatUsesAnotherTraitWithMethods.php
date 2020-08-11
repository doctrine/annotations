<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\Traits\TraitThatUsesAnotherTrait;

class ClassThatUsesTraitThatUsesAnotherTraitWithMethods
{
    use TraitThatUsesAnotherTrait;

    /**
     * @Route("/someprefix")
     */
    public function method1(): void
    {
    }

    /**
     * @Route("/someotherprefix")
     */
    public function method2(): void
    {
    }
}
