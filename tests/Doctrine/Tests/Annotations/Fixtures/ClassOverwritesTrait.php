<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Bar2\Autoload;

class ClassOverwritesTrait {
    use TraitWithAnnotatedMethod;

    /**
     * @Autoload
     */
    public function traitMethod() : void
    {

    }
}


namespace Doctrine\Tests\Annotations\Bar2;

/** @Annotation */
class Autoload
{
}
