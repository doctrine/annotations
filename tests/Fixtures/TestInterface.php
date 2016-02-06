<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\Secure;

interface TestInterface
{
    /**
     * @Secure
     */
    function foo();
}