<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("CONSTANT")
 */
class AnnotationTargetConstant
{
    /** @var mixed */
    public $data;
    /** @var mixed */
    public $name;
    /** @var mixed */
    public $target;
}
