<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;

/**
 * @Route("/someprefix")
 */
interface InterfaceThatExtendsAnInterface extends EmptyInterface
{
}
