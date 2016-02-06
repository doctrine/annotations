<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Bar\Autoload;

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


namespace Doctrine\AnnotationsTests\Bar;

/** @Annotation */
class Autoload
{
}
