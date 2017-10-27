<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

trait AnnotateAtMethodLevelTrait
{
    /**
     * @IgnoreNamespaceTrait\Subnamespace\Name
     */
    public function test() {
    }
}
