<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation as A;

trait TraitWithAnnotatedMethod {

    /**
     * @A\Autoload
     * @param $injected
     */
    public function myMethod($injected) {

    }
}
