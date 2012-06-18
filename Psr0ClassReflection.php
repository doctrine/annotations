<?php

namespace Doctrine\Common\Annotations;

use ReflectionClass;
use ReflectionException;

class Psr0ClassReflection extends ReflectionClass {
    function __construct($psr0Parser) {
        $this->psr0Parser = $psr0Parser;
    }

    public function getName() {
        return $this->psr0Parser->getClassName();
    }

    public function getDocComment() {
        return $this->psr0Parser->getClassDoxygen();
    }

    public function getNamespaceName() {
        return $this->psr0Parser->getNamespaceName();
    }

    public function getUseStatements() {
        return $this->psr0Parser->getUseStatements();
    }

    static function export($argument, $return = FALSE) { throw new ReflectionException('Method not implemented'); }
    function getConstant($name) { throw new ReflectionException('Method not implemented'); }
    function getConstants() { throw new ReflectionException('Method not implemented'); }
    function getConstructor() { throw new ReflectionException('Method not implemented'); }
    function getDefaultProperties() { throw new ReflectionException('Method not implemented'); }
    function getEndLine() { throw new ReflectionException('Method not implemented'); }
    function getExtension() { throw new ReflectionException('Method not implemented'); }
    function getExtensionName() { throw new ReflectionException('Method not implemented'); }
    function getFileName() { throw new ReflectionException('Method not implemented'); }
    function getInterfaceNames() { throw new ReflectionException('Method not implemented'); }
    function getInterfaces() { throw new ReflectionException('Method not implemented'); }
    function getMethod($name) { throw new ReflectionException('Method not implemented'); }
    function getMethods($filter = NULL) { throw new ReflectionException('Method not implemented'); }
    function getModifiers() { throw new ReflectionException('Method not implemented'); }
    function getParentClass() { throw new ReflectionException('Method not implemented'); }
    function getProperties($filter = NULL) { throw new ReflectionException('Method not implemented'); }
    function getProperty($name) { throw new ReflectionException('Method not implemented'); }
    function getShortName() { throw new ReflectionException('Method not implemented'); }
    function getStartLine() { throw new ReflectionException('Method not implemented'); }
    function getStaticProperties() { throw new ReflectionException('Method not implemented'); }
    function getStaticPropertyValue($name, $default = '') { throw new ReflectionException('Method not implemented'); }
    function getTraitAliases() { throw new ReflectionException('Method not implemented'); }
    function getTraitNames() { throw new ReflectionException('Method not implemented'); }
    function getTraits() { throw new ReflectionException('Method not implemented'); }
    function hasConstant($name) { throw new ReflectionException('Method not implemented'); }
    function hasMethod($name) { throw new ReflectionException('Method not implemented'); }
    function hasProperty($name) { throw new ReflectionException('Method not implemented'); }
    function implementsInterface($interface) { throw new ReflectionException('Method not implemented'); }
    function inNamespace() { throw new ReflectionException('Method not implemented'); }
    function isAbstract() { throw new ReflectionException('Method not implemented'); }
    function isCloneable() { throw new ReflectionException('Method not implemented'); }
    function isFinal() { throw new ReflectionException('Method not implemented'); }
    function isInstance($object) { throw new ReflectionException('Method not implemented'); }
    function isInstantiable() { throw new ReflectionException('Method not implemented'); }
    function isInterface() { throw new ReflectionException('Method not implemented'); }
    function isInternal() { throw new ReflectionException('Method not implemented'); }
    function isIterateable() { throw new ReflectionException('Method not implemented'); }
    function isSubclassOf($class) { throw new ReflectionException('Method not implemented'); }
    function isTrait() { throw new ReflectionException('Method not implemented'); }
    function isUserDefined() { throw new ReflectionException('Method not implemented'); }
    function newInstance($args) { throw new ReflectionException('Method not implemented'); }
    function newInstanceArgs(array $args = array()) { throw new ReflectionException('Method not implemented'); }
    function newInstanceWithoutConstructor() { throw new ReflectionException('Method not implemented'); }
    function setStaticPropertyValue($name, $value) { throw new ReflectionException('Method not implemented'); }
    function __toString() { throw new ReflectionException('Method not implemented'); }
}
