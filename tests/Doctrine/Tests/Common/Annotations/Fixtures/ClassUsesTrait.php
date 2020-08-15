<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Bar\Autoload;

class ClassUsesTrait
{
    use TraitWithAnnotatedMethod;

    /**
     * @var mixed
     * @Autoload
     */
    public $aProperty;

    /**
     * @Autoload
     */
    public function someMethod(): void
    {
    }
}

namespace Doctrine\Tests\Common\Annotations\Bar;

/** @Annotation */
class Autoload
{
}
