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

use ReflectionException;

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
     * The short name of the class (the part after the namespace).
     *
     * @var string
     */
    protected $classShortName = '';

    /**
     * The filename of the class.
     *
     * @var string
     */
    protected $fileName = '';

    /**
     * The include paths.
     *
     * @var string
     */
    protected $includePaths;

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
    protected $parsed = FALSE;

    /**
     * The namespace of the class
     *
     * @var string
     */
    protected $ns = '';

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
    protected $doxygen = array(
        'class' => '',
        'property' => array(),
        'method' => array(),
    );

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
     * @param string $class
     *     The full, namespaced class name.
     * @param string $includePaths
     *     An array of base include paths. Each key is a PHP namespace and
     *     each value is a list of directories.
     * @param boolean $classAnnotationOptimize
     *     Only retrieve the class doxygen. Presumes there is only one
     *     statement per line.
     */
    public function __construct($className, $includePaths, $classAnnotationOptimize = FALSE)
    {
        $this->className = ltrim($className, '\\');
        $this->includePaths  = $includePaths;
        if ($lastNsPos = strrpos($this->className, '\\')) {
            $this->classShortName = substr($this->className, $lastNsPos + 1);
            $this->ns = substr($this->className, 0, $lastNsPos);
        }
        $this->classAnnotationOptimize = $classAnnotationOptimize;
    }

    protected function parse()
    {
        if ($this->parsed || !$this->fileName = $this->findClassFile($this->includePaths, $this->ns, $this->classShortName)) {
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
        $doxygen = '';
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
                        $this->doxygen['class'] = $doxygen;
                        $doxygen = '';
                        break;
                    case T_VAR:
                    case T_PRIVATE:
                    case T_PROTECTED:
                    case T_PUBLIC:
                        $token = $this->next();
                        if ($token[0] === T_VARIABLE) {
                            $propertyName = substr($token[1], 1);
                            $this->doxygen['property'][$propertyName] = $doxygen;
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
                        $this->doxygen['method'][$methodName] = $doxygen;
                        $doxygen = '';
                        break;
                    case T_EXTENDS:
                        $this->parentClassName = '';
                        while (($token = $this->next()) && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
                            $this->parentClassName .= $token[1];
                        }
                        $nsPos = strpos($this->parentClassName, '\\');
                        $fullySpecified = FALSE;
                        if ($nsPos === 0) {
                            $fullySpecified = TRUE;
                        } else {
                            if ($nsPos) {
                                $prefix = strtolower(substr($this->parentClassName, 0, $nsPos));
                                $postfix = substr($this->parentClassName, $nsPos);
                            } else {
                                $prefix = strtolower($this->parentClassName);
                                $postfix = '';
                            }
                            foreach ($this->useStatements as $alias => $use) {
                                if ($alias == $prefix) {
                                    $this->parentClassName = '\\' . $use . $postfix;
                                    $fullySpecified = TRUE;
                              }
                            }
                        }
                        if (!$fullySpecified) {
                            $this->parentClassName = '\\' . $this->ns . '\\' . $this->parentClassName;
                        }
                        break;
                }
            }
        }
        // Drop the tokens to save memory.
        $this->tokens = array();
    }

    protected function findClassFile($includePaths, $namespace, $classShortName)
    {
        $normalizedClass = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.$classShortName.'.php';
        foreach ($includePaths as $ns => $dirs) {
            if (strpos($namespace, $ns) === 0) {
                foreach ($dirs as $dir) {
                    $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }
        }
    }

    protected function getParentPsr0Parser()
    {
        if (empty($this->parentPsr0Parser)) {
            $class = get_class($this);
            $this->parentPsr0Parser = new $class($this->parentClassName, $this->includePaths);
        }

        return $this->parentPsr0Parser;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getNamespaceName()
    {
        return $this->ns;
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

    public function getDoxygen($type = 'class', $name = '')
    {
        $this->parse();

        return $name ? $this->doxygen[$type][$name] : $this->doxygen[$type];
    }

    public function getPsr0ParserFor($type, $name)
    {
        $this->parse();
        if (isset($this->doxygen[$type][$name])) {
            return $this;
        }
        if (!empty($this->parentClassName)) {
            return $this->getParentPsr0Parser()->getPsr0ParserFor($type, $name);
        }
        throw new ReflectionException('Invalid ' . $type . ' "' . $name . '"');
    }
}
