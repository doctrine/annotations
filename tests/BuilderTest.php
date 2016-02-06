<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Context;
use Doctrine\Annotations\Builder;
use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Reference;
use Doctrine\Annotations\Parser\DocParser;
use Doctrine\Annotations\Metadata\MetadataFactory;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $builder;

    protected function setUp()
    {
        parent::setUp();

        $resolver = $this->config->getResolver();
        $factory  = $this->config->getMetadataFactory();

        $this->builder  = new Builder($resolver, $factory);
    }

    public function testCreateSimpleAnnotation()
    {
        $class     = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespace = 'Doctrine\AnnotationsTests\Fixtures';
        $imports   = [
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template'
        ];

        $context   = new Context($class, $namespace, $imports);
        $reference = new Reference('Template', [
            'value' => 'BlogBundle:Post:show.html.twig'
        ]);

        $annotation = $this->builder->create($context, $reference);

        $this->assertInstanceOf('Doctrine\AnnotationsTests\Fixtures\Annotation\Template', $annotation);
        $this->assertEquals('BlogBundle:Post:show.html.twig', $annotation->getName());
    }

    /**
     * @expectedException Doctrine\Annotations\Exception\TargetNotAllowedException
     * @expectedExceptionMessage Annotation @Doctrine\AnnotationsTests\Fixtures\Annotation\AnnotationTargetPropertyMethod is not allowed to be declared on class Doctrine\AnnotationsTests\Fixtures\Controller. You may only use this annotation on these code elements: METHOD,PROPERTY.
     */
    public function testTargetNotAllowedException()
    {
        $class     = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespace = 'Doctrine\AnnotationsTests\Fixtures\Annotation';
        $context   = new Context($class, $namespace);
        $reference = new Reference('AnnotationTargetPropertyMethod', [
            'data'  => 'string val'
        ]);

        $this->builder->create($context, $reference);
    }
}
