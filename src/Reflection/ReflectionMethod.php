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
 * Reflection Method
 *
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
class ReflectionMethod extends \ReflectionMethod
{
    /**
     * @var \Doctrine\Annotations\Parser\PhpParser
     */
    private $phpParser;

    /**
     * @var array
     */
    private $imports;

    /**
     * @var \Doctrine\Annotations\Reflection\ReflectionClass
     */
    private $declaringClass;

    /**
     * Constructor.
     *
     * @param string                                 $className
     * @param string                                 $methodName
     * @param \Doctrine\Annotations\Parser\PhpParser $phpParser
     */
    public function __construct(string $className, string $methodName, PhpParser $phpParser)
    {
        parent::__construct($className, $methodName);

        $this->phpParser = $phpParser;
    }

    /**
     * @return array
     */
    public function getImports() : array
    {
        if ($this->imports !== null) {
            return $this->imports;
        }

        $class        = $this->getDeclaringClass();
        $classImports = $class->getImports($class);
        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ( ! $trait->hasMethod($this->getName())) {
                continue;
            }

            if ($trait->getFileName() !== $this->getFileName()) {
                continue;
            }

            $methodImports = $this->phpParser->parseClass($trait);
            $traitImports  = array_merge($traitImports, $methodImports);
        }

        return $this->imports = array_merge($classImports, $traitImports);
    }

    /**
     * @return \Doctrine\Annotations\Reflection\ReflectionClass
     */
    public function getDeclaringClass() : ReflectionClass
    {
        if ($this->declaringClass !== null) {
            return $this->declaringClass;
        }

        $className  = parent::getDeclaringClass()->name;
        $reflection = new ReflectionClass($className, $this->phpParser);

        return $this->declaringClass = $reflection;
    }
}
