<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Bar2\Autoload;

class ClassOverwritesTrait {
    use TraitWithAnnotatedMethod;

    /**
     * @Autoload
     */
    public function traitMethod()
    {

    }
}


namespace Doctrine\AnnotationsTests\Bar2;

/** @Annotation */
class Autoload
{
}
