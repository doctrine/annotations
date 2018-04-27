<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Template;
use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Annotations\Fixtures\Traits\SecretRouteTrait;

/**
 * @Route("/someprefix")
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ControllerWithTrait
{
    use SecretRouteTrait;

    /**
     * @Route("/", name="_demo")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }
}
