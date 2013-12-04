<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation as A;

trait TraitWithAliasAndAnnotatedMethod {

    /**
     * @A\Autoload
     */
    public function myMethod() {
    }
}
