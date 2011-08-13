<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\SimpleAnnotationReader;

class SimpleAnnotationReaderTest extends AbstractReaderTest
{

    protected function getReader()
    {
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace(__NAMESPACE__);
        $reader->addNamespace(__NAMESPACE__ . '\Fixtures');

        return $reader;
    }

    /**
     * Expected Exception invalid for SimpleAnnotationReader
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        parent::testImportDetectsNotImportedAnnotation();
        $this->assertTrue(true);
    }

    /**
     * Expected Exception invalid for SimpleAnnotationReader
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        parent::testImportDetectsNonExistentAnnotation();
        $this->assertTrue(true);
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     */
    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        parent::testInvalidAnnotationUsageButIgnoredClass();
    }

}