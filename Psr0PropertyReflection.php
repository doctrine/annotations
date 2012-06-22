<?php

namespace Doctrine\Common\Annotations;

use ReflectionProperty;

class Psr0PropertyReflection extends ReflectionProperty
{
    /**
     * The PSR-0 parser object.
     *
     * @var Psr0Parser
     */
    protected $psr0Parser;

    /**
     * The name of the property.
     *
     * @var string
     */
    protected $propertyName;

    public function __construct($psr0Parser, $propertyName)
    {
        $this->psr0Parser = $psr0Parser;
        $this->propertyName = $propertyName;
    }
    public function getName()
    {
        return $this->propertyName;
    }
    protected function getPsr0Parser()
    {
        return $this->psr0Parser->getPsr0ParserForDeclaringClass('property', $this->propertyName);
    }
    public function getDeclaringClass()
    {
        return $this->getPsr0Parser()->getClassReflection();
    }
    public function getDocComment()
    {
        return $this->getPsr0Parser()->getDoxygen('property', $this->propertyName);
    }
    public function getUseStatements()
    {
        return $this->getPsr0Parser()->getUseStatements();
    }
    public static function export ($class, $name, $return = false) { throw new ReflectionException('Method not implemented'); }
    public function getModifiers() { throw new ReflectionException('Method not implemented'); }
    public function getValue($object = NULL) { throw new ReflectionException('Method not implemented'); }
    public function isDefault() { throw new ReflectionException('Method not implemented'); }
    public function isPrivate() { throw new ReflectionException('Method not implemented'); }
    public function isProtected() { throw new ReflectionException('Method not implemented'); }
    public function isPublic() { throw new ReflectionException('Method not implemented'); }
    public function isStatic() { throw new ReflectionException('Method not implemented'); }
    public function setAccessible ($accessible) { throw new ReflectionException('Method not implemented'); }
    public function setValue ($object, $value = NULL) { throw new ReflectionException('Method not implemented'); }
    public function __toString() { throw new ReflectionException('Method not implemented'); }
}
