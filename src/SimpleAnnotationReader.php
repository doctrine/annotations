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

namespace Doctrine\Annotations;

use Doctrine\Annotations\Parser\DocParser;

use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Simple Annotation Reader.
 *
 * This annotation reader is intended to be used in projects where you have
 * full-control over all annotations that are available.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class SimpleAnnotationReader implements Reader
{
    /**
     * @var DocParser
     */
    private $parser;

    /**
     * Annotations parser.
     *
     * @var \Doctrine\Annotations\Configuration
     */
    private $config;

    /**
     * List of namespaces.
     *
     * @var string[]
     */
    private $namespaces;

    /**
     * Constructor.
     *
     * @param \Doctrine\Annotations\Configuration $config
     * @param array                               $namespaces
     */
    public function __construct(Configuration $config = null, array $namespaces = [])
    {
        $this->namespaces = $namespaces;
        $this->config     = $config ?: new Configuration();
    }

    /**
     * Adds a namespace in which we will look for annotations.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function addNamespace(string $namespace)
    {
        $this->namespaces[] = $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassAnnotations(ReflectionClass $class) : array
    {
        $namespace = $class->getNamespaceName();
        $docblock  = $class->getDocComment();

        return $this->parse($class, $namespace, $docblock);
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
        $namespace = $class->getNamespaceName();
        $docblock  = $property->getDocComment();

        return $this->parse($property, $namespace, $docblock);
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
        $namespace = $class->getNamespaceName();
        $docblock  = $method->getDocComment();

        return $this->parse($method, $namespace, $docblock);
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
     * @param \Reflector  $reflector
     * @param string      $namespace
     * @param string|bool $docblock
     *
     * @return array
     */
    private function parse(Reflector $reflector, $namespace, $docblock) : array
    {
        if ($docblock === false) {
            return [];
        }

        $parser     = new DocParser($this->config->getHoaParser(), $this->config->getBuilder(), true);
        $namespaces = array_merge($this->namespaces, [$namespace]);
        $context    = new Context($reflector, $namespaces);

        return $parser->parse($docblock, $context);
    }
}
