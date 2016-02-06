<?php
namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\Autoload;

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
