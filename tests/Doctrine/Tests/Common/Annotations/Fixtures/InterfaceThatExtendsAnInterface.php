<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Common\Annotations\Fixtures\EmptyInterface;

/**
 * @Route("/someprefix")
 */
interface InterfaceThatExtendsAnInterface extends EmptyInterface
{
}
