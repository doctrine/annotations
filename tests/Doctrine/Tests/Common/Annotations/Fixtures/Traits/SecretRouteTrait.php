<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Version as Property;

trait SecretRouteTrait
{
    /**
     * @Property
     */
    private $route;

    /**
     * @Route("/secret", name="_secret")
     * @Template()
     */
    public function secretAction()
    {
        return array();
    }
}
