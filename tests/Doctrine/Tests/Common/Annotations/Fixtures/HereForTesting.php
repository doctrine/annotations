<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Secure;

interface HereForTesting
{
    /**
     * @Secure
     */
    public function foo();
}
