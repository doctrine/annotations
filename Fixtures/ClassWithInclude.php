<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\DummyAnnotationWithImportedClass;

/**
 * @DummyAnnotationWithImportedClass(dummyValue="hello")
 */
class ClassWithInclude
{
}

include(__DIR__ . '/ApiClass.php');