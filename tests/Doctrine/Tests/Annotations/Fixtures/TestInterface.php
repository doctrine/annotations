<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Secure;

interface TestInterface
{
    /**
     * @Secure
     */
    public function foo();
}
