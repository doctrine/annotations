<?php

namespace Doctrine\AnnotationsTests;

use Doctrine\Annotations\Resolver;
use Doctrine\Annotations\Context;

class ResolverTest extends TestCase
{
    public function testResolveFromSameNamespace()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $expected   = 'Doctrine\AnnotationsTests\Fixtures\NoAnnotation';
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];

        $context  = new Context($class, $namespaces);
        $resolver = new Resolver();

        $this->assertEquals($expected, $resolver->resolve($context, 'NoAnnotation'));
    }

    public function testResolveFromImports()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $expected   = 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template';
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];
        $imports    = [
            'template' => 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template'
        ];

        $context   = new Context($class, $namespaces, $imports);
        $resolver  = new Resolver();

        $this->assertEquals($expected, $resolver->resolve($context, 'Template'));
    }

    public function testResolveFromImportsAlias()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $expected   = 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template';
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];
        $imports    = [
            // use Doctrine\AnnotationsTests\Fixtures\Annotation as Annot;
            'annot' => 'Doctrine\AnnotationsTests\Fixtures\Annotation'
        ];

        $context   = new Context($class, $namespaces, $imports);
        $resolver  = new Resolver();

        $this->assertEquals($expected, $resolver->resolve($context, 'Annot\Template'));
    }

    public function testResolveFullyQualified()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];

        $context   = new Context($class, $namespaces);
        $resolver  = new Resolver();

        $this->assertEquals(
            '\Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
            $resolver->resolve($context, '\Doctrine\AnnotationsTests\Fixtures\Annotation\Template')
        );

        $this->assertEquals(
            'Doctrine\AnnotationsTests\Fixtures\Annotation\Template',
            $resolver->resolve($context, 'Doctrine\AnnotationsTests\Fixtures\Annotation\Template')
        );
    }

    public function testResolutionOrder()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures\Annotation'];
        $imports    = [
            // use Doctrine\AnnotationsTests\Fixtures\Route as Template;
            'template' => '\Doctrine\AnnotationsTests\Fixtures\Annotation\Route'
        ];

        $context  = new Context($class, $namespaces, $imports);
        $resolver = new Resolver();

        $this->assertEquals(
            '\Doctrine\AnnotationsTests\Fixtures\Annotation\Route',
            $resolver->resolve($context, 'Template')
        );
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ClassNotFoundException
     * @expectedExceptionMessage The annotation "@UNKNOWN_ANNOTATION" in class Doctrine\AnnotationsTests\Fixtures\Controller was never imported. Did you maybe forget to add a "use" statement for this annotation ?
     */
    public function testAnnotationNotImportedException()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];

        $context   = new Context($class, $namespaces);
        $resolver  = new Resolver();

        $resolver->resolve($context, 'UNKNOWN_ANNOTATION');
    }

    /**
     * @expectedException \Doctrine\Annotations\Exception\ClassNotFoundException
     * @expectedExceptionMessage The annotation "@\UNKNOWN_FULLY_QUALIFIED_ANNOTATION" in class Doctrine\AnnotationsTests\Fixtures\Controller does not exist, or could not be auto-loaded.
     */
    public function testAnnotationNotFoundException()
    {
        $class      = new \ReflectionClass('Doctrine\AnnotationsTests\Fixtures\Controller');
        $namespaces = ['Doctrine\AnnotationsTests\Fixtures'];

        $context   = new Context($class, $namespaces);
        $resolver  = new Resolver();

        $resolver->resolve($context, '\UNKNOWN_FULLY_QUALIFIED_ANNOTATION');
    }
}
