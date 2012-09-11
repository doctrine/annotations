<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\DummyAnnotation;

/**
 * @api
 * @DummyAnnotation(dummyValue="hello")
 */
class ClassWithAlias
{
}

class ApiTestAlias
{	
}

class_alias('ApiTestAlias', 'API');