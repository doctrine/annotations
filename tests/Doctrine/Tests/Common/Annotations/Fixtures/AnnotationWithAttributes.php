<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;

/**
 * @Annotation
 * @Target("ALL")
 * @Attributes({
      @Attribute("mixed",              type = "mixed"),
      @Attribute("boolean",            type = "boolean"),
      @Attribute("bool",               type = "bool"),
      @Attribute("float",              type = "float"),
      @Attribute("string",             type = "string"),
      @Attribute("integer",            type = "integer"),
      @Attribute("array",              type = "array"),
      @Attribute("arrayOfIntegers",    type = "array<integer>"),
      @Attribute("arrayOfStrings",     type = "string[]"),
      @Attribute("annotation",         type = "Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll"),
      @Attribute("arrayOfAnnotations", type = "array<Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll>"),
  })
 */
final class AnnotationWithAttributes
{
    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /** @var mixed */
    private $mixed;
    /** @var bool */
    private $boolean;
    /** @var bool */
    private $bool;
    /** @var float */
    private $float;
    /** @var string */
    private $string;
    /** @var integer */
    private $integer;
    /** @var mixed[] */
    private $array;
    /** @var object */
    private $annotation;
    /** @var int[] */
    private $arrayOfIntegers;
    /** @var string[] */
    private $arrayOfStrings;
    /** @var object[] */
    private $arrayOfAnnotations;

    /**
     * @return mixed
     */
    public function getMixed()
    {
        return $this->mixed;
    }

    /**
     * @return boolean
     */
    public function getBoolean()
    {
        return $this->boolean;
    }

    /**
     * @return bool
     */
    public function getBool()
    {
        return $this->bool;
    }

    /**
     * @return float
     */
    public function getFloat()
    {
        return $this->float;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    public function getInteger(): int
    {
        return $this->integer;
    }

    /**
     * @return mixed[]
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * @return AnnotationTargetAll
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * @return string[]
     */
    public function getArrayOfStrings()
    {
        return $this->arrayOfStrings;
    }

    /**
     * @return array<integer>
     */
    public function getArrayOfIntegers()
    {
        return $this->arrayOfIntegers;
    }

    /**
     * @return array<Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll>
     */
    public function getArrayOfAnnotations()
    {
        return $this->arrayOfAnnotations;
    }
}
