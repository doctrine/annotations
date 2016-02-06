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

use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Metadata\ClassMetadata;
use Doctrine\Annotations\Metadata\MetadataFactory;
use Doctrine\Annotations\Exception\TypeMismatchException;
use Doctrine\Annotations\Exception\TargetNotAllowedException;

/**
 * Build annotations objects
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class Builder
{
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * Constructor.
     *
     * @param Resolver        $resolver
     * @param MetadataFactory $metadataFactory
     */
    public final function __construct(Resolver $resolver, MetadataFactory $metadataFactory)
    {
        $this->resolver        = $resolver;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @return Doctrine\Annotations\Resolver
     */
    public function getResolver() : Resolver
    {
        return $this->resolver;
    }

    /**
     * @param Context   $context
     * @param Reference $reference
     *
     * @return object
     */
    public function create(Context $context, Reference $reference)
    {
        return $this->createAnotation($context, $reference, $context->getTarget());
    }

    /**
     * @param Context   $context
     * @param Reference $reference
     * @param integer   $target
     *
     * @return object
     */
    private function createAnotation(Context $context, Reference $reference, int $target)
    {
        $fullClass = $this->resolver->resolve($context, $reference->name);
        $metadata  = $this->metadataFactory->getMetadataFor($fullClass);
        $values    = $reference->values;

        if (($metadata->target & $target) === 0) {
            $contextDesc   = $context->getDescription();
            $allowedTarget = implode(',', Target::getNames($metadata->target));

            throw TargetNotAllowedException::notAllowedDeclaration($fullClass, $contextDesc, $allowedTarget);
        }

        return $this->instantiate($context, $metadata, $values);
    }

    /**
     * @param Context       $context
     * @param ClassMetadata $metadata
     * @param array         $values
     *
     * @return object
     */
    private function instantiate(Context $context, ClassMetadata $metadata, array $values)
    {
        $this->assertPropertyTypes($context, $metadata, $values);

        $className  = $metadata->class;
        $annotation = $metadata->hasConstructor
            ? new $className($values)
            : new $className();

        if ( ! $metadata->hasConstructor) {
            $this->injectValues($annotation, $context, $metadata, $values);
        }

        return $annotation;
    }

    /**
     * @param object        $annotation
     * @param Context       $context
     * @param ClassMetadata $metadata
     * @param array         $values
     */
    private function injectValues($annotation, Context $context, ClassMetadata $metadata, array $values)
    {
        $properties      = $metadata->properties;
        $defaultProperty = $metadata->defaultProperty;
        $propertyNames   = array_keys($metadata->properties);

        foreach ($values as $property => $value) {

            if (isset($properties[$property])) {
                $annotation->{$property} = $value;

                continue;
            }

            if ($property !== 'value') {
                throw new \RuntimeException(sprintf(
                    'The annotation @%s declared on %s does not have a property named "%s". Available properties: %s',
                    $metadata->class,
                    $context->getDescription(),
                    $property,
                    implode(', ', $propertyNames)
                ));
            }

            // handle the case if the property has no annotations
            if ( ! $defaultProperty) {
                throw new \RuntimeException(sprintf(
                    'The annotation @%s declared on %s does not accept any values, but got %s.',
                    $metadata->class,
                    $context->getDescription(),
                    json_encode($values)
                ));
            }

            $annotation->{$defaultProperty} = $value;
        }
    }



    /**
     * @param Context       $context
     * @param ClassMetadata $metadata
     * @param array         $values
     *
     * @return object
     */
    private function assertPropertyTypes(Context $context, ClassMetadata $metadata, array $values)
    {
        $properties      = $metadata->properties;
        $defaultProperty = $metadata->defaultProperty;

        // checks all declared attributes
        foreach ($metadata->properties as $propertyName => $property) {

            if ($propertyName === $defaultProperty && ! isset($values[$propertyName]) && isset($values['value'])) {
                $propertyName = 'value';
            }

            // handle a not given attribute
            if ( ! isset($values[$propertyName]) && $property['required']) {
                throw AnnotationException::requiredError($propertyName, $metadata->class, $this->context, 'a(n) '.$property['value']);
            }

            // null values
            if ( ! isset($values[$propertyName])) {
                continue;
            }

            // checks if the attribute is a valid enumerator
            if (isset($property['enum']) && ! in_array($values[$propertyName], $property['enum']['value'])) {
                throw TypeMismatchException::enumeratorError(
                    $propertyName,
                    $metadata->class,
                    $context->getDescription(),
                    $property['enum']['literal'],
                    $values[$propertyName]
                );
            }

            // mixed values
            if (! isset($property['type']) || $property['type'] === 'mixed') {
                continue;
            }

            if ($property['type'] === 'array') {
                // handle the case of a single value
                if ( ! is_array($values[$propertyName])) {
                    $values[$propertyName] = array($values[$propertyName]);
                }

                // checks if the attribute has array type declaration, such as "array<string>"
                if ( ! isset($property['array_type'])) {
                    continue;
                }

                foreach ($values[$propertyName] as $item) {
                    if (gettype($item) !== $property['array_type'] && ! $item instanceof $property['array_type']) {
                        throw TypeMismatchException::attributeTypeError(
                            $propertyName,
                            $metadata->class,
                            $context->getDescription(),
                            'either a(n) ' . $property['array_type'] . ', or an array of ' . $property['array_type'] . 's',
                            $item
                        );
                    }
                }

                continue;
            }

            if (gettype($values[$propertyName]) !== $property['type'] && ! $values[$propertyName] instanceof $property['type']) {
                throw TypeMismatchException::attributeTypeError(
                    $propertyName,
                    $metadata->class,
                    $context->getDescription(),
                    'a(n) ' . $property['type'],
                    $values[$propertyName]
                );
            }
        }
    }
}
