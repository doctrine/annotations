<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload;

trait ConflictTraitC
{
    /**
     * @Autoload
     */
    public function conflict(){}
}
