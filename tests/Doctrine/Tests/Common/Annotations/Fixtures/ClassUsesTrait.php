<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Bar\Autoload;

class ClassUsesTrait {
    use TraitWithAnnotatedMethod;

    /**
     * @Autoload
     */
    public function someMethod()
    {

    }
}


namespace Doctrine\Tests\Common\Annotations\Bar;

/** @Annotation */
class Autoload
{
}