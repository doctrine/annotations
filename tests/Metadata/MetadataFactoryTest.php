<?php

namespace Doctrine\AnnotationsTests\Metadata;

use Doctrine\AnnotationsTests\TestCase;

use Doctrine\Annotations\Metadata\MetadataFactory;
use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Parser\HoaParser;
use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Annotation;
use Doctrine\Annotations\Resolver;

class MetadataFactoryTest extends TestCase
{
    /**
     * @return \Doctrine\Annotations\Metadata\MetadataFactory
     */
    public function createMetadataFactory() : MetadataFactory
    {
        $resolver       = new Resolver();
        $hoaParser      = new HoaParser();
        $metadataParser = new MetadataParser($hoaParser, $resolver);

        return new MetadataFactory($metadataParser);
    }

    public function testGetMetadataFor()
    {
        $factory  = $this->createMetadataFactory();
        $metadata = $factory->getMetadataFor(Annotation::CLASS);

        $this->assertEquals(Annotation::CLASS, $metadata->class);
        $this->assertEquals(Target::TARGET_ALL, $metadata->target);
        $this->assertTrue($metadata->hasConstructor);

        $this->assertArrayHasKey('value', $metadata->properties);
        $this->assertEquals('value', $metadata->properties['value']['name']);
        $this->assertEquals('mixed', $metadata->properties['value']['type']);

        $this->assertSame($metadata, $factory->getMetadataFor(Annotation::CLASS));
    }

    public function testGetMetadataForAnnotationTarget()
    {
        $class    = 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetClass';
        $factory  = $this->createMetadataFactory();
        $metadata = $factory->getMetadataFor($class);

        $this->assertEquals($class, $metadata->class);
        $this->assertEquals(Target::TARGET_CLASS, $metadata->target);
    }

    public function testGetMetadataForAnnotationType()
    {
        $class    = 'Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationWithVarType';
        $factory  = $this->createMetadataFactory();
        $metadata = $factory->getMetadataFor($class);

        $this->assertEquals($class, $metadata->class);

        $this->assertArrayHasKey('mixed', $metadata->properties);
        $this->assertArrayHasKey('boolean', $metadata->properties);
        $this->assertArrayHasKey('bool', $metadata->properties);
        $this->assertArrayHasKey('float', $metadata->properties);
        $this->assertArrayHasKey('string', $metadata->properties);
        $this->assertArrayHasKey('integer', $metadata->properties);
        $this->assertArrayHasKey('array', $metadata->properties);
        $this->assertArrayHasKey('annotation', $metadata->properties);
        $this->assertArrayHasKey('arrayOfStrings', $metadata->properties);
        $this->assertArrayHasKey('arrayOfIntegers', $metadata->properties);
        $this->assertArrayHasKey('arrayOfAnnotations', $metadata->properties);

        $this->assertEquals('mixed', $metadata->properties['mixed']['type']);
        $this->assertEquals('boolean', $metadata->properties['boolean']['type']);
        $this->assertEquals('boolean', $metadata->properties['bool']['type']);
        $this->assertEquals('double', $metadata->properties['float']['type']);
        $this->assertEquals('string', $metadata->properties['string']['type']);
        $this->assertEquals('integer', $metadata->properties['integer']['type']);
        $this->assertEquals('array', $metadata->properties['array']['type']);
        $this->assertEquals('array', $metadata->properties['arrayOfIntegers']['type']);
        $this->assertEquals('array', $metadata->properties['arrayOfStrings']['type']);
        $this->assertEquals('array', $metadata->properties['arrayOfAnnotations']['type']);
        $this->assertEquals('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', $metadata->properties['annotation']['type']);

        $this->assertEquals('integer', $metadata->properties['arrayOfIntegers']['array_type']);
        $this->assertEquals('string', $metadata->properties['arrayOfStrings']['array_type']);
        $this->assertEquals('Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetAll', $metadata->properties['arrayOfAnnotations']['array_type']);
    }
}
