<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\SimpleAnnotationReader;

class SimpleAnnotationReaderTest extends AbstractReaderTest
{
    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testImportDetectsNotImportedAnnotation()
    {
        parent::testImportDetectsNotImportedAnnotation();
    }

    /**
     * Contrary to the behavior of the default annotation reader, we do just ignore
     * these in the simple annotation reader (so, no expected exception here).
     */
    public function testImportDetectsNonExistentAnnotation()
    {
        parent::testImportDetectsNonExistentAnnotation();
    }

    /**
     * @expectedException Doctrine\Common\Annotations\AnnotationException
     */
    public function testInvalidAnnotationUsageButIgnoredClass()
    {
        parent::testInvalidAnnotationUsageButIgnoredClass();
    }

    protected function getReader()
    {
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace(__NAMESPACE__);
        $reader->addNamespace(__NAMESPACE__ . '\Fixtures');

        return $reader;
    }
}