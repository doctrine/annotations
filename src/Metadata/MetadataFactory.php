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

namespace Doctrine\Annotations\Metadata;

use ReflectionClass;
use ReflectionProperty;

use Doctrine\Annotations\Parser\MetadataParser;
use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Annotation;

/**
 * Annotation metadata factory
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class MetadataFactory
{
    /**
     * @var MetadataParser
     */
    private $parser;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Constructor
     *
     * @param MetadataParser $parser
     */
    public function __construct(MetadataParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Gets the class metadata descriptor for a annotation class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     *
     * @throws \Exception
     */
    public function getMetadataFor(string $className)
    {
        if (isset($this->cache[$className])) {
            return $this->cache[$className];
        }

        $class       = new \ReflectionClass($className);
        $constructor = $class->getConstructor();
        $metadata    = new ClassMetadata();

        $metadata->hasConstructor = false;
        $metadata->class          = $className;
        $metadata->target         = Target::TARGET_ALL;

        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            $metadata->hasConstructor = true;
        }

        $annotations = $this->parser->parseAnnotation($class);
        $indexed     = $this->getIndexedAnnotations($annotations);

        if ( ! $this->isAnnotation($class, $indexed)) {
            return null;
        }

        if (isset($indexed['Target'])) {
            $metadata->target = $indexed['Target']->target;
        }

        $this->collectPropertiesMetadata($class, $metadata);

        return $this->cache[$className] = $metadata;
    }

    /**
     * @param \ReflectionClass $class
     * @param array            $annotations
     *
     * @return bool
     */
    private function isAnnotation(ReflectionClass $class, array $annotations) : bool
    {
        if ($class->isSubclassOf(Annotation::CLASS)) {
            return true;
        }

        if ($class->name === Annotation::CLASS) {
            return true;
        }

        if (isset($annotations['Annotation'])) {
            return true;
        }

        return false;
    }

    /**
     * @param ReflectionClass $class
     * @param ClassMetadata   $metadata
     */
    private function collectPropertiesMetadata(ReflectionClass $class, ClassMetadata $metadata)
    {
        $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC) ;
        $result     = [];

        // collect all public properties
        foreach ($properties as $property) {
            $result[$property->name] = $this->collectPropertyMetadata($property);
        }

        $metadata->properties      = $result;
        $metadata->defaultProperty = key($result);
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return array
     */
    private function collectPropertyMetadata(ReflectionProperty $property)
    {
        $annotations = $this->parser->parseAnnotationProperty($property);
        $indexed     = $this->getIndexedAnnotations($annotations);
        $metadata = [
            'name'       => $property->name,
            'required'   => false,
            'enum'       => null,
            'array_type' => null,
            'type'       => null
        ];

        if (isset($indexed['Required'])) {
            $metadata['required'] = true;
        }

        if (isset($indexed['Type'])) {
            $metadata['type']         = $indexed['Type']->type;
            $metadata['array_type']   = $indexed['Type']->arrayType;
        }

        if (isset($indexed['Enum'])) {
            $metadata['enum']['value']   = $indexed['Enum']->value;
            $metadata['enum']['literal'] = ( ! empty($indexed['Enum']->literal))
                ? $indexed['Enum']->literal
                : $indexed['Enum']->value;
        }

        return $metadata;
    }

    /**
     * @param array $annotations
     *
     * @return array
     */
    private function getIndexedAnnotations(array $annotations) : array
    {
        $result = [];

        foreach ($annotations as $annotation) {
            $class = get_class($annotation);
            $index = strrpos($class, '\\') + 1;
            $name  = substr($class, $index);

            $result[$name] = $annotation;
        }

        return $result;
    }
}
