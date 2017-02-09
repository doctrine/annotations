<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * The leading \ is intentional to ensure that code using
 * this use statement format works see issue #115/PR #120
 */
use \Doctrine\Tests\Common\Annotations\Fixtures\Annotation as Annotations;

/**
 * @Annotations\SingleUseAnnotation
 */
class ClassWithFullPathUseStatement
{

}
