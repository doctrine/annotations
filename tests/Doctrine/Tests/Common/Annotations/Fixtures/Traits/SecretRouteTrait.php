<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;

trait SecretRouteTrait
{
    /**
     * @return mixed[]
     *
     * @Route("/secret", name="_secret")
     * @Template()
     */
    public function secretAction(): array
    {
        return [];
    }
}
