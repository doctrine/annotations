<?php

namespace Doctrine\AnnotationsTests\Metadata;

use Doctrine\AnnotationsTests\Fixtures\DummyClass;
use Doctrine\AnnotationsTests\TestCase;

use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Parser\HoaParser;
use Doctrine\Annotations\Resolver;

class MetadataParserTest extends TestCase
{
    /**
     * @return \Doctrine\Annotations\Parser\MetadataParser
     */
    public function createMetadataParser() : MetadataParser
    {
        $resolver  = new Resolver();
        $hoaParser = new HoaParser();

        return new MetadataParser($hoaParser, $resolver);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidReflectionClass()
    {
        $factory = $this->createMetadataParser();

        $factory->parseAnnotation(new DummyClass());
}
}
