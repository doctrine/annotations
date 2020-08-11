<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Param;

class ClassWithImportedIgnoredAnnotation
{
    /**
     * @param string $foo
     */
    public function something($foo): void
    {
    }
}
