<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;

trait ConflictTraitB
{
    /**
     * @Route
     */
    public function conflict(){}
}
