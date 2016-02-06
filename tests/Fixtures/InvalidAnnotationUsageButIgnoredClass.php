<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\Route;

/**
 * @NoAnnotation
 * @IgnoreAnnotation({"NoAnnotation"})
 * @Route("foo")
 */
class InvalidAnnotationUsageButIgnoredClass
{
}