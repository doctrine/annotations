<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Bar\Autoload;

class ClassUsesTrait {
    use TraitWithAnnotatedMethod;

    /**
     * @Autoload
     */
    public $aProperty;

    /**
     * @Autoload
     */
    public function someMethod()
    {

    }
}


namespace Doctrine\Tests\Annotations\Bar;

/** @Annotation */
class Autoload
{
}
