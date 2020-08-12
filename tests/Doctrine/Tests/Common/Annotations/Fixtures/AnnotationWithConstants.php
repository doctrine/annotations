<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 */
final class AnnotationWithConstants
{
    public const INTEGER = 1;
    public const FLOAT   = 1.2;
    public const STRING  = '1.2.3';

    /** @var mixed */
    public $value;
}
