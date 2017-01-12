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

namespace Doctrine\Annotations\Reflection;

use Doctrine\Annotations\Parser\PhpParser;

/**
 * Reflection Factory
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ReflectionFactory
{
    /**
     * @var array
     */
    private $classes = [];

    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $properties = [];

    /**
     * @var array
     */
    private $functions = [];

    /**
     * @var \Doctrine\Annotations\Parser\PhpParser
     */
    private $phpParser;

    /**
     * Constructor.
     *
     * @param \Doctrine\Annotations\Parser\PhpParser $phpParser
     */
    public function __construct(PhpParser $phpParser)
    {
        $this->phpParser = $phpParser;
    }

    /**
     * @param string $className The name of the class.
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionClass
     *
     * @throws \ReflectionException
     */
    public function getReflectionClass(string $className)
    {
        if (isset($this->classes[$className])) {
            return $this->classes[$className];
        }

        return $this->classes[$className] = new ReflectionClass($className, $this->phpParser);
    }

    /**
     * @param string $className  The name of the class.
     * @param string $methodName The name of the method.
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionMethod
     *
     * @throws \ReflectionException
     */
    public function getReflectionMethod(string $className, string $methodName)
    {
        if (isset($this->methods[$className . '#' . $methodName])) {
            return $this->methods[$className . '#' . $methodName];
        }

        return $this->methods[$className . '#' . $methodName] = new ReflectionMethod($className, $methodName, $this->phpParser);
    }

    /**
     * @param string $className    The name of the class.
     * @param string $propertyName The name of the property.
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionProperty
     *
     * @throws \ReflectionException
     */
    public function getReflectionProperty(string $className, string $propertyName)
    {
        if (isset($this->properties[$className . '$' . $propertyName])) {
            return $this->properties[$className . '$' . $propertyName];
        }

        return $this->properties[$className . '$' . $propertyName] = new ReflectionProperty($className, $propertyName, $this->phpParser);
    }

    /**
     * @param string $functionName The name of the function.
     *
     * @return \Doctrine\Annotations\Reflection\ReflectionFunction
     *
     * @throws \ReflectionException
     */
    public function getReflectionFunction(string $functionName)
    {
        if (isset($this->functions[$functionName])) {
            return $this->functions[$functionName];
        }

        return $this->functions[$functionName] = new ReflectionFunction($functionName, $this->phpParser);
    }
}
