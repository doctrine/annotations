<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;

trait ConflictTraitA
{
    /**
     * @Template
     */
    public function conflict(){}
}
