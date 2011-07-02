<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

require_once __DIR__ . '/Annotation/IgnoreAnnotation.php';

/**
 * A reader for docblock annotations.
 *
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class AnnotationReader implements Reader
{
    /**
     * Global map for imports.
     *
     * @var array
     */
    private static $globalImports = array(
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation',
    );

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNames = array(
        'access'=> true, 'author'=> true, 'copyright'=> true, 'deprecated'=> true,
        'example'=> true, 'ignore'=> true, 'internal'=> true, 'link'=> true, 'see'=> true,
        'since'=> true, 'tutorial'=> true, 'version'=> true, 'package'=> true,
        'subpackage'=> true, 'name'=> true, 'global'=> true, 'param'=> true,
        'return'=> true, 'staticvar'=> true, 'category'=> true, 'staticVar'=> true,
        'static'=> true, 'var'=> true, 'throws'=> true, 'inheritdoc'=> true,
        'inheritDoc'=> true, 'license'=> true, 'todo'=> true, 'deprecated'=> true,
        'deprec'=> true, 'author'=> true, 'property' => true, 'method' => true,
        'abstract'=> true, 'exception'=> true, 'magic' => true, 'api' => true,
        'final'=> true, 'filesource'=> true, 'throw' => true, 'uses' => true,
        'usedby'=> true, 'private' => true, 'Annotation' => true,
    );

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
     * Annotations Parser
     *
     * @var Doctrine\Common\Annotations\DocParser
     */
    private $parser;

    /**
     * Annotations Parser used to collect parsing metadata
     *
     * @var Doctrine\Common\Annotations\DocParser
     */
    private $preParser;

    /**
     * PHP Parser used to collect imports.
     *
     * @var Doctrine\Common\Annotations\PhpParser
     */
    private $phpParser;

    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @var array
     */
    private $imports = array();

    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * @var string
     */
    private $defaultAnnotationNamespace = false;

    /**
     * @var bool
     */
    private $enablePhpImports = true;

    /**
     * Constructor. Initializes a new AnnotationReader that uses the given Cache provider.
     *
     * @param DocParser $parser The parser to use. If none is provided, the default parser is used.
     */
    public function __construct()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Annotation/IgnoreAnnotation.php');
        
        $this->parser = new DocParser;

        $this->preParser = new DocParser;
        $this->preParser->setImports(self::$globalImports);
        $this->preParser->setIgnoreNotImportedAnnotations(true);

        $this->phpParser = new PhpParser;
    }

    /**
     * Ignore not imported annotations and not throw an exception.
     *
     * @param bool $bool
     */
    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->parser->setIgnoreNotImportedAnnotations($bool);
    }

    /**
     * Detect imports by parsing the use statements of affected files.
     *
     * @deprecated Will be removed in 3.0, imports will always be enabled.
     * @param bool $flag
     */
    public function setEnableParsePhpImports($flag)
    {
        $this->enablePhpImports = $flag;
    }

    /**
     * @deprecated Will be removed in 3.0, imports will always be enabled.
     * @return bool
     */
    public function isParsePhpImportsEnabled()
    {
        return $this->enablePhpImports;
    }

    /**
     * Sets the default namespace that the AnnotationReader should assume for annotations
     * with not fully qualified names.
     *
     * @deprecated This method will be removed in Doctrine Common 3.0
     * @param string $defaultNamespace
     */
    public function setDefaultAnnotationNamespace($defaultNamespace)
    {
        $this->defaultAnnotationNamespace = $defaultNamespace;
    }

    /**
     * Sets the custom function to use for creating new annotations on the
     * underlying parser.
     *
     * The function is supplied two arguments. The first argument is the name
     * of the annotation and the second argument an array of values for this
     * annotation. The function is assumed to return an object or NULL.
     * Whenever the function returns NULL for an annotation, the implementation falls
     * back to the default annotation creation process of the underlying parser.
     *
     * @deprecated This method will be removed in Doctrine Common 3.0
     * @param Closure $func
     */
    public function setAnnotationCreationFunction(Closure $func)
    {
        $this->parser->setAnnotationCreationFunction($func);
    }

    /**
     * Sets an alias for an annotation namespace.
     *
     * @param string $namespace
     * @param string $alias
     */
    public function setAnnotationNamespaceAlias($namespace, $alias)
    {
        $this->parser->setAnnotationNamespaceAlias($namespace, $alias);
    }

    /**
     * Gets the annotations applied to a class.
     *
     * @param string|ReflectionClass $class The name or ReflectionClass of the class from which
     * the class annotations should be read.
     * @return array An array of Annotations.
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));

        return $this->parser->parse($class->getDocComment(), 'class ' . $class->getName());
    }

    /**
     * Gets a class annotation.
     *
     * @param ReflectionClass $class The ReflectionClass of the class from which
     *                               the class annotations should be read.
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
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
     * Gets the annotations applied to a property.
     *
     * @param string|ReflectionProperty $property The name or ReflectionProperty of the property
     * from which the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $context = 'property ' . $class->getName() . "::\$" . $property->getName();
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));

        return $this->parser->parse($property->getDocComment(), $context);
    }

    /**
     * Gets a property annotation.
     *
     * @param ReflectionProperty $property
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
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
     * Gets the annotations applied to a method.
     *
     * @param ReflectionMethod $property The name or ReflectionMethod of the method from which
     * the annotations should be read.
     * @return array An array of Annotations.
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $context = 'method ' . $class->getName() . '::' . $method->getName() . '()';
        $this->parser->setImports($this->getImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));

        return $this->parser->parse($method->getDocComment(), $context);
    }

    /**
     * Gets a method annotation.
     *
     * @param ReflectionMethod $method
     * @param string $annotationName The name of the annotation.
     * @return The Annotation or NULL, if the requested annotation does not exist.
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
     * Returns the ignored annotations for the given class.
     *
     * @param ReflectionClass $class
     * @return array
     */
    private function getIgnoredAnnotationNames(ReflectionClass $class)
    {
        if (isset($this->ignoredAnnotationNames[$name = $class->getName()])) {
            return $this->ignoredAnnotationNames[$name];
        }
        $this->collectParsingMetadata($class);

        return $this->ignoredAnnotationNames[$name];
    }

    private function getImports(ReflectionClass $class)
    {
        if (isset($this->imports[$name = $class->getName()])) {
            return $this->imports[$name];
        }
        $this->collectParsingMetadata($class);

        return $this->imports[$name];
    }

    /**
     * Collects parsing metadata for a given class
     *
     * @param ReflectionClass $class
     */
    private function collectParsingMetadata(ReflectionClass $class)
    {
        $imports = self::$globalImports;
        $ignoredAnnotationNames = self::$globalIgnoredNames;

        if ($this->enablePhpImports) {
            $annotations = $this->preParser->parse($class->getDocComment());
            foreach ($annotations as $annotation) {
                if ($annotation instanceof IgnoreAnnotation) {
                    foreach ($annotation->names AS $annot) {
                        $ignoredAnnotationNames[$annot] = true;
                    }
                }
            }
        }

        $name = $class->getName();
        $this->imports[$name] = array_merge(
            self::$globalImports,
            ($this->enablePhpImports) ? $this->phpParser->parseClass($class) : array(),
            ($this->enablePhpImports) ? array('__NAMESPACE__' => $class->getNamespaceName()) : array()
        );
        if ($this->defaultAnnotationNamespace) {
            $this->imports[$name]['__DEFAULT__'] = $this->defaultAnnotationNamespace;
        }
        $this->ignoredAnnotationNames[$name] = $ignoredAnnotationNames;
    }
}
