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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

declare(strict_types=1);

namespace Doctrine\Annotations;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use Doctrine\Annotations\Parser\DocParser;
use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Annotation\IgnoreAnnotation;

/**
 * A reader for docblock annotations.
 *
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@hotmail.com>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 */
class AnnotationReader implements Reader
{
    /**
     * @var \Doctrine\Annotations\Configuration
     */
    private $config;

    /**
     * @var \Doctrine\Annotations\Parser\PhpParser
     */
    private $phpParser;

    /**
     * @var \Doctrine\Annotations\Parser\DocParser
     */
    private $docParser;

    /**
     * @var \Doctrine\Annotations\Parser\MetadataParser
     */
    private $metadataParser;

    /**
     * @var \Doctrine\Annotations\Reflection\ReflectionFactory
     */
    private $reflectionFactory;

    /**
     * @var \Doctrine\Annotations\IgnoredAnnotationNames
     */
    private $ignoredAnnotationNames;

    /**
     * Constructor.
     *
     * @param \Doctrine\Annotations\Configuration $config
     */
    public function __construct(Configuration $config = null)
    {
        $this->config                 = $config ?: new Configuration();
        $this->phpParser              = $this->config->getPhpParser();
        $this->docParser              = $this->config->getDocParser();
        $this->metadataParser         = $this->config->getMetadataParser();
        $this->reflectionFactory      = $this->config->getReflectionFactory();
        $this->ignoredAnnotationNames = $this->config->getIgnoredAnnotationNames();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class) : array
    {
        if (($docblock = $class->getDocComment()) === false) {
            return [];
        }

        $className  = $class->getName();
        $namespace  = $class->getNamespaceName();
        $reflection = $this->reflectionFactory->getReflectionClass($className);

        $imports = $reflection->getImports();
        $ignored = $this->getIgnoredAnnotationNames($class);
        $context = new Context($class, [$namespace], $imports, $ignored);

        return $this->docParser->parse($docblock, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotation(ReflectionClass $class, string $annotationName)
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
    public function getPropertyAnnotations(ReflectionProperty $property) : array
    {
        if (($docblock = $property->getDocComment()) === false) {
            return [];
        }

        $propertyName = $property->getName();
        $class        = $property->getDeclaringClass();

        $className  = $class->getName();
        $namespace  = $class->getNamespaceName();
        $reflection = $this->reflectionFactory->getReflectionProperty($className, $propertyName);

        $imports = $reflection->getImports();
        $ignored = $this->getIgnoredAnnotationNames($class);
        $context = new Context($property, [$namespace], $imports, $ignored);

        return $this->docParser->parse($docblock, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyAnnotation(ReflectionProperty $property, string $annotationName)
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
    public function getMethodAnnotations(ReflectionMethod $method) : array
    {
        if (($docblock = $method->getDocComment()) === false) {
            return [];
        }

        $methodName = $method->getName();
        $class      = $method->getDeclaringClass();

        $className  = $class->getName();
        $namespace  = $class->getNamespaceName();
        $reflection = $this->reflectionFactory->getReflectionMethod($className, $methodName);

        $imports = $reflection->getImports();
        $ignored = $this->getIgnoredAnnotationNames($class);
        $context = new Context($method, [$namespace], $imports, $ignored);

        return $this->docParser->parse($docblock, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodAnnotation(ReflectionMethod $method, string $annotationName)
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
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getIgnoredAnnotationNames(ReflectionClass $class) : array
    {
        $ignoredNames = $this->ignoredAnnotationNames->getArrayCopy();
        $annotations  = $this->metadataParser->parseAnnotationClass($class);

        foreach ($annotations as $annotation) {
            if ( ! $annotation instanceof IgnoreAnnotation) {
                continue;
            }

            foreach ($annotation->names as $name) {
                $ignoredNames[$name] = true;
            }
        }

        return $ignoredNames;
    }
}
