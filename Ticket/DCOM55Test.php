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
    
    public function testAnnotation()
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\\DCOM55Consumer');
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $annots = $reader->getClassAnnotations($class);
        
        $this->assertEquals(1, count($annots));
        $this->assertInstanceOf(__NAMESPACE__.'\\DCOM55Annotation', $annots[0]);
    }
    
    public function testParseAnnotationDocblocks()
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\\DCOM55Annotation');
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

/**
 * @Annotation
 */
class DCOM55Annotation
{
    
}

/**
 * @DCOM55Annotation
 */
class DCOM55Consumer
{
    
}