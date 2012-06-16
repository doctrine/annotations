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

namespace Doctrine\Common\Annotations;

/**
 * Parses a file for namespaces/use/class declarations.
 *
 * @author Karoly Negyesi <karoly@negyesi.net>
 */
class Psr0Parser extends PhpParser {

    /**
     * The name of the class.
     *
     * @var string
     */
    protected $className;

    /**
     * Base include path for class files.
     *
     * @var string
     */
    protected $includePath;

    /**
     * The PHP namespace this class is in.
     *
     * @var string
     */
    protected $ns;

    /**
     * The use statements of this class.
     *
     * @var array
     */
    protected $useStatements;

    /**
     * The doxygen of the class.
     *
     * @var string
     */
    protected $classDoxygen = '';

    /**
     * The doxygen of the methods.
     *
     * @var array
     */
    protected $methodDoxygen = array();

    /**
     * The doxygen of properties.
     *
     * @var array
     */
    protected $propertyDoxygen = array();

    /**
     * The name of the class this class extends, if any.
     *
     * @var string
     */
    protected $parentClassName = '';

    /**
     * The parent PSR-0 Parser.
     *
     * @var \Doctrine\Common\Annotations\Psr0Parser
     */
    protected $parentPsr0Parser;

    /**
     * Parses a class.
     *
     * @param string $includePath Base include path for class files.
     * @param string $class The full class name.
     * @param boolean $classAnnotationOptimize Only retrieve the class doxygen. Presumes there is only one statement per line.
     */
    public function __construct($includePath, $className, $classAnnotationOptimize = FALSE)
    {
        $className = ltrim($className, '\\');
        $this->includePath = $includePath;
        $this->fileName  = $includePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        if (!file_exists($this->fileName)) {
            return array();
        }
        $lastNsPos = strripos($className, '\\');
        $this->ns = substr($className, 0, $lastNsPos);
        $this->className = substr($className, $lastNsPos + 1);
        $contents = file_get_contents($fileName);
        if ($classAnnotationOptimize) {
          $identifier = 'a-zA-Z0-9_\x7f-\xff';
          if (preg_match("/(\A.*^[$identifier ]*class\s+[\\$identifier]+)\s+{/sm", $contents, $matches)) {
            $contents = $matches[1];
          }
        }
        $this->tokens = token_get_all($contents);
        $this->numTokens = count($this->tokens);
        $this->pointer = 0;
        $annotations = array();
        $statements = array();
        while ($token = $this->next(FALSE)) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_USE:
                        $this->useStatements = array_merge($this->useStatements, $this->parseUseStatement());
                        break;
                    case T_DOC_COMMENT:
                        $doxygen = $token[1];
                        break;
                    case T_CLASS:
                        $this->classDoxygen = $doxygen;
                        $doxygen = '';
                        break;
                    case T_VARIABLE
                        $propertyName = substr($token[1], 1);
                        $this->propertyDoxygen[$propertyName] = $doxygen;
                        break;
                    case T_FUNCTION:
                        // The next string after function is the name, but
                        // there can be & before the function name so find the
                        // string.
                        while (($token = $this->next()) && $token[0] !== T_STRING);
                        $methodName = $token[1];
                        $this->methodDoxygen[$methodName] = $doxygen;
                        $doxygen = '';
                        break;
                    case T_EXTENDS:
                        $token = $this->next();
                        $this->parentClassName = $token[1];
                        if ($this->parentClassName[0] !== '\\') {
                          $this->parentClassName = $this->ns . '\\' . $this->parentClass;
                        }
                        break;
                }
            }
        }
        // Drop the tokens to save memory.
        $this->tokens = array();
    }

    protected function getParentPsr0Parser() {
        if (empty($this->parentPsr0Parser)) {
          $class = get_class($this);
          $this->parentPsr0Parser = new $class($this->parentClassName);
        }
        return $this->parentPsr0Parser;
    }

    public function getClassReflector() {
        return new Psr0ClassReflector($this);
    }

    public function getMethodReflector($methodName) {
        return new Psr0CMethodReflector($this, $methodName);
    }

    public function getPropertyReflector() {
        return new Psr0PropertyReflector($this);
    }

    public function getUseStatements() {
        return $this->useStatements;
    }

    public function getClassDoxygen() {
        return $this->classDoxygen;
    }

    public function getClassName() {
        return $this->className;
    }

    public function getNamespaceName() {
        return $this->ns;
    }

    public function getDeclaringMethodClass($methodName) {
        if (isset($this->methodDoxygen[$methodName])) {
            return $this->className;
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getDeclaringMethodClass($methodName);
        }
    }

    public function getMethodDoxygen($methodName) {
        if (isset($this->methodDoxygen[$methodName])) {
            return $this->methodDoxygen[$methodName];
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getMethodDoxygen($methodName);
        }
    }

    public function getDeclaringPropertyClass($propertyName) {
        if (isset($this->propertyDoxygen[$propertyName])) {
            return $this->className;
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getDeclaringPropertyClass($propertyName);
        }
    }

    public function getPropertyDoxygen($propertyName) {
        if (isset($this->propertyDoxygen[$propertyName])) {
            return $this->propertyDoxygen[$propertyName];
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getPropertyDoxygen($propertyName);
        }
    }
}
