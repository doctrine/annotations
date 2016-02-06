<?php

namespace Doctrine\AnnotationsTests\Fixtures;

// Include a class named Api
require_once(__DIR__ . '/Api.php');

use Doctrine\AnnotationsTests\DummyAnnotationWithIgnoredAnnotation;

/**
 * @DummyAnnotationWithIgnoredAnnotation(dummyValue="hello")
 */
class ClassWithRequire
{
}