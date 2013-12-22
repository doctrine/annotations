<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload;

trait TraitWithAnnotatedMethod {

    /**
     * @Autoload
     */
    public $traitProperty;

    /**
     * @Autoload
     */
    public function traitMethod()
    {
    }
}
