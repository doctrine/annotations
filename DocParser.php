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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

use Closure;
use ReflectionClass;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * A parser for docblock annotations.
 *
 * It is strongly discouraged to change the default annotation parsing process.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Fabio B. Silva <fabio.bat.silva@gmail.com>
 */
final class DocParser
{
    /**
     * An array of all valid tokens for a class name.
     *
     * @var array
     */
    private static $classIdentifiers = array(DocLexer::T_IDENTIFIER, DocLexer::T_TRUE, DocLexer::T_FALSE, DocLexer::T_NULL);

    /**
     * The lexer.
     *
     * @var Doctrine\Common\Annotations\DocLexer
     */
    private $lexer;
    
    /**
     * Current target context
     * 
     * @var string
     */
    private $target;
    
    /**
     * Doc Parser used to collect annotation target
     *
     * @var Doctrine\Common\Annotations\DocParser
     */
    private static $preParser;

    /**
     * Flag to control if the current annotation is nested or not.
     *
     * @var boolean
     */
    private $isNestedAnnotation = false;

    /**
     * Hashmap containing all use-statements that are to be used when parsing
     * the given doc block.
     *
     * @var array
     */
    private $imports = array();

    /**
     * This hashmap is used internally to cache results of class_exists()
     * look-ups.
     *
     * @var array
     */
    private $classExists = array();

    /**
     * Whether annotations that have not been imported should be ignored.
     *
     * @var boolean
     */
    private $ignoreNotImportedAnnotations = false;

    /**
     * An array of default namespaces if operating in simple mode.
     *
     * @var array
     */
    private $namespaces = array();

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     * class names.
     *
     * @var array
     */
    private $ignoredAnnotationNames = array();

    /**
     * @var string
     */
    private $context = '';

    /**
     * Hash-map for caching annotation classes to avoid reparsing their doc-blocks
     * @var array
     */
    private static $isAnnotation = array();

    /**
     * @var Hash-map of ReflectionClass
     */
    private static $annotations = array();

    /**
     * @var Hash-map for caching to avoid reparsing class properties.
     */
    private static $properties = array();

    /**
     * @var Hash-map for caching to avoid reparsing.
     */
    private static $hasProperty = array();

    /**
     * @var Hash-map for caching to avoid reparsing class constructor.
     */
    private static $hasConstructor = array();
    
     /**
     * @var Hash-map for caching to avoid reparsing  annotation class target.
     */
    private static $checkTarget = array();

    /**
     * Constructs a new DocParser.
     */
    public function __construct()
    {
        $this->lexer = new DocLexer;
    }

