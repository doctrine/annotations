<?php

namespace Doctrine\AnnotationsTests\Fixtures;

use Doctrine\AnnotationsTests\Fixtures\Annotation\Secure;
use Doctrine\AnnotationsTests\Fixtures\Annotation\Route;
use Doctrine\AnnotationsTests\Fixtures\Annotation\Template;

$var = 1;
function () use ($var) {};

class NamespaceWithClosureDeclaration {}
