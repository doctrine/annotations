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

use Doctrine\Common\Annotations\Annotation\ParseAnnotation;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Closure;
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
class Reader implements ReaderInterface
{
    /**
     * Global map for imports.
     *
     * @var array
     */
    private static $globalImports = array(
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation',
        'parseannotation'  => 'Doctrine\Common\Annotations\Annotation\ParseAnnotation',
    );

    /**
     * A list of globally ignored annotation names.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNames = array(
        'access', 'author', 'copyright', 'deprecated', 'example', 'ignore',
        'internal', 'link', 'see', 'since', 'tutorial', 'version', 'package',
        'subpackage', 'name', 'global', 'param', 'return', 'staticvar',
        'static', 'var', 'throws', 'inheritdoc',
    );

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
     * Constructor. Initializes a new AnnotationReader that uses the given Cache provider.
     *
     * @param DocParser $parser The parser to use. If none is provided, the default parser is used.
     */
    public function __construct(DocParser $parser = null)
    {
        $this->parser = $parser ?: new DocParser;

        $this->preParser = new DocParser();
        $this->preParser->setImports(self::$globalImports);
        $this->preParser->setIgnoreNotImportedAnnotations(true);

        $this->phpParser = new PhpParser();
    }

    /**
     * Sets a flag whether to auto-load annotation classes or not.
     *
     * NOTE: It is recommended to turn auto-loading on if your auto-loader
     *       supports silent failing. For this reason, setting this to TRUE
     *       renders the parser incompatible with {@link ClassLoader}.
     *
     * @param boolean $bool Boolean flag.
     */
    public function setAutoloadAnnotations($bool)
    {
        $this->parser->setAutoloadAnnotations($bool);
    }

    /**
     * Gets a flag whether to try to autoload annotation classes.
     *
     * @see setAutoloadAnnotations
     * @return boolean
     */
    public function isAutoloadAnnotations()
    {
        return $this->parser->isAutoloadAnnotations();
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

        $annotations = $this->preParser->parse($class->getDocComment());
        foreach ($annotations as $annotation) {
            if ($annotation instanceof IgnoreAnnotation) {
                $ignoredAnnotationNames = array_merge($ignoredAnnotationNames, $annotation->names);
            } else if ($annotation instanceof ParseAnnotation) {
                $ignoredAnnotationNames = array_diff($ignoredAnnotationNames, $annotation->names);
            }
        }

        $name = $class->getName();
        $this->imports[$name] = array_merge(
            self::$globalImports,
            $this->phpParser->parseClass($class),
            array('__NAMESPACE__' => $class->getNamespaceName())
        );
        $this->ignoredAnnotationNames[$name] = array_unique($ignoredAnnotationNames);
    }
}
