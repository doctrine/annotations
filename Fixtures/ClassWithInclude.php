<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

// Include a class named Api
require_once(__DIR__ . '/ApiClass.php');

use Doctrine\Tests\Common\Annotations\DummyAnnotationWithImportedClass;

/**
 * @DummyAnnotationWithImportedClass(dummyValue="hello")
 */
class ClassWithInclude
{
}