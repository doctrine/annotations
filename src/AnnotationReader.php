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
     * Annotations parser.
     *
     * @var \Doctrine\Annotations\Configuration
     */
    private $config;

    /**
     * Constructor.
     *
     * @param \Doctrine\Annotations\Configuration $config
     */
    public function __construct(Configuration $config = null)
    {
        $this->config = $config ?: new Configuration();
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class) : array
    {
        $docblock  = $class->getDocComment();
        $namespace = $class->getNamespaceName();
        $imports   = $this->getClassImports($class);
        $ignored   = $this->getIgnoredAnnotationNames($class);
        $context   = new Context($class, [$namespace], $imports, $ignored);

        if ($docblock === false) {
            return [];
        }

        $parser = new DocParser($this->config->getHoaParser(), $this->config->getBuilder());
        $result = $parser->parse($docblock, $context);

        return $result;
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
    public function getPropertyAnnotations(ReflectionProperty $property) : array
    {
        $class     = $property->getDeclaringClass();
        $docblock  = $property->getDocComment();
        $namespace = $class->getNamespaceName();
        $imports   = $this->getPropertyImports($property);
        $ignored   = $this->getIgnoredAnnotationNames($class);
        $context   = new Context($property, [$namespace], $imports, $ignored);

        if ($docblock === false) {
            return [];
        }

        $parser = new DocParser($this->config->getHoaParser(), $this->config->getBuilder());
        $result = $parser->parse($docblock, $context);

        return $result;
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
    public function getMethodAnnotations(ReflectionMethod $method) : array
    {
        $class     = $method->getDeclaringClass();
        $docblock  = $method->getDocComment();
        $namespace = $class->getNamespaceName();
        $imports   = $this->getMethodImports($method);
        $ignored   = $this->getIgnoredAnnotationNames($class);
        $context   = new Context($method, [$namespace], $imports, $ignored);

        if ($docblock === false) {
            return [];
        }

        $parser = new DocParser($this->config->getHoaParser(), $this->config->getBuilder());
        $result = $parser->parse($docblock, $context);

        return $result;
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
     * Returns the ignored annotations for the given class.
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getIgnoredAnnotationNames(ReflectionClass $class) : array
    {
        $parser       = new MetadataParser($this->config->getHoaParser(), $this->config->getResolver());
        $ignoredNames = $this->config->getIgnoredAnnotationNames()->getArrayCopy();
        $annotations  = $parser->parseAnnotationClass($class);

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

    /**
     * Retrieves imports.
     *
     * @param \ReflectionClass $class
     *
     * @return array
     */
    private function getClassImports(ReflectionClass $class)
    {
        if (isset($this->imports[$name = $class->getName()])) {
            return $this->imports[$name];
        }

        $parser = $this->config->getPhpParser();
        $result = $parser->parseClass($class);

        return $this->imports[$name] = $result;
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
        $class        = $method->getDeclaringClass();
        $parser       = $this->config->getPhpParser();
        $classImports = $this->getClassImports($class);
        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ( ! $trait->hasMethod($method->getName())) {
                continue;
            }

            if ($trait->getFileName() !== $method->getFileName()) {
                continue;
            }

            $traitImports = array_merge($traitImports, $parser->parseClass($trait));
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
        $parser       = $this->config->getPhpParser();
        $class        = $property->getDeclaringClass();
        $classImports = $this->getClassImports($class);
        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ( ! $trait->hasProperty($property->getName())) {
                continue;
            }

            $traitImports = array_merge($traitImports, $parser->parseClass($trait));
        }

        return array_merge($classImports, $traitImports);
    }
}