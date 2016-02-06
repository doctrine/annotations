<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Bar2\Autoload;

class ClassOverwritesTrait {
    use TraitWithAnnotatedMethod;

    /**
     * @Autoload
     */
    public function traitMethod()
    {

    }
}


namespace Doctrine\Tests\Common\Annotations\Bar2;

/** @Annotation */
class Autoload
{
}
