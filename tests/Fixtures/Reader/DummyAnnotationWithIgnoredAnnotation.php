<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/**
 * @api
 * @Annotation
 */
class DummyAnnotationWithIgnoredAnnotation extends \Doctrine\Annotations\Annotation {
    public $dummyValue;
}