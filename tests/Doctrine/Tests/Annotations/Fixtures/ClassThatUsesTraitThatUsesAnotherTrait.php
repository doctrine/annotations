<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Annotations\Fixtures\Traits\TraitThatUsesAnotherTrait;

/**
 * @Route("/someprefix")
 */
class ClassThatUsesTraitThatUsesAnotherTrait
{
    use TraitThatUsesAnotherTrait;
}
