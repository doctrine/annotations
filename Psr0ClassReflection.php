<?php

namespace Doctrine\Common\Annotations;

use ReflectionClass;
use ReflectionException;

class Psr0ClassReflection extends ReflectionClass
{
    public function __construct($psr0Parser)
    {
        $this->psr0Parser = $psr0Parser;
    }

    public function getName()
    {
        return $this->psr0Parser->getClassName();
    }

    public function getDocComment()
    {
        return $this->psr0Parser->getClassDoxygen();
    }

    public function getNamespaceName()
    {
        return $this->psr0Parser->getNamespaceName();
    }

    public function getUseStatements()
    {
        return $this->psr0Parser->getUseStatements();
    }

    public static function export($argument, $return = FALSE) { throw new ReflectionException('Method not implemented'); }
    public function getConstant($name) { throw new ReflectionException('Method not implemented'); }
    public function getConstants() { throw new ReflectionException('Method not implemented'); }
    public function getConstructor() { throw new ReflectionException('Method not implemented'); }
    public function getDefaultProperties() { throw new ReflectionException('Method not implemented'); }
    public function getEndLine() { throw new ReflectionException('Method not implemented'); }
    public function getExtension() { throw new ReflectionException('Method not implemented'); }
    public function getExtensionName() { throw new ReflectionException('Method not implemented'); }
    public function getFileName() { throw new ReflectionException('Method not implemented'); }
    public function getInterfaceNames() { throw new ReflectionException('Method not implemented'); }
    public function getInterfaces() { throw new ReflectionException('Method not implemented'); }
    public function getMethod($name) { throw new ReflectionException('Method not implemented'); }
    public function getMethods($filter = NULL) { throw new ReflectionException('Method not implemented'); }
    public function getModifiers() { throw new ReflectionException('Method not implemented'); }
    public function getParentClass() { throw new ReflectionException('Method not implemented'); }
    public function getProperties($filter = NULL) { throw new ReflectionException('Method not implemented'); }
    public function getProperty($name) { throw new ReflectionException('Method not implemented'); }
    public function getShortName() { throw new ReflectionException('Method not implemented'); }
    public function getStartLine() { throw new ReflectionException('Method not implemented'); }
    public function getStaticProperties() { throw new ReflectionException('Method not implemented'); }
    public function getStaticPropertyValue($name, $default = '') { throw new ReflectionException('Method not implemented'); }
    public function getTraitAliases() { throw new ReflectionException('Method not implemented'); }
    public function getTraitNames() { throw new ReflectionException('Method not implemented'); }
    public function getTraits() { throw new ReflectionException('Method not implemented'); }
    public function hasConstant($name) { throw new ReflectionException('Method not implemented'); }
    public function hasMethod($name) { throw new ReflectionException('Method not implemented'); }
    public function hasProperty($name) { throw new ReflectionException('Method not implemented'); }
    public function implementsInterface($interface) { throw new ReflectionException('Method not implemented'); }
    public function inNamespace() { throw new ReflectionException('Method not implemented'); }
    public function isAbstract() { throw new ReflectionException('Method not implemented'); }
    public function isCloneable() { throw new ReflectionException('Method not implemented'); }
    public function isFinal() { throw new ReflectionException('Method not implemented'); }
    public function isInstance($object) { throw new ReflectionException('Method not implemented'); }
    public function isInstantiable() { throw new ReflectionException('Method not implemented'); }
    public function isInterface() { throw new ReflectionException('Method not implemented'); }
    public function isInternal() { throw new ReflectionException('Method not implemented'); }
    public function isIterateable() { throw new ReflectionException('Method not implemented'); }
    public function isSubclassOf($class) { throw new ReflectionException('Method not implemented'); }
    public function isTrait() { throw new ReflectionException('Method not implemented'); }
    public function isUserDefined() { throw new ReflectionException('Method not implemented'); }
    public function newInstance($args) { throw new ReflectionException('Method not implemented'); }
    public function newInstanceArgs(array $args = array()) { throw new ReflectionException('Method not implemented'); }
    public function newInstanceWithoutConstructor() { throw new ReflectionException('Method not implemented'); }
    public function setStaticPropertyValue($name, $value) { throw new ReflectionException('Method not implemented'); }
    public function __toString() { throw new ReflectionException('Method not implemented'); }
}