    /**
     * Sets the annotation names that are ignored during the parsing process.
     *
     * The names are supposed to be the raw names as used in the class, not the
     * fully qualified class names.
     *
     * @param array $names
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->ignoreNotImportedAnnotations = (Boolean) $bool;
    }

    /**
     * Sets the default namespaces.
     * @param array $namespaces
     */
    public function addNamespace($namespace)
    {
        if ($this->imports) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }
        $this->namespaces[] = $namespace;
    }

    public function setImports(array $imports)
    {
        if ($this->namespaces) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }
        $this->imports = $imports;
    }
    
     /**
     * Sets current target context that is being parsed.
     * 
     * @param string $target 
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $input The docblock string to parse.
     * @param string $context The parsing context.
     * @return array Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($input, $context = '')
    {
        if (false === $pos = strpos($input, '@')) {
            return array();
        }

        // also parse whatever character is before the @
        if ($pos > 0) {
            $pos -= 1;
        }

        $this->context = $context;
        $this->lexer->setInput(trim(substr($input, $pos), '* /'));
        $this->lexer->moveNext();

        return $this->Annotations();
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param int Token type.
     * @return bool True if tokens match; false otherwise.
     */
    private function match($token)
    {
        if ( ! $this->lexer->isNextToken($token) ) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }

        return $this->lexer->moveNext();
    }

    /**
     * Attempts to match the current lookahead token with any of the given tokens.
     *
     * If any of them matches, this method updates the lookahead token; otherwise
     * a syntax error is raised.
     *
     * @param array $tokens
     * @return bool
     */
    private function matchAny(array $tokens)
    {
        if ( ! $this->lexer->isNextTokenAny($tokens)) {
            $this->syntaxError(implode(' or ', array_map(array($this->lexer, 'getLiteral'), $tokens)));
        }

        return $this->lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string $expected Expected string.
     * @param array $token Optional token.
     * @throws SyntaxException
     */
    private function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $message =  "Expected {$expected}, got ";

        if ($this->lexer->lookahead === null) {
            $message .= 'end of string';
        } else {
            $message .= "'{$token['value']}' at position {$token['position']}";
        }

        if (strlen($this->context)) {
            $message .= ' in ' . $this->context;
        }

        $message .= '.';

        throw AnnotationException::syntaxError($message);
    }

    /**
     * Attempt to check if a class exists or not. This never goes through the PHP autoloading mechanism
     * but uses the {@link AnnotationRegistry} to load classes.
     *
     * @param string $fqcn
     * @return boolean
     */
    private function classExists($fqcn)
    {
        if (isset($this->classExists[$fqcn])) {
            return $this->classExists[$fqcn];
        }

        // first check if the class already exists, maybe loaded through another AnnotationReader
        if (class_exists($fqcn, false)) {
            return $this->classExists[$fqcn] = true;
        }

        // final check, does this class exist?
        return $this->classExists[$fqcn] = AnnotationRegistry::loadAnnotationClass($fqcn);
    }
    
    /**
     * Checks if the annotation can be used in the current target.
     * 
     * @param   string $name        The annotation name
     * @throws  AnnotationException Throws an AnnotationException if the target is invalid
     */
    private function checkTarget($name)
    {
        if (false !== $pos = strpos($input = $this->getAnnotationClass($name)->getDocComment(), '@Target')) {
            
            $parser  = self::$preParser ?: $this->getPreParser();
            $context = 'class @' . $name;
            
            foreach ($parser->parse($input,$context) as $annotation) {
                if($annotation instanceof Target){
                    
                    //if not all targets
                    //and anotation does not support current target
                    if (!in_array(Target::TARGET_ALL, $annotation->value) && !in_array($this->target, $annotation->value)) {
                        throw AnnotationException::creationError(
                            sprintf('Declaration of "@%s" is not compatible with annotation target [%s], %s.', 
                                $name, implode(", ", $annotation->value), $this->context)
                        );
                    }
                }
            }
        }
        self::$checkTarget[$this->context][$name] = true;
    }
    
    /**
     * Returns the DocParser used to collect annotation target
     * 
     * @return DocParser
     */
    private function getPreParser()
    {
        if(self::$preParser == null){
            self::$preParser = new self();
            self::$preParser->setIgnoreNotImportedAnnotations(true);
            self::$preParser->setImports(array(
                'target' => 'Doctrine\Common\Annotations\Annotation\Target'
            ));
            AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Target.php');
        }
        return self::$preParser;
    }

    /**
     * Returns a ReflectionClass from hash-map or creates if does not exist.
     * 
     * @param  string $className    The annotation name
     * @return \ReflectionClass
     */
    private function getAnnotationClass($className)
    {
        if (!isset(self::$annotations[$className])) {
            self::$annotations[$className] = new \ReflectionClass($className);
        }

        return self::$annotations[$className];
    }

    /**
     * Returns an array with the names of class properties
     * 
     * @param    string $className  The annotation name
     * @return   array 
     */
    private function getProperties($className)
    {
        if (!isset(self::$properties[$className])) {
            self::$properties[$className] = array();

            foreach ($this->getAnnotationClass($className)->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                self::$properties[$className][] = $property->name;
            }
        }

        return self::$properties[$className];
    }

    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @return array
     */
    private function Annotations()
    {
        $annotations = array();

        while (null !== $this->lexer->lookahead) {
            if (DocLexer::T_AT !== $this->lexer->lookahead['type']) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is preceded by non-catchable pattern
            if (null !== $this->lexer->token && $this->lexer->lookahead['position'] === $this->lexer->token['position'] + strlen($this->lexer->token['value'])) {
                $this->lexer->moveNext();
                continue;
            }

            // make sure the @ is followed by either a namespace separator, or
            // an identifier token
            if ((null === $peek = $this->lexer->glimpse())
                || (DocLexer::T_NAMESPACE_SEPARATOR !== $peek['type'] && !in_array($peek['type'], self::$classIdentifiers, true))
                || $peek['position'] !== $this->lexer->lookahead['position'] + 1) {
                $this->lexer->moveNext();
                continue;
            }

            $this->isNestedAnnotation = false;
            if (false !== $annot = $this->Annotation()) {
                $annotations[] = $annot;
            }
        }

        return $annotations;
    }

    /**
     * Annotation     ::= "@" AnnotationName ["(" [Values] ")"]
     * AnnotationName ::= QualifiedName | SimpleName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * NameSpacePart  ::= identifier | null | false | true
     * SimpleName     ::= identifier | null | false | true
     *
     * @return mixed False if it is not a valid annotation.
     */
    private function Annotation()
    {
        $this->match(DocLexer::T_AT);

        // check if we have an annotation
        if ($this->lexer->isNextTokenAny(self::$classIdentifiers)) {
            $this->lexer->moveNext();
            $name = $this->lexer->token['value'];
        } else if ($this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)) {
            $name = '';
        } else {
            $this->syntaxError('namespace separator or identifier');
        }

        while ($this->lexer->lookahead['position'] === $this->lexer->token['position'] + strlen($this->lexer->token['value']) && $this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)) {
            $this->match(DocLexer::T_NAMESPACE_SEPARATOR);
            $this->matchAny(self::$classIdentifiers);
            $name .= '\\'.$this->lexer->token['value'];
        }

        // only process names which are not fully qualified, yet
        $originalName = $name;
        if ('\\' !== $name[0] && !$this->classExists($name)) {
            $alias = (false === $pos = strpos($name, '\\'))? $name : substr($name, 0, $pos);

            $found = false;
            if ($this->namespaces) {
                foreach ($this->namespaces as $namespace) {
                    if ($this->classExists($namespace.'\\'.$name)) {
                        $name = $namespace.'\\'.$name;
                        $found = true;
                        break;
                    }
                }
            } elseif (isset($this->imports[$loweredAlias = strtolower($alias)])) {
                if (false !== $pos) {
                    $name = $this->imports[$loweredAlias].substr($name, $pos);
                } else {
                    $name = $this->imports[$loweredAlias];
                }
                $found = true;
            } elseif (isset($this->imports['__NAMESPACE__']) && $this->classExists($this->imports['__NAMESPACE__'].'\\'.$name)) {
                 $name = $this->imports['__NAMESPACE__'].'\\'.$name;
                 $found = true;
            }

            if (!$found) {
                if ($this->ignoreNotImportedAnnotations || isset($this->ignoredAnnotationNames[$name])) {
                    return false;
                }

                throw AnnotationException::semanticalError(sprintf('The annotation "@%s" in %s was never imported.', $name, $this->context));
            }
        }

        if (!$this->classExists($name)) {
            throw AnnotationException::semanticalError(sprintf('The annotation "@%s" in %s does not exist, or could not be auto-loaded.', $name, $this->context));
        }

        // at this point, $name contains the fully qualified class name of the
        // annotation, and it is also guaranteed that this class exists, and
        // that it is loaded

        // verify that the class is really meant to be an annotation and not just any ordinary class
        if (!isset(self::$isAnnotation[$name])) {

            if (false === strpos($this->getAnnotationClass($name)->getDocComment(), '@Annotation')) {
                if (isset($this->ignoredAnnotationNames[$originalName])) {
                    return false;
                }

                throw AnnotationException::semanticalError(sprintf('The class "%s" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "%s". If it is indeed no annotation, then you need to add @IgnoreAnnotation("%s") to the _class_ doc comment of %s.', $name, $name, $originalName, $this->context));
            }
            self::$isAnnotation[$name] = true;
        }

        // Next will be nested
        $this->isNestedAnnotation = true;
        
        //if the annotation has not yet been verified in the current context
        if (!isset(self::$checkTarget[$this->context][$name]) && $this->target && $this->context){
            $this->checkTarget($name);
        }

        $values = array();
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
            $this->match(DocLexer::T_OPEN_PARENTHESIS);

            if ( ! $this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
                $values = $this->Values();
            }

            $this->match(DocLexer::T_CLOSE_PARENTHESIS);
        }

        if (!isset(self::$hasConstructor[$name])) {
            $constructor = $this->getAnnotationClass($name)->getConstructor();
            self::$hasConstructor[$name] = null !== $constructor && $constructor->getNumberOfParameters() > 0;
        }

        // check if the annotation expects values via the constructor,
        // or directly injected into public properties
        if (self::$hasConstructor[$name]) {
            return new $name($values);
        }

        $instance = new $name();
        foreach ($values as $property => $value) {
            if (!isset(self::$hasProperty[$name][$property])) {
                $refClass = $this->getAnnotationClass($name);
                self::$hasProperty[$name][$property] = $refClass->hasProperty($property) && $refClass->getProperty($property)->isPublic();
            }

            if (!self::$hasProperty[$name][$property]) {
                if ('value' !== $property) {
                    throw AnnotationException::creationError(sprintf('The annotation @%s declared on %s does not have a property named "%s". Available properties: %s', $originalName, $this->context, $property, implode(', ', $this->getProperties($name))));
                }

                // handle the case if the property has no annotations
                if (!$properties = $this->getProperties($name)) {
                    throw AnnotationException::creationError(sprintf('The annotation @%s declared on %s does not accept any values, but got %s.', $originalName, $this->context, json_encode($values)));
                }

                // choose the first property as default property
                $property   = reset($properties);
            }

            $instance->{$property} = $value;
        }

        return $instance;
    }

    /**
     * Values ::= Array | Value {"," Value}*
     *
     * @return array
     */
    private function Values()
    {
        $values = array();

        // Handle the case of a single array as value, i.e. @Foo({....})
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_CURLY_BRACES)) {
            $values['value'] = $this->Value();
            return $values;
        }

        $values[] = $this->Value();

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->match(DocLexer::T_COMMA);
            $token = $this->lexer->lookahead;
            $value = $this->Value();

            if ( ! is_object($value) && ! is_array($value)) {
                $this->syntaxError('Value', $token);
            }

            $values[] = $value;
        }

        foreach ($values as $k => $value) {
            if (is_object($value) && $value instanceof \stdClass) {
                $values[$value->name] = $value->value;
            } else if ( ! isset($values['value'])){
                $values['value'] = $value;
            } else {
                if ( ! is_array($values['value'])) {
                    $values['value'] = array($values['value']);
                }

                $values['value'][] = $value;
            }

            unset($values[$k]);
        }

        return $values;
    }

    /**
     * Value ::= PlainValue | FieldAssignment
     *
     * @return mixed
     */
    private function Value()
    {
        $peek = $this->lexer->glimpse();

        if (DocLexer::T_EQUALS === $peek['type']) {
            return $this->FieldAssignment();
        }

        return $this->PlainValue();
    }

    /**
     * PlainValue ::= integer | string | float | boolean | Array | Annotation
     *
     * @return mixed
     */
    private function PlainValue()
    {
        if ($this->lexer->isNextToken(DocLexer::T_OPEN_CURLY_BRACES)) {
            return $this->Arrayx();
        }

        if ($this->lexer->isNextToken(DocLexer::T_AT)) {
            return $this->Annotation();
        }

        switch ($this->lexer->lookahead['type']) {
            case DocLexer::T_STRING:
                $this->match(DocLexer::T_STRING);
                return $this->lexer->token['value'];

            case DocLexer::T_INTEGER:
                $this->match(DocLexer::T_INTEGER);
                return (int)$this->lexer->token['value'];

            case DocLexer::T_FLOAT:
                $this->match(DocLexer::T_FLOAT);
                return (float)$this->lexer->token['value'];

            case DocLexer::T_TRUE:
                $this->match(DocLexer::T_TRUE);
                return true;

            case DocLexer::T_FALSE:
                $this->match(DocLexer::T_FALSE);
                return false;

            case DocLexer::T_NULL:
                $this->match(DocLexer::T_NULL);
                return null;

            default:
                $this->syntaxError('PlainValue');
        }
    }

    /**
     * FieldAssignment ::= FieldName "=" PlainValue
     * FieldName ::= identifier
     *
     * @return array
     */
    private function FieldAssignment()
    {
        $this->match(DocLexer::T_IDENTIFIER);
        $fieldName = $this->lexer->token['value'];

        $this->match(DocLexer::T_EQUALS);

        $item = new \stdClass();
        $item->name  = $fieldName;
        $item->value = $this->PlainValue();

        return $item;
    }

    /**
     * Array ::= "{" ArrayEntry {"," ArrayEntry}* [","] "}"
     *
     * @return array
     */
    private function Arrayx()
    {
        $array = $values = array();

        $this->match(DocLexer::T_OPEN_CURLY_BRACES);
        $values[] = $this->ArrayEntry();

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->match(DocLexer::T_COMMA);

            // optional trailing comma
            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
                break;
            }

            $values[] = $this->ArrayEntry();
        }

        $this->match(DocLexer::T_CLOSE_CURLY_BRACES);

        foreach ($values as $value) {
            list ($key, $val) = $value;

            if ($key !== null) {
                $array[$key] = $val;
            } else {
                $array[] = $val;
            }
        }

        return $array;
    }

    /**
     * ArrayEntry ::= Value | KeyValuePair
     * KeyValuePair ::= Key "=" PlainValue
     * Key ::= string | integer
     *
     * @return array
     */
    private function ArrayEntry()
    {
        $peek = $this->lexer->glimpse();

        if (DocLexer::T_EQUALS === $peek['type']) {
            $this->match(
                $this->lexer->isNextToken(DocLexer::T_INTEGER) ? DocLexer::T_INTEGER : DocLexer::T_STRING
            );

            $key = $this->lexer->token['value'];
            $this->match(DocLexer::T_EQUALS);

            return array($key, $this->PlainValue());
        }

        return array(null, $this->Value());
    }
}