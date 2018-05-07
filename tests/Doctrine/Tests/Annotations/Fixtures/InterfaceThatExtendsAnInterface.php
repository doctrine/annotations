<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;

/**
 * @Route("/someprefix")
 */
interface InterfaceThatExtendsAnInterface extends EmptyInterface
{
}
