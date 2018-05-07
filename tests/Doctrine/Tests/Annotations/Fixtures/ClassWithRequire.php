<?php

namespace Doctrine\Tests\Annotations\Fixtures;

// Include a class named Api
require_once __DIR__ . '/Api.php';

use Doctrine\Tests\Annotations\DummyAnnotationWithIgnoredAnnotation;

/**
 * @DummyAnnotationWithIgnoredAnnotation(dummyValue="hello")
 */
class ClassWithRequire
{
}
