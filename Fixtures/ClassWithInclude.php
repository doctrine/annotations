<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\DummyAnnotation;

/**
 * @api
 * @DummyAnnotation(dummyValue="hello")
 */
class ClassWithInclude
{
}

include(__DIR__ . '/ApiClass.php');