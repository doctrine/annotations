<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Common\Annotations\Reader;

class ReaderTest extends AbstractReaderTest
{
    public function testGetIsAutoloadAnnotations()
    {
        $reader = $this->getReader();

        $this->assertTrue($reader->isAutoloadAnnotations());
        $reader->setAutoloadAnnotations(false);
        $this->assertFalse($reader->isAutoloadAnnotations());
        $reader->setAutoloadAnnotations(true);
        $this->assertTrue($reader->isAutoloadAnnotations());
    }

    protected function getReader()
    {
        return new Reader();
    }
}