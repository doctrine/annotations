<?php

namespace Doctrine\Tests\Common\Annotations\Ticket;

use Doctrine\Tests\Common\Annotations\Fixtures\Controller;

/**
 * @group
 */
class DCOM55Test extends \PHPUnit_Framework_TestCase
{
    public function testIssue()
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\\Dummy');
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $annots = $reader->getClassAnnotations($class);
        
        $this->assertEquals(0, count($annots));
    }
}

/**
 * @Controller
 */
class Dummy
{
    
}