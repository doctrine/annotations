<?php
namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Autoload;

trait TraitWithAnnotatedMethod {

    /**
     * @Autoload
     */
    public $traitProperty;

    /**
     * @Autoload
     */
    public function traitMethod() : void
    {
    }
}
