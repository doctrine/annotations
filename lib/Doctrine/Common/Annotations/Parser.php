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

use Closure, Doctrine\Common\ClassLoader;

/**
 * A simple parser for docblock annotations.
 *
 * This Parser can be subclassed to customize certain aspects of the annotation
 * parsing and/or creation process. Note though that currently no special care
 * is taken to maintain full backwards compatibility for subclasses. Implementation
 * details of the default Parser can change without explicit notice.
 *
 * @since 2.0
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class Parser
{
    /**
     * Some common tags that are stripped prior to parsing in order to reduce parsing overhead.
     *
     * @var array
     */
    private static $strippedTags = array(
        "{@internal", "{@inheritdoc", "{@link"
    );

    /**
     * The lexer.
     *
     * @var Doctrine\Common\Annotations\Lexer
     */
    private $lexer;

    /**
     * Flag to control if the current annotation is nested or not.
     *
     * @var boolean
     */
    protected $isNestedAnnotation = false;

    /**
     * Default namespace for annotations.
     *
     * @var string
     */
    private $defaultAnnotationNamespace = '';

    /**
     * Hashmap to store namespace aliases.
     *
     * @var array
     */
    private $namespaceAliases = array();

    /**
     * @var string
     */
    private $context = '';

    /**
     * @var boolean Whether to try to autoload annotations that are not yet defined.
     */
    private $autoloadAnnotations = false;

    /**
     * @var Closure The custom function used to create new annotations, if any.
     */
    private $annotationCreationFunction;

    /**
     * Constructs a new AnnotationParser.
     */
    public function __construct(Lexer $lexer = null)
    {
        $this->lexer = $lexer ?: new Lexer;
    }

    /**
     * Gets the lexer used by this parser.
     * 
     * @return Lexer The lexer.
     */
    public function getLexer()
    {
        return $this->lexer;
    }

    /**
     * Sets a flag whether to try to autoload annotation classes, as well as to distinguish
     * between what is an annotation and what not by triggering autoloading.
     *
     * NOTE: Autoloading of annotation classes is inefficient and requires silently failing
     *       autoloaders. In particular, setting this option to TRUE renders the Parser
     *       incompatible with a {@link ClassLoader}.
     * @param boolean $bool Boolean flag.
     */
    public function setAutoloadAnnotations($bool)
    {
        $this->autoloadAnnotations = $bool;
    }

    /**
     * Sets the custom function to use for creating new annotations.
     *
     * The function is supplied two arguments. The first argument is the name
     * of the annotation and the second argument an array of values for this
     * annotation. The function is assumed to return an object or NULL.
     * Whenever the function returns NULL for an annotation, the parser falls
     * back to the default annotation creation process.
     *
     * Whenever the function returns NULL for an annotation, the implementation falls
     * back to the default annotation creation process.
     *
     * @param Closure $func
     */
    public function setAnnotationCreationFunction(Closure $func)
    {
        $this->annotationCreationFunction = $func;
    }

    /**
     * Gets a flag whether to try to autoload annotation classes.
     *
     * @see setAutoloadAnnotations
     * @return boolean
     */
    public function getAutoloadAnnotations()
    {
        return $this->autoloadAnnotations;
    }

    /**
     * Sets the default namespace that is assumed for an annotation that does not
     * define a namespace prefix.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultAnnotationNamespace($defaultNamespace)
    {
        $this->defaultAnnotationNamespace = $defaultNamespace;
    }

    /**
     * Sets an alias for an annotation namespace.
     *
     * @param string $namespace
     * @param string $alias
     */
    public function setAnnotationNamespaceAlias($namespace, $alias)
    {
        $this->namespaceAliases[$alias] = $namespace;
    }

    /**
     * Gets the namespace alias mappings used by this parser.
     *
     * @return array The namespace alias mappings.
     */
    public function getNamespaceAliases()
    {
        return $this->namespaceAliases;
    }

    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $docBlockString The docblock string to parse.
     * @param string $context The parsing context.
     * @return array Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($docBlockString, $context='')
    {
        $this->context = $context;

        // Strip out some known inline tags.
        $input = str_replace(self::$strippedTags, '', $docBlockString);

        // Cut of the beginning of the input until the first '@'.
        $input = substr($input, strpos($input, '@'));

        $this->lexer->reset();
        $this->lexer->setInput(trim($input, '* /'));
        $this->lexer->moveNext();

        if ($this->lexer->isNextToken(Lexer::T_AT)) {
            return $this->Annotations();
        }

        return array();
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param int Token type.
     * @return bool True if tokens match; false otherwise.
     */
    public function match($token)
    {
        if ( ! ($this->lexer->lookahead['type'] === $token)) {
            $this->syntaxError($this->lexer->getLiteral($token));
        }
        $this->lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string $expected Expected string.
     * @param array $token Optional token.
     * @throws AnnotationException
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
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @return array
     */
    public function Annotations()
    {
        $this->isNestedAnnotation = false;

        $annotations = array();
        $annot = $this->Annotation();

        if ($annot !== false) {
            $annotations[get_class($annot)] = $annot;
            $this->lexer->skipUntil(Lexer::T_AT);
        }

        while ($this->lexer->lookahead !== null && $this->lexer->isNextToken(Lexer::T_AT)) {
            $this->isNestedAnnotation = false;
            $annot = $this->Annotation();

            if ($annot !== false) {
                $annotations[get_class($annot)] = $annot;
                $this->lexer->skipUntil(Lexer::T_AT);
            }
        }

        return $annotations;
    }

    /**
     * Annotation     ::= "@" AnnotationName ["(" [Values] ")"]
     * AnnotationName ::= QualifiedName | SimpleName | AliasedName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * AliasedName    ::= Alias ":" SimpleName
     * NameSpacePart  ::= identifier
     * SimpleName     ::= identifier
     * Alias          ::= identifier
     *
     * @return mixed False if it is not a valid annotation.
     */
    public function Annotation()
    {
        $values = array();
        $nameParts = array();

        $this->match(Lexer::T_AT);
        $this->match(Lexer::T_IDENTIFIER);
        $nameParts[] = $this->lexer->token['value'];

        while ($this->lexer->isNextToken(Lexer::T_NAMESPACE_SEPARATOR)) {
            $this->match(Lexer::T_NAMESPACE_SEPARATOR);
            $this->match(Lexer::T_IDENTIFIER);
            $nameParts[] = $this->lexer->token['value'];
        }

        // Effectively pick the name of the class (append default NS if none, grab from NS alias, etc)
        if (strpos($nameParts[0], ':')) {
            list ($alias, $nameParts[0]) = explode(':', $nameParts[0]);

            // If the namespace alias doesnt exist, skip until next annotation
            if ( ! isset($this->namespaceAliases[$alias])) {
                $this->lexer->skipUntil(Lexer::T_AT);
                return false;
            }

            $name = $this->namespaceAliases[$alias] . implode('\\', $nameParts);
        } else if (count($nameParts) == 1) {
            $name = $this->defaultAnnotationNamespace . $nameParts[0];
        } else {
            $name = implode('\\', $nameParts);
        }

        // Does the annotation class exist?
        if ( ! class_exists($name, $this->autoloadAnnotations)) {
            $this->lexer->skipUntil(Lexer::T_AT);
            return false;
        }

        // Next will be nested
        $this->isNestedAnnotation = true;

        if ($this->lexer->isNextToken(Lexer::T_OPEN_PARENTHESIS)) {
            $this->match(Lexer::T_OPEN_PARENTHESIS);

            if ( ! $this->lexer->isNextToken(Lexer::T_CLOSE_PARENTHESIS)) {
                $values = $this->Values();
            }

            $this->match(Lexer::T_CLOSE_PARENTHESIS);
        }

        if ($this->annotationCreationFunction !== null) {
            $func = $this->annotationCreationFunction;
            $annot = $func($name, $values);
        }

        return isset($annot) ? $annot : $this->newAnnotation($name, $values);
    }

    /**
     * Values ::= Array | Value {"," Value}*
     *
     * @return array
     */
    public function Values()
    {
        $values = array();

        // Handle the case of a single array as value, i.e. @Foo({....})
        if ($this->lexer->isNextToken(Lexer::T_OPEN_CURLY_BRACES)) {
            $values['value'] = $this->Value();
            return $values;
        }

        $values[] = $this->Value();

        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $value = $this->Value();

            if ( ! is_array($value)) {
                $this->syntaxError('Value', $value);
            }

            $values[] = $value;
        }

        foreach ($values as $k => $value) {
            if (is_array($value) && is_string(key($value))) {
                $key = key($value);
                $values[$key] = $value[$key];
            } else {
                $values['value'] = $value;
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
    public function Value()
    {
        $peek = $this->lexer->glimpse();

        if ($peek['value'] === '=') {
            return $this->FieldAssignment();
        }

        return $this->PlainValue();
    }

    /**
     * PlainValue ::= integer | string | float | boolean | Array | Annotation
     *
     * @return mixed
     */
    public function PlainValue()
    {
        if ($this->lexer->isNextToken(Lexer::T_OPEN_CURLY_BRACES)) {
            return $this->Arrayx();
        }

        if ($this->lexer->isNextToken(Lexer::T_AT)) {
            return $this->Annotation();
        }

        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_STRING:
                $this->match(Lexer::T_STRING);
                return $this->lexer->token['value'];

            case Lexer::T_INTEGER:
                $this->match(Lexer::T_INTEGER);
                return $this->lexer->token['value'];

            case Lexer::T_FLOAT:
                $this->match(Lexer::T_FLOAT);
                return $this->lexer->token['value'];

            case Lexer::T_TRUE:
                $this->match(Lexer::T_TRUE);
                return true;

            case Lexer::T_FALSE:
                $this->match(Lexer::T_FALSE);
                return false;

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
    public function FieldAssignment()
    {
        $this->match(Lexer::T_IDENTIFIER);
        $fieldName = $this->lexer->token['value'];
        $this->match(Lexer::T_EQUALS);

        return array($fieldName => $this->PlainValue());
    }

    /**
     * Array ::= "{" ArrayEntry {"," ArrayEntry}* "}"
     *
     * @return array
     */
    public function Arrayx()
    {
        $array = $values = array();

        $this->match(Lexer::T_OPEN_CURLY_BRACES);
        $values[] = $this->ArrayEntry();

        while ($this->lexer->isNextToken(Lexer::T_COMMA)) {
            $this->match(Lexer::T_COMMA);
            $values[] = $this->ArrayEntry();
        }

        $this->match(Lexer::T_CLOSE_CURLY_BRACES);

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
    public function ArrayEntry()
    {
        $peek = $this->lexer->glimpse();

        if ($peek['value'] == '=') {
            $this->match(
                $this->lexer->isNextToken(Lexer::T_INTEGER) ? Lexer::T_INTEGER : Lexer::T_STRING
            );

            $key = $this->lexer->token['value'];
            $this->match(Lexer::T_EQUALS);

            return array($key, $this->PlainValue());
        }

        return array(null, $this->Value());
    }

    /**
     * Constructs a new annotation with a given map of values.
     *
     * The default construction procedure is to instantiate a new object of a class
     * with the same name as the annotation. Subclasses can override this method to
     * change the construction process of new annotations.
     *
     * @param string The name of the annotation.
     * @param array The map of annotation values.
     * @return mixed The new annotation with the given values.
     */
    protected function newAnnotation($name, array $values)
    {
        return new $name($values);
    }
}