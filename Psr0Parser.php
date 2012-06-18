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
class Psr0Parser extends TokenParser
{

    /**
     * The name of the class.
     *
     * @var string
     */
    protected $className;

    /**
     * The filename of the class.
     *
     * @var string
     */
    protected $fileName;

    /**
     * TRUE if the caller only wants class annotations.
     *
     * @var boolean.
     */
    protected $classAnnotationOptimize;

    /**
     * TRUE when the parser has ran.
     *
     * @var boolean
     */
    protected $parsed;

    /**
     * The namespace of the class
     *
     * @var string
     */
    protected $ns;

    /**
     * The use statements of this class.
     *
     * @var array
     */
    protected $useStatements = array();

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
     * Parses a class residing in a PSR-0 hierarchy.
     *
     * @param string  $includePath             Base include path for class files.
     * @param string  $class                   The full class name.
     * @param boolean $classAnnotationOptimize Only retrieve the class doxygen. Presumes there is only one statement per line.
     */
    public function __construct($includePath, $className, $classAnnotationOptimize = FALSE)
    {
        $this->className = ltrim($className, '\\');
        $this->fileName  = $includePath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        $lastNsPos = strrpos($className, '\\');
        $this->ns = substr($className, 0, $lastNsPos);
        $this->classAnnotationOptimize = $classAnnotationOptimize;
    }

    protected function parse()
    {
        if ($this->parsed || !file_exists($this->fileName)) {
            return;
        }
        $this->parsed = TRUE;
        $contents = file_get_contents($this->fileName);
        if ($this->classAnnotationOptimize) {
            if (preg_match("/(\A.*)^\s+(abstract|final)?\s+class\s+$className\s+{/sm", $contents, $matches)) {
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
                    case T_VAR:
                    case T_PRIVATE:
                    case T_PROTECTED:
                    case T_PUBLIC:
                        $token = $this->next();
                        if ($token[0] === T_VARIABLE) {
                          $propertyName = substr($token[1], 1);
                          $this->propertyDoxygen[$propertyName] = $doxygen;
                          continue 2;
                        }
                        if ($token[0] !== T_FUNCTION) {
                          // For example, it can be T_FINAL.
                          continue 2;
                        }
                        // No break.
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

    protected function getParentPsr0Parser()
    {
        if (empty($this->parentPsr0Parser)) {
            $class = get_class($this);
            $this->parentPsr0Parser = new $class($this->parentClassName);
        }

        return $this->parentPsr0Parser;
    }

    public function getClassReflection()
    {
        return new Psr0ClassReflection($this);
    }

    public function getMethodReflection($methodName)
    {
        return new Psr0MethodReflection($this, $methodName);
    }

    public function getPropertyReflection($propertyName)
    {
        return new Psr0PropertyReflection($this, $propertyName);
    }

    public function getUseStatements()
    {
        $this->parse();

        return $this->useStatements;
    }

    public function getClassDoxygen()
    {
        $this->parse();

        return $this->classDoxygen;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getNamespaceName()
    {
        return $this->ns;
    }

    public function getDeclaringMethodClass($methodName)
    {
        $this->parse();
        if (isset($this->methodDoxygen[$methodName])) {
            return $this->getClassReflection($this);
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getDeclaringMethodClass($methodName);
        }
    }

    public function getMethodDoxygen($methodName)
    {
        $this->parse();
        if (isset($this->methodDoxygen[$methodName])) {
            return $this->methodDoxygen[$methodName];
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getMethodDoxygen($methodName);
        }
    }

    public function getDeclaringPropertyClass($propertyName)
    {
        $this->parse();
        if (isset($this->propertyDoxygen[$propertyName])) {
            return $this->getClassReflection($this);
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getDeclaringPropertyClass($propertyName);
        }
    }

    public function getPropertyDoxygen($propertyName)
    {
        $this->parse();
        if (isset($this->propertyDoxygen[$propertyName])) {
            return $this->propertyDoxygen[$propertyName];
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getPropertyDoxygen($propertyName);
        }
    }
}
