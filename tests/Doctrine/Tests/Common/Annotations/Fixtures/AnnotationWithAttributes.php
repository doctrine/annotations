<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll as RenamedAnnotationTargetAll;
use Doctrine\Tests\Common;
use Doctrine\Tests\Common as RenamedCommon;

/**
 * @Annotation
 * @Target("ALL")
 * @Attributes({
      @Attribute("mixed",                    type = "mixed"),
      @Attribute("boolean",                  type = "boolean"),
      @Attribute("bool",                     type = "bool"),
      @Attribute("float",                    type = "float"),
      @Attribute("string",                   type = "string"),
      @Attribute("integer",                  type = "integer"),
      @Attribute("array",                    type = "array"),
      @Attribute("unqualifiedAnnotation",    type = "AnnotationTargetAll"),
      @Attribute("renamedAnnotation",        type = "RenamedAnnotationTargetAll"),
      @Attribute("partiallyNamedAnnotation", type = "Common\Annotations\Fixtures\AnnotationTargetAll"),
      @Attribute("partiallyNamedAndRenamedAnnotation",    type = "RenamedCommon\Annotations\Fixtures\AnnotationTargetAll"),
      @Attribute("arrayOfIntegers",          type = "array<integer>"),
      @Attribute("arrayOfStrings",           type = "string[]"),
      @Attribute("annotation",               type = "Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll"),
      @Attribute("arrayOfAnnotations",       type = "array<Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll>"),
      @Attribute(
          "arrayOfUnqualifiedAnnotations",
          type = "array<AnnotationTargetAll>"
      ),
      @Attribute(
          "arrayOfRenamedAnnotations",
          type = "array<RenamedAnnotationTargetAll>"
      ),
      @Attribute(
          "arrayOfPartiallyNamedAnnotations",
          type = "array<Common\Annotations\Fixtures\AnnotationTargetAll>"
      ),
      @Attribute(
          "arrayOfPartiallyNamedAndRenamedAnnotations",
          type = "array<RenamedCommon\Annotations\Fixtures\AnnotationTargetAll>"
      ),
  })
 */
final class AnnotationWithAttributes
{

    public final function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    private $mixed;
    private $boolean;
    private $bool;
    private $float;
    private $string;
    private $integer;
    private $array;
    private $annotation;
    private $unqualifiedAnnotation;
    private $renamedAnnotation;
    private $partiallyNamedAnnotation;
    private $partiallyNamedAndRenamedAnnotation;
    private $arrayOfIntegers;
    private $arrayOfStrings;
    private $arrayOfAnnotations;
    private $arrayOfUnqualifiedAnnotations;
    private $arrayOfRenamedAnnotations;
    private $arrayOfPartiallyNamedAnnotations;
    private $arrayOfPartiallyNamedAndRenamedAnnotations;

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

    public function getInteger()
    {
        return $this->integer;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * @return \Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll
     */
    public function getAnnotation()
    {
        return $this->annotation;
    }

    /**
     * @return AnnotationTargetAll
     */
    public function getUnqualifiedAnnotation()
    {
        return $this->unqualifiedAnnotation;
    }

    /**
     * @return RenamedAnnotationTargetAll
     */
    public function getRenamedAnnotation()
    {
        return $this->renamedAnnotation;
    }

    /**
     * @return Common\Annotations\Fixtures\AnnotationTargetAll
     */
    public function getPartiallyNamedAnnotation()
    {
        return $this->partiallyNamedAnnotation;
    }

    /**
     * @return RenamedCommon\Annotations\Fixtures\AnnotationTargetAll
     */
    public function getPartiallyNamedAndRenamedAnnotation()
    {
        return $this->partiallyNamedAndRenamedAnnotation;
    }

    /**
     * @return string[]
     */
    public function getArrayOfStrings()
    {
        return $this->arrayOfIntegers;
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

    /**
     * @return array<AnnotationTargetAll>
     */
    public function getArrayOfUnqualifiedAnnotations()
    {
        return $this->arrayOfUnqualifiedAnnotations;
    }

    /**
     * @return array<RenamedAnnotationTargetAll>
     */
    public function getArrayOfRenamedAnnotations()
    {
        return $this->arrayOfRenamedAnnotations;
    }

    /**
     * @return array<Common\Annotations\Fixtures\AnnotationTargetAll>
     */
    public function getArrayOfPartiallyNamedAnnotations()
    {
        return $this->arrayOfPartiallyNamedAnnotations;
    }

    /**
     * @return array<RenamedCommon\Annotations\Fixtures\AnnotationTargetAll>
     */
    public function getArrayOfPartiallyNamedAndRenamedAnnotations()
    {
        return $this->arrayOfPartiallyNamedAndRenamedAnnotations;
    }

}
