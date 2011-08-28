<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

/**
 * @Annotation
 * @Target("ALL")
 * @Attributes({
     @Attribute("mixed",                type = "mixed"),
     @Attribute("boolean",              type = "boolean"),
     @Attribute("bool",                 type = "bool"),
     @Attribute("float",                type = "float"),
     @Attribute("string",               type = "string"),
     @Attribute("integer",              type = "integer"),
     @Attribute("array",                type = "array"),
     @Attribute("annotation",           type = "annotation"),
     @Attribute("arrayOfIntegers",      type = "array"),
     @Attribute("arrayOfAnnotations",   type = "annotation"),
   })
 */
final class AnnotationWithAttributes
{

    /**
     * @var mixed
     */
    public $mixed;

    /**
     * @var boolean
     */
    public $boolean;

    /**
     * @var bool
     */
    public $bool;

    /**
     * @var float
     */
    public $float;

    /**
     * @var string
     */
    public $string;

    /**
     * @var integer
     */
    public $integer;

    /**
     * @var array
     */
    public $array;

    /**
     * @var Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll
     */
    public $annotation;
    
    /**
     * @var array<integer>
     */
    public $arrayOfIntegers;
    
    /**
     * @var array<Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll>
     */
    public $arrayOfAnnotations;

}