<?php

namespace Doctrine\Annotations;

use Doctrine\Annotations\Annotation\Attribute;
use Doctrine\Annotations\Metadata\AnnotationTarget;
use Doctrine\Annotations\Metadata\Builder\AnnotationMetadataBuilder;
use Doctrine\Annotations\Metadata\Builder\PropertyMetadataBuilder;
use Doctrine\Annotations\Metadata\InternalAnnotations;
use Doctrine\Annotations\Metadata\MetadataCollection;
use Doctrine\Annotations\Metadata\TransientMetadataCollection;
use Doctrine\Annotations\Type\ArrayType;
use Doctrine\Annotations\Type\BooleanType;
use Doctrine\Annotations\Type\FloatType;
use Doctrine\Annotations\Type\IntegerType;
use Doctrine\Annotations\Type\MixedType;
use Doctrine\Annotations\Type\ObjectType;
use Doctrine\Annotations\Type\StringType;
use Doctrine\Annotations\Type\Type;
use ReflectionClass;
use Doctrine\Annotations\Annotation\Enum;
use Doctrine\Annotations\Annotation\Target;
use Doctrine\Annotations\Annotation\Attributes;
use function array_key_exists;
use function array_keys;

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
    private static $classIdentifiers = [
        DocLexer::T_IDENTIFIER,
        DocLexer::T_TRUE,
        DocLexer::T_FALSE,
        DocLexer::T_NULL
    ];

    /**
     * The lexer.
     *
     * @var \Doctrine\Annotations\DocLexer
     */
    private $lexer;

    /**
     * Current target context.
     *
     * @var integer
     */
    private $target;

    /**
     * Doc parser used to collect annotation target.
     *
     * @var \Doctrine\Annotations\DocParser
     */
    private static $metadataParser;

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
    private $imports = [];

    /**
     * This hashmap is used internally to cache results of class_exists()
     * look-ups.
     *
     * @var array
     */
    private $classExists = [];

    /**
     * Whether annotations that have not been imported should be ignored.
     *
     * @var boolean
     */
    private $ignoreNotImportedAnnotations = false;

    /**
     * An array of default namespaces if operating in simple mode.
     *
     * @var string[]
     */
    private $namespaces = [];

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names must be the raw names as used in the class, not the fully qualified
     * class names.
     *
     * @var bool[] indexed by annotation name
     */
    private $ignoredAnnotationNames = [];

    /**
     * A list with annotations in namespaced format
     * that are not causing exceptions when not resolved to an annotation class.
     *
     * @var bool[] indexed by namespace name
     */
    private $ignoredAnnotationNamespaces = [];

    /**
     * @var string
     */
    private $context = '';

    /**
     * Hash-map for caching annotation metadata.
     *
     * @var MetadataCollection
     */
    private $metadata;

    /** @var array<string, bool> */
    private $nonAnnotationClasses = [];

    /**
     * Constructs a new DocParser.
     */
    public function __construct()
    {
        $this->lexer    = new DocLexer;
        $this->metadata = InternalAnnotations::createMetadata();
    }

    /**
     * Sets the annotation names that are ignored during the parsing process.
     *
     * The names are supposed to be the raw names as used in the class, not the
     * fully qualified class names.
     *
     * @param bool[] $names indexed by annotation name
     *
     * @return void
     */
    public function setIgnoredAnnotationNames(array $names)
    {
        $this->ignoredAnnotationNames = $names;
    }

    /**
     * Sets the annotation namespaces that are ignored during the parsing process.
     *
     * @param bool[] $ignoredAnnotationNamespaces indexed by annotation namespace name
     *
     * @return void
     */
    public function setIgnoredAnnotationNamespaces($ignoredAnnotationNamespaces)
    {
        $this->ignoredAnnotationNamespaces = $ignoredAnnotationNamespaces;
    }

    /**
     * Sets ignore on not-imported annotations.
     *
     * @param boolean $bool
     *
     * @return void
     */
    public function setIgnoreNotImportedAnnotations($bool)
    {
        $this->ignoreNotImportedAnnotations = (boolean) $bool;
    }

    /**
     * Sets the default namespaces.
     *
     * @param string $namespace
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function addNamespace($namespace)
    {
        if ($this->imports) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }

        $this->namespaces[] = $namespace;
    }

    /**
     * Sets the imports.
     *
     * @param array $imports
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function setImports(array $imports)
    {
        if ($this->namespaces) {
            throw new \RuntimeException('You must either use addNamespace(), or setImports(), but not both.');
        }

        $this->imports = $imports;
    }

    /**
     * Sets current target context as bitmask.
     *
     * @param integer $target
     *
     * @return void
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Parses the given docblock string for annotations.
     *
     * @param string $input   The docblock string to parse.
     * @param string $context The parsing context.
     *
     * @return array Array of annotations. If no annotations are found, an empty array is returned.
     */
    public function parse($input, $context = '')
    {
        $pos = $this->findInitialTokenPosition($input);
        if ($pos === null) {
            return [];
        }

        $this->context = $context;

        $this->lexer->setInput(trim(substr($input, $pos), '* /'));
        $this->lexer->moveNext();

        return $this->Annotations();
    }

    /**
     * Finds the first valid annotation
     *
     * @param string $input The docblock string to parse
     *
     * @return int|null
     */
    private function findInitialTokenPosition($input)
    {
        $pos = 0;

        // search for first valid annotation
        while (($pos = strpos($input, '@', $pos)) !== false) {
            $preceding = substr($input, $pos - 1, 1);

            // if the @ is preceded by a space, a tab or * it is valid
            if ($pos === 0 || $preceding === ' ' || $preceding === '*' || $preceding === "\t") {
                return $pos;
            }

            $pos++;
        }

        return null;
    }

    /**
     * Attempts to match the given token with the current lookahead token.
     * If they match, updates the lookahead token; otherwise raises a syntax error.
     *
     * @param integer $token Type of token.
     *
     * @return boolean True if tokens match; false otherwise.
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
     *
     * @return boolean
     */
    private function matchAny(array $tokens)
    {
        if ( ! $this->lexer->isNextTokenAny($tokens)) {
            $this->syntaxError(implode(' or ', array_map([$this->lexer, 'getLiteral'], $tokens)));
        }

        return $this->lexer->moveNext();
    }

    /**
     * Generates a new syntax error.
     *
     * @param string     $expected Expected string.
     * @param array|null $token    Optional token.
     *
     * @return void
     *
     * @throws AnnotationException
     */
    private function syntaxError($expected, $token = null)
    {
        if ($token === null) {
            $token = $this->lexer->lookahead;
        }

        $message  = sprintf('Expected %s, got ', $expected);
        $message .= ($this->lexer->lookahead === null)
            ? 'end of string'
            : sprintf("'%s' at position %s", $token['value'], $token['position']);

        if (strlen($this->context)) {
            $message .= ' in ' . $this->context;
        }

        $message .= '.';

        throw AnnotationException::syntaxError($message);
    }

    /**
     * Attempts to check if a class exists or not. This always uses PHP autoloading mechanism.
     *
     * @param string $fqcn
     *
     * @return boolean
     */
    private function classExists($fqcn)
    {
        if (isset($this->classExists[$fqcn])) {
            return $this->classExists[$fqcn];
        }

        // final check, does this class exist?
        return $this->classExists[$fqcn] = class_exists($fqcn);
    }

    /**
     * Collects parsing metadata for a given annotation class
     *
     * @param string $name The annotation name
     *
     * @return void
     */
    private function collectAnnotationMetadata($name)
    {
        if (self::$metadataParser === null) {
            self::$metadataParser = new self();

            self::$metadataParser->setIgnoreNotImportedAnnotations(true);
            self::$metadataParser->setIgnoredAnnotationNames($this->ignoredAnnotationNames);
            self::$metadataParser->setImports([
                'enum'          => 'Doctrine\Annotations\Annotation\Enum',
                'target'        => 'Doctrine\Annotations\Annotation\Target',
                'attribute'     => 'Doctrine\Annotations\Annotation\Attribute',
                'attributes'    => 'Doctrine\Annotations\Annotation\Attributes'
            ]);
        }

        $class      = new ReflectionClass($name);
        $docComment = $class->getDocComment();

        // verify that the class is really meant to be an annotation
        if (strpos($docComment, '@Annotation') === false) {
            $this->nonAnnotationClasses[$name] = true;
            return;
        }

        $constructor       = $class->getConstructor();
        $useConstructor    = $constructor !== null && $constructor->getNumberOfParameters() > 0;
        $annotationBuilder = new AnnotationMetadataBuilder($name);

        if ($useConstructor) {
            $annotationBuilder->withUsingConstructor();
        }

        self::$metadataParser->setTarget(Target::TARGET_CLASS);

        foreach (self::$metadataParser->parse($docComment, 'class @' . $name) as $annotation) {
            if ($annotation instanceof Target) {
                $annotationBuilder->withTarget(AnnotationTarget::fromAnnotation($annotation));

                continue;
            }

            if ($annotation instanceof Attributes) {
                foreach ($annotation->value as $attribute) {
                    $propertyBuilder = new PropertyMetadataBuilder($attribute->name);

                    $this->collectAttributeTypeMetadata($propertyBuilder, $attribute);
                    $annotationBuilder->withProperty($propertyBuilder->build());
                }
            }
        }

        if ($useConstructor) {
            $this->metadata->add($annotationBuilder->build());

            return;
        }

        // if there is no constructor we will inject values into public properties

        // collect all public properties
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $i => $property) {
            $propertyBuilder = new PropertyMetadataBuilder($property->getName());
            $propertyComment = $property->getDocComment();

            if ($i === 0) {
                $propertyBuilder->withBeingDefault();
            }


            if ($propertyComment === false) {
                $annotationBuilder->withProperty($propertyBuilder->build());

                continue;
            }

            $attribute           = new Attribute();
            $attribute->required = (false !== strpos($propertyComment, '@Required'));
            $attribute->name     = $property->name;
            $attribute->type     = (false !== strpos($propertyComment, '@var') && preg_match('/@var\s+([^\s]+)/',$propertyComment, $matches))
                ? $matches[1]
                : 'mixed';

            $this->collectAttributeTypeMetadata($propertyBuilder, $attribute);

            // checks if the property has @Enum
            if (false !== strpos($propertyComment, '@Enum')) {
                $context = 'property ' . $class->name . "::\$" . $property->name;

                self::$metadataParser->setTarget(Target::TARGET_PROPERTY);

                foreach (self::$metadataParser->parse($propertyComment, $context) as $annotation) {
                    if ( ! $annotation instanceof Enum) {
                        continue;
                    }

                    $propertyBuilder = $propertyBuilder->withEnum([
                        'value'   => $annotation->value,
                        'literal' => ( ! empty($annotation->literal))
                            ? $annotation->literal
                            : $annotation->value,
                    ]);
                }
            }

            $annotationBuilder->withProperty($propertyBuilder->build());
        }

        $this->metadata->add($annotationBuilder->build());
    }

    /**
     * Collects parsing metadata for a given attribute.
     */
    private function collectAttributeTypeMetadata(
        PropertyMetadataBuilder $metadata,
        Attribute $attribute
    ) : void {
        $type = $attribute->type;

        // handle the case if the property type is mixed
        if ($type === 'mixed') {
            return;
        }

        if ($attribute->required) {
            $metadata->withBeingRequired();
        }

        // Evaluate type

        // Checks if the property has array<type>
        if (false !== $pos = strpos($type, '<')) {
            $arrayType = substr($type, $pos + 1, -1);

            $metadata->withType(new ArrayType(new MixedType(), $this->createTypeFromName($arrayType)));

            return;
        }

        // Checks if the property has type[]
         if (false !== $pos = strrpos($type, '[')) {
            $arrayType = substr($type, 0, $pos);

            $metadata->withType(new ArrayType(new MixedType(), $this->createTypeFromName($arrayType)));

            return;
        }

        $metadata->withType($this->createTypeFromName($attribute->type));
    }

    private function createTypeFromName(string $name) : Type
    {
        if ($name === 'bool' || $name === 'boolean' || $name === 'Boolean') {
            return new BooleanType();
        }
        if ($name === 'int' || $name === 'integer') {
            return new IntegerType();
        }
        if ($name === 'float' || $name === 'double') {
            return new FloatType();
        }
        if ($name === 'string') {
            return new StringType();
        }
        if ($name === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($name === 'object') {
            return new ObjectType(null);
        }

        return new ObjectType($name);
    }

    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @return array
     */
    private function Annotations()
    {
        $annotations = [];

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
     * Annotation     ::= "@" AnnotationName MethodCall
     * AnnotationName ::= QualifiedName | SimpleName
     * QualifiedName  ::= NameSpacePart "\" {NameSpacePart "\"}* SimpleName
     * NameSpacePart  ::= identifier | null | false | true
     * SimpleName     ::= identifier | null | false | true
     *
     * @return mixed False if it is not a valid annotation.
     *
     * @throws AnnotationException
     */
    private function Annotation()
    {
        $this->match(DocLexer::T_AT);

        // check if we have an annotation
        $name = $this->Identifier();

        if ($this->lexer->isNextToken(DocLexer::T_MINUS)
            && $this->lexer->nextTokenIsAdjacent()
        ) {
            // Annotations with dashes, such as "@foo-" or "@foo-bar", are to be discarded
            return false;
        }

        // only process names which are not fully qualified, yet
        // fully qualified names must start with a \
        $originalName = $name;

        if ('\\' !== $name[0]) {
            $pos = strpos($name, '\\');
            $alias = (false === $pos)? $name : substr($name, 0, $pos);
            $found = false;
            $loweredAlias = strtolower($alias);

            if ($this->namespaces) {
                foreach ($this->namespaces as $namespace) {
                    if ($this->classExists($namespace.'\\'.$name)) {
                        $name = $namespace.'\\'.$name;
                        $found = true;
                        break;
                    }
                }
            } elseif (isset($this->imports[$loweredAlias])) {
                $found = true;
                $name  = (false !== $pos)
                    ? $this->imports[$loweredAlias] . substr($name, $pos)
                    : $this->imports[$loweredAlias];
            } elseif ( ! isset($this->ignoredAnnotationNames[$name])
                && isset($this->imports['__NAMESPACE__'])
                && $this->classExists($this->imports['__NAMESPACE__'] . '\\' . $name)
            ) {
                $name  = $this->imports['__NAMESPACE__'].'\\'.$name;
                $found = true;
            } elseif (! isset($this->ignoredAnnotationNames[$name]) && $this->classExists($name)) {
                $found = true;
            }

            if ( ! $found) {
                if ($this->isIgnoredAnnotation($name)) {
                    return false;
                }

                throw AnnotationException::semanticalError(sprintf('The annotation "@%s" in %s was never imported. Did you maybe forget to add a "use" statement for this annotation?', $name, $this->context));
            }
        }

        $name = ltrim($name,'\\');

        if ( ! $this->classExists($name)) {
            throw AnnotationException::semanticalError(sprintf('The annotation "@%s" in %s does not exist, or could not be auto-loaded.', $name, $this->context));
        }

        // at this point, $name contains the fully qualified class name of the
        // annotation, and it is also guaranteed that this class exists, and
        // that it is loaded


        // collects the metadata annotation only if there is not yet
        if (! $this->metadata->has($name) && ! array_key_exists($name, $this->nonAnnotationClasses)) {
            $this->collectAnnotationMetadata($name);
        }

        // verify that the class is really meant to be an annotation and not just any ordinary class
        if (array_key_exists($name, $this->nonAnnotationClasses)) {
            if ($this->ignoreNotImportedAnnotations || isset($this->ignoredAnnotationNames[$originalName])) {
                return false;
            }

            throw AnnotationException::semanticalError(sprintf('The class "%s" is not annotated with @Annotation. Are you sure this class can be used as annotation? If so, then you need to add @Annotation to the _class_ doc comment of "%s". If it is indeed no annotation, then you need to add @IgnoreAnnotation("%s") to the _class_ doc comment of %s.', $name, $name, $originalName, $this->context));
        }

        //if target is nested annotation
        $target = $this->isNestedAnnotation ? Target::TARGET_ANNOTATION : $this->target;

        // Next will be nested
        $this->isNestedAnnotation = true;
        $metadata                 = $this->metadata->get($name);

        //if annotation does not support current target
        if (($metadata->getTarget()->unwrap() & $target) === 0 && $target) {
            throw AnnotationException::semanticalError(
                sprintf('Annotation @%s is not allowed to be declared on %s. You may only use this annotation on these code elements: %s.',
                     $originalName, $this->context, $metadata->getTarget()->describe())
            );
        }

        $values = $this->MethodCall();

        // checks all declared attributes for enums
        foreach ($metadata->getProperties() as $property) {
            $propertyName = $property->getName();
            $enum         = $property->getEnum();

            // checks if the attribute is a valid enumerator
            if ($enum !== null && isset($values[$propertyName]) && ! in_array($values[$propertyName], $enum['value'])) {
                throw AnnotationException::enumeratorError($propertyName, $name, $this->context, $enum['literal'], $values[$propertyName]);
            }
        }

        // checks all declared attributes
        foreach ($metadata->getProperties() as $property) {
            $propertyName = $property->getName();
            $valueName    = $propertyName;
            $type         = $property->getType();

            if ($property->isDefault() && !isset($values[$propertyName]) && isset($values['value'])) {
                $valueName = 'value';
            }

            // handle a not given attribute or null value
            if (! isset($values[$valueName])) {
                if ($property->isRequired()) {
                    throw AnnotationException::requiredError($propertyName, $originalName, $this->context, 'a(n) ' . $type->describe());
                }

                continue;
            }

            if ($type instanceof ArrayType) {
                // handle the case of a single value
                if ( ! is_array($values[$valueName])) {
                    $values[$valueName] = [$values[$valueName]];
                }

                $valueType = $type->getValueType();

                if (! $type->validate($values[$valueName])) {
                    $firstInvalidValue = (static function (array $values) use ($valueType) {
                        foreach ($values as $value) {
                            if ($valueType->validate($value)) {
                                continue;
                            }

                            return $value;
                        }
                    })($values[$valueName]);

                    throw AnnotationException::attributeTypeError($propertyName, $originalName, $this->context, 'either a(n) ' . $type->getValueType()->describe() . ', or an array of ' . $type->getValueType()->describe() . 's', $firstInvalidValue);
                }
            } elseif (! $type->validate($values[$valueName])) {
                throw AnnotationException::attributeTypeError($propertyName, $originalName, $this->context, 'a(n) '.$type->describe(), $values[$valueName]);
            }
        }

        // check if the annotation expects values via the constructor,
        // or directly injected into public properties
        if ($metadata->usesConstructor()) {
            return new $name($values);
        }

        $instance = new $name();

        foreach ($values as $property => $value) {
            if (! isset($metadata->getProperties()[$property])) {
                if ('value' !== $property) {
                    throw AnnotationException::creationError(
                        sprintf(
                            'The annotation @%s declared on %s does not have a property named "%s". Available properties: %s',
                            $originalName,
                            $this->context,
                            $property,
                            implode(', ', array_keys($metadata->getProperties()))
                        )
                    );
                }

                $defaultProperty = $metadata->getDefaultProperty();

                // handle the case if the property has no annotations
                if ($defaultProperty === null) {
                    throw AnnotationException::creationError(sprintf('The annotation @%s declared on %s does not accept any values, but got %s.', $originalName, $this->context, json_encode($values)));
                }

                $property = $defaultProperty->getName();
            }

            $instance->{$property} = $value;
        }

        return $instance;
    }

    /**
     * MethodCall ::= ["(" [Values] ")"]
     *
     * @return array
     */
    private function MethodCall()
    {
        $values = [];

        if ( ! $this->lexer->isNextToken(DocLexer::T_OPEN_PARENTHESIS)) {
            return $values;
        }

        $this->match(DocLexer::T_OPEN_PARENTHESIS);

        if ( ! $this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
            $values = $this->Values();
        }

        $this->match(DocLexer::T_CLOSE_PARENTHESIS);

        return $values;
    }

    /**
     * Values ::= Array | Value {"," Value}* [","]
     *
     * @return array
     */
    private function Values()
    {
        $values = [$this->Value()];

        while ($this->lexer->isNextToken(DocLexer::T_COMMA)) {
            $this->match(DocLexer::T_COMMA);

            if ($this->lexer->isNextToken(DocLexer::T_CLOSE_PARENTHESIS)) {
                break;
            }

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
                    $values['value'] = [$values['value']];
                }

                $values['value'][] = $value;
            }

            unset($values[$k]);
        }

        return $values;
    }

    /**
     * Constant ::= integer | string | float | boolean
     *
     * @return mixed
     *
     * @throws AnnotationException
     */
    private function Constant()
    {
        $identifier = $this->Identifier();

        if ( ! defined($identifier) && false !== strpos($identifier, '::') && '\\' !== $identifier[0]) {
            list($className, $const) = explode('::', $identifier);

            $pos = strpos($className, '\\');
            $alias = (false === $pos) ? $className : substr($className, 0, $pos);
            $found = false;
            $loweredAlias = strtolower($alias);

            switch (true) {
                case !empty ($this->namespaces):
                    foreach ($this->namespaces as $ns) {
                        if (class_exists($ns.'\\'.$className) || interface_exists($ns.'\\'.$className)) {
                             $className = $ns.'\\'.$className;
                             $found = true;
                             break;
                        }
                    }
                    break;

                case isset($this->imports[$loweredAlias]):
                    $found     = true;
                    $className = (false !== $pos)
                        ? $this->imports[$loweredAlias] . substr($className, $pos)
                        : $this->imports[$loweredAlias];
                    break;

                default:
                    if(isset($this->imports['__NAMESPACE__'])) {
                        $ns = $this->imports['__NAMESPACE__'];

                        if (class_exists($ns.'\\'.$className) || interface_exists($ns.'\\'.$className)) {
                            $className = $ns.'\\'.$className;
                            $found = true;
                        }
                    }
                    break;
            }

            if ($found) {
                 $identifier = $className . '::' . $const;
            }
        }

        // checks if identifier ends with ::class, \strlen('::class') === 7
        $classPos = stripos($identifier, '::class');
        if ($classPos === strlen($identifier) - 7) {
            return substr($identifier, 0, $classPos);
        }

        if (!defined($identifier)) {
            throw AnnotationException::semanticalErrorConstants($identifier, $this->context);
        }

        return constant($identifier);
    }

    /**
     * Identifier ::= string
     *
     * @return string
     */
    private function Identifier()
    {
        // check if we have an annotation
        if ( ! $this->lexer->isNextTokenAny(self::$classIdentifiers)) {
            $this->syntaxError('namespace separator or identifier');
        }

        $this->lexer->moveNext();

        $className = $this->lexer->token['value'];

        while ($this->lexer->lookahead['position'] === ($this->lexer->token['position'] + strlen($this->lexer->token['value']))
                && $this->lexer->isNextToken(DocLexer::T_NAMESPACE_SEPARATOR)) {

            $this->match(DocLexer::T_NAMESPACE_SEPARATOR);
            $this->matchAny(self::$classIdentifiers);

            $className .= '\\' . $this->lexer->token['value'];
        }

        return $className;
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

        if ($this->lexer->isNextToken(DocLexer::T_IDENTIFIER)) {
            return $this->Constant();
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
     * @return \stdClass
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
        $array = $values = [];

        $this->match(DocLexer::T_OPEN_CURLY_BRACES);

        // If the array is empty, stop parsing and return.
        if ($this->lexer->isNextToken(DocLexer::T_CLOSE_CURLY_BRACES)) {
            $this->match(DocLexer::T_CLOSE_CURLY_BRACES);

            return $array;
        }

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
     * KeyValuePair ::= Key ("=" | ":") PlainValue | Constant
     * Key ::= string | integer | Constant
     *
     * @return array
     */
    private function ArrayEntry()
    {
        $peek = $this->lexer->glimpse();

        if (DocLexer::T_EQUALS === $peek['type']
                || DocLexer::T_COLON === $peek['type']) {

            if ($this->lexer->isNextToken(DocLexer::T_IDENTIFIER)) {
                $key = $this->Constant();
            } else {
                $this->matchAny([DocLexer::T_INTEGER, DocLexer::T_STRING]);
                $key = $this->lexer->token['value'];
            }

            $this->matchAny([DocLexer::T_EQUALS, DocLexer::T_COLON]);

            return [$key, $this->PlainValue()];
        }

        return [null, $this->Value()];
    }

    /**
     * Checks whether the given $name matches any ignored annotation name or namespace
     *
     * @param string $name
     *
     * @return bool
     */
    private function isIgnoredAnnotation($name)
    {
        if ($this->ignoreNotImportedAnnotations || isset($this->ignoredAnnotationNames[$name])) {
            return true;
        }

        foreach (array_keys($this->ignoredAnnotationNamespaces) as $ignoredAnnotationNamespace) {
            $ignoredAnnotationNamespace = rtrim($ignoredAnnotationNamespace, '\\') . '\\';

            if (0 === stripos(rtrim($name, '\\') . '\\', $ignoredAnnotationNamespace)) {
                return true;
            }
        }

        return false;
    }
}
