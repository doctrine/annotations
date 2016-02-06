<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/**
 * @parseAnnotation("var")
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 */
class TestParseAnnotationClass
{
    /**
     * @var
     */
    private $field;
}