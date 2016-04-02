<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload;

class ParentClassWithAnnotatedMethod
{

    /**
     * @Autoload
     */
    public function someMethod()
    {

    }
}
