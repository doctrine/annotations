<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Configuration;

use Doctrine\Annotations\Reflection\ReflectionFactory;
use Doctrine\Annotations\Metadata\MetadataFactory;
use Doctrine\Annotations\IgnoredAnnotationNames;
use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Parser\HoaParser;
use Doctrine\Annotations\Parser\DocParser;
use Doctrine\Annotations\Parser\PhpParser;
use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Builder;

class ConfigurationTest extends TestCase
{
    public function testConfigurationAccessors()
    {
        $ignored   = new IgnoredAnnotationNames();
        $hoaParser = new HoaParser();
        $phpParser = new PhpParser();
        $resolver  = new Resolver();

        $metadataParser    = new MetadataParser($hoaParser, $resolver);
        $metadataFactory   = new MetadataFactory($metadataParser);
        $reflectionFactory = new ReflectionFactory($phpParser);

        $builder   = new Builder($resolver, $metadataFactory);
        $docParser = new DocParser($hoaParser, $builder, $resolver);

        $config = new Configuration();

        $config->setReflectionFactory($reflectionFactory);
        $config->setMetadataFactory($metadataFactory);
        $config->setIgnoredAnnotationNames($ignored);
        $config->setMetadataParser($metadataParser);
        $config->setDocParser($docParser);
        $config->setPhpParser($phpParser);
        $config->setResolver($resolver);
        $config->setBuilder($builder);

        $this->assertSame($reflectionFactory, $config->getReflectionFactory());
        $this->assertSame($metadataFactory, $config->getMetadataFactory());
        $this->assertSame($ignored, $config->getIgnoredAnnotationNames());
        $this->assertSame($metadataParser, $config->getMetadataParser());
        $this->assertSame($docParser, $config->getDocParser());
        $this->assertSame($phpParser, $config->getPhpParser());
        $this->assertSame($resolver, $config->getResolver());
        $this->assertSame($builder, $config->getBuilder());
    }

    public function testConfigurationDefaults()
    {
        $config = new Configuration();

        $this->assertInstanceOf(IgnoredAnnotationNames::CLASS, $config->getIgnoredAnnotationNames());
        $this->assertInstanceOf(ReflectionFactory::CLASS, $config->getReflectionFactory());
        $this->assertInstanceOf(MetadataFactory::CLASS, $config->getMetadataFactory());
        $this->assertInstanceOf(MetadataParser::CLASS, $config->getMetadataParser());
        $this->assertInstanceOf(DocParser::CLASS, $config->getDocParser());
        $this->assertInstanceOf(PhpParser::CLASS, $config->getPhpParser());
        $this->assertInstanceOf(Resolver::CLASS, $config->getResolver());
        $this->assertInstanceOf(Builder::CLASS, $config->getBuilder());
    }
}
