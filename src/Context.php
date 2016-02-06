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

use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Doctrine\Annotations\Annotation\Target;

/**
 * Annotation parsing context.
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class Context
{
    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * @var array
     */
    private $ignoredNames = [];

    /**
     * Property, method or class been parsed
     *
     * @var \Reflector
     */
    private $reflection;

    /**
     * The namespace name.
     *
     * @var string
     */
    private $namespace;

    /**
     * A map with use statements in the form (Alias => FQN).
     *
     * @var array
     */
    private $imports;

    /**
     * Constructor.
     *
     * @param Reflector $reflection
     * @param string    $namespace
     * @param array     $imports
     * @param array     $ignoredNames
     */
    public final function __construct(Reflector $reflection, string $namespace, array $imports = [], array $ignoredNames = [])
    {
        $this->ignoredNames = $ignoredNames;
        $this->reflection   = $reflection;
        $this->namespace    = $namespace;
        $this->imports      = $imports;
    }

    /**
     * @return array
     */
    public function getIgnoredNames() : array
    {
        return $this->ignoredNames;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function isIgnoredName($name) : bool
    {
        return isset($this->ignoredNames[$name]);
    }

    /**
     * @return \Reflector
     */
    public function getReflection() : Reflector
    {
        return $this->reflection;
    }

    /**
     * @return string
     */
    public function getNamespace() : string
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getImports() : array
    {
        return $this->imports;
    }

    /**
     * @return int
     */
    public function getTarget() : int
    {
        if ($this->reflection instanceof ReflectionClass) {
            return Target::TARGET_CLASS;
        }

        if ($this->reflection instanceof ReflectionMethod) {
            return Target::TARGET_METHOD;
        }

        if ($this->reflection instanceof ReflectionProperty) {
            return Target::TARGET_PROPERTY;
        }

        throw new \RuntimeException('Unsupported target : ' . get_class($this->reflection));
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        if ($this->reflection instanceof ReflectionClass) {
            $name    = $this->reflection->getName();
            $context = 'class ' . $name;

            return $context;
        }

        if ($this->reflection instanceof ReflectionMethod) {
            $name    = $this->reflection->getName();
            $class   = $this->reflection->getDeclaringClass();
            $context = 'method ' . $class->getName() . '::' . $name . '()';

            return $context;
        }

        if ($this->reflection instanceof ReflectionProperty) {
            $name    = $this->reflection->getName();
            $class   = $this->reflection->getDeclaringClass();
            $context = 'property ' . $class->getName() . '::$' . $name;

            return $context;
        }

        throw new \RuntimeException('Unsupported target : ' . get_class($this->reflection));
    }
}
