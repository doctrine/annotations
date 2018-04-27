<?php

namespace Doctrine\Tests\Annotations\Fixtures;

use Doctrine\Tests\Annotations\Fixtures\Annotation\Secure;
use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;
use Doctrine\Tests\Annotations\Fixtures\Annotation\Template;

$var = 1;
function () use ($var) {};

class NamespaceWithClosureDeclaration {}
