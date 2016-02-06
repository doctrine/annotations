<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Configuration;

class ConfigurationTest extends TestCase
{
    public function testConfigurationAccessors()
    {
        $metadata  = $this->getMock('Doctrine\Annotations\Metadata\MetadataFactory', [], [], '', false);
        $ignored   = $this->getMock('Doctrine\Annotations\IgnoredAnnotationNames', [], [], '', false);
        $phpParser = $this->getMock('Doctrine\Annotations\Parser\PhpParser', [], [], '', false);
        $resolver  = $this->getMock('Doctrine\Annotations\Resolver', [], [], '', false);
        $builder   = $this->getMock('Doctrine\Annotations\Builder', [], [], '', false);
        $config    = new Configuration();

        $config->setIgnoredAnnotationNames($ignored);
        $config->setMetadataFactory($metadata);
        $config->setPhpParser($phpParser);
        $config->setResolver($resolver);
        $config->setBuilder($builder);

        $this->assertSame($ignored, $config->getIgnoredAnnotationNames());
        $this->assertSame($metadata, $config->getMetadataFactory());
        $this->assertSame($phpParser, $config->getPhpParser());
        $this->assertSame($resolver, $config->getResolver());
        $this->assertSame($builder, $config->getBuilder());
    }

    public function testConfigurationDefaults()
    {
        $config = new Configuration();

        $this->assertInstanceOf('Doctrine\Annotations\IgnoredAnnotationNames', $config->getIgnoredAnnotationNames());
        $this->assertInstanceOf('Doctrine\Annotations\Metadata\MetadataFactory', $config->getMetadataFactory());
        $this->assertInstanceOf('Doctrine\Annotations\Parser\PhpParser', $config->getPhpParser());
        $this->assertInstanceOf('Doctrine\Annotations\Resolver', $config->getResolver());
        $this->assertInstanceOf('Doctrine\Annotations\Builder', $config->getBuilder());
    }
}
