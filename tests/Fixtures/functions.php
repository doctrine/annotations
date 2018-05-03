<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationEnum;
use Doctrine\AnnotationsTests\Fixtures\Annotation\Autoload;

/**
 * @AnnotationEnum(AnnotationEnum::ONE)
 */
function annotation_enum_function()
{
}

/**
 * @Autoload
 */
function annotation_autoload_function()
{
}