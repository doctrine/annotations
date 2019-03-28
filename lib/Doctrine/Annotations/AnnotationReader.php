<?php

namespace Doctrine\Annotations;

use Doctrine\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Annotations\Annotation\Target;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * A reader for docblock annotations.
 *
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AnnotationReader implements Reader
{
    /**
     * Global map for imports.
     *
     * @var array
     */
    private static $globalImports = [
        'ignoreannotation' => 'Doctrine\Annotations\Annotation\IgnoreAnnotation',
    ];

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNames = ImplicitlyIgnoredAnnotationNames::LIST;

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNamespaces = [];

    /**
     * Add a new annotation to the globally ignored annotation names with regard to exception handling.
     *
     * @param string $name
     */
    static public function addGlobalIgnoredName($name)
    {
        self::$globalIgnoredNames[$name] = true;
    }

    /**
     * Add a new annotation to the globally ignored annotation namespaces with regard to exception handling.
     *
     * @param string $namespace
     */
    static public function addGlobalIgnoredNamespace($namespace)
    {
        self::$globalIgnoredNamespaces[$namespace] = true;
    }

    /**
     * Annotations parser.
     *
     * @var \Doctrine\Annotations\DocParser
     */
    private $parser;

    /**
     * Annotations parser used to collect parsing metadata.
     *
     * @var \Doctrine\Annotations\DocParser
     */
    private $preParser;

    /**
     * PHP parser used to collect imports.
     *
     * @var \Doctrine\Annotations\PhpParser
     */
    private $phpParser;

    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @var array
     */
    private $imports = [];

    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @var array
     */
    private $ignoredAnnotationNames = [];

    /**
     * Constructor.
     *
     * Initializes a new AnnotationReader.
     *
     * @param DocParser $parser
     *
     * @throws AnnotationException
     */
    public function __construct(DocParser $parser = null)
    {
        if (extension_loaded('Zend Optimizer+') && (ini_get('zend_optimizerplus.save_comments') === "0" || ini_get('opcache.save_comments') === "0")) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        if (extension_loaded('Zend OPcache') && ini_get('opcache.save_comments') == 0) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        $this->parser = $parser ?: new DocParser();

        $this->preParser = new DocParser;

        $this->preParser->setImports(self::$globalImports);
        $this->preParser->setIgnoreNotImportedAnnotations(true);
        $this->preParser->setIgnoredAnnotationNames(self::$globalIgnoredNames);

        $this->phpParser = new PhpParser;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $this->parser->setTarget(Target::TARGET_CLASS);
        $this->parser->setImports($this->getClassImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($class->getDocComment(), 'class ' . $class->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        $annotations = $this->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $class   = $property->getDeclaringClass();
        $context = 'property ' . $class->getName() . "::\$" . $property->getName();

        $this->parser->setTarget(Target::TARGET_PROPERTY);
        $this->parser->setImports($this->getPropertyImports($property));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($property->getDocComment(), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $class   = $method->getDeclaringClass();
        $context = 'method ' . $class->getName() . '::' . $method->getName() . '()';

        $this->parser->setTarget(Target::TARGET_METHOD);
        $this->parser->setImports($this->getMethodImports($method));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($method->getDocComment(), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantAnnotations(\ReflectionClassConstant $constant)
    {
        $class   = $constant->getDeclaringClass();
        $context = 'constant ' . $class->getName() . "::" . $constant->getName();

        $this->parser->setTarget(Target::TARGET_CONSTANT);
        $this->parser->setImports($this->getConstantImports($constant));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($constant->getDocComment(), $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getConstantAnnotation(\ReflectionClassConstant $constant, $annotationName)
    {
        $annotations = $this->getConstantAnnotations($constant);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Returns the ignored annotations for the given class.
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getIgnoredAnnotationNames(ReflectionClass $class)
    {
        $name = $class->getName();
        if (isset($this->ignoredAnnotationNames[$name])) {
            return $this->ignoredAnnotationNames[$name];
        }

        $this->collectParsingMetadata($class);

        return $this->ignoredAnnotationNames[$name];
    }

    /**
     * Retrieves imports.
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getClassImports(ReflectionClass $class)
    {
        $name = $class->getName();
        if (isset($this->imports[$name])) {
            return $this->imports[$name];
        }

        $this->collectParsingMetadata($class);

        return $this->imports[$name];
    }

    /**
     * Retrieves imports for methods.
     *
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    private function getMethodImports(ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $classImports = $this->getClassImports($class);

        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ($trait->hasMethod($method->getName())
                && $trait->getFileName() === $method->getFileName()
            ) {
                $traitImports = array_merge($traitImports, $this->phpParser->parseClass($trait));
            }
        }

        return array_merge($classImports, $traitImports);
    }

    /**
     * Retrieves imports for properties.
     *
     * @param \ReflectionProperty $property
     *
     * @return array
     */
    private function getPropertyImports(ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $classImports = $this->getClassImports($class);

        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ($trait->hasProperty($property->getName())) {
                $traitImports = array_merge($traitImports, $this->phpParser->parseClass($trait));
            }
        }

        return array_merge($classImports, $traitImports);
    }

    /**
     * Retrieves imports for constants.
     *
     * @param \ReflectionClassConstant $constant
     *
     * @return array
     */
    private function getConstantImports(\ReflectionClassConstant $constant)
    {
        $class = $constant->getDeclaringClass();
        $classImports = $this->getClassImports($class);
        if (!method_exists($class, 'getTraits')) {
            return $classImports;
        }

        $traitImports = array();

        foreach ($class->getTraits() as $trait) {
            if ($trait->hasConstant($constant->getName())) {
                $traitImports = array_merge($traitImports, $this->phpParser->parseClass($trait));
            }
        }

        return array_merge($classImports, $traitImports);
    }
    
    /**
     * Collects parsing metadata for a given class.
     *
     * @param \ReflectionClass $class
     */
    private function collectParsingMetadata(ReflectionClass $class)
    {
        $ignoredAnnotationNames = self::$globalIgnoredNames;
        $annotations            = $this->preParser->parse($class->getDocComment(), 'class ' . $class->name);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof IgnoreAnnotation) {
                foreach ($annotation->names AS $annot) {
                    $ignoredAnnotationNames[$annot] = true;
                }
            }
        }

        $name = $class->getName();

        $this->imports[$name] = array_merge(
            self::$globalImports,
            $this->phpParser->parseClass($class),
            ['__NAMESPACE__' => $class->getNamespaceName()]
        );

        $this->ignoredAnnotationNames[$name] = $ignoredAnnotationNames;
    }
}
