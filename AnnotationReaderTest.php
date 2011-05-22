<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationReaderTest extends AbstractReaderTest
{
    public function testGetIsAutoloadAnnotations()
    {
        $reader = $this->getReader();

        $this->assertFalse($reader->isAutoloadAnnotations());
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->isAutoloadAnnotations());
        $reader->setAutoloadAnnotations(false);
        $this->assertFalse($reader->isAutoloadAnnotations());
    }

    protected function getReader()
    {
        return new AnnotationReader();
    }
}