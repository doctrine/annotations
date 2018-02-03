<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;

trait SecretRouteTrait
{
    /**
     * @Route("/secret", name="_secret")
     * @Template()
     */
    public function secretAction()
    {
        return [];
    }
}
