<?php

namespace Doctrine\Tests\Annotations\Fixtures\Traits;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Template;
use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;

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
