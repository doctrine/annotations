<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Template;
use Doctrine\Tests\Common\Annotations\Fixtures\Traits\SecretRouteTrait;

/**
 * @Route("/someprefix")
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ControllerWithTrait
{
    use SecretRouteTrait;

    /**
     * @return mixed[]
     *
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction(): array
    {
        return [];
    }
}
