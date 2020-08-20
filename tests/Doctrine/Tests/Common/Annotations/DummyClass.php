<?php

namespace Doctrine\Tests\Common\Annotations;

/**
 * A description of this class.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 *
 * @DummyAnnotation(dummyValue="hello")
 */
class DummyClass
{
    /**
     * A nice property.
     *
     * @var mixed
     * @DummyAnnotation(dummyValue="fieldHello")
     */
    public $field1;

    /**
     * @var mixed;
     * @DummyJoinTable(name="join_table",
     *      joinColumns={@DummyJoinColumn(name="col1", referencedColumnName="col2")},
     *      inverseJoinColumns={
     *          @DummyJoinColumn(name="col3", referencedColumnName="col4")
     *      })
     */
    public $field2;

    /**
     * Gets the value of field1.
     *
     * @return mixed
     *
     * @DummyAnnotation({1,2,"three"})
     */
    public function getField1()
    {
    }

    /**
     * A parameter value with a space in it.
     *
     * @DummyAnnotation("\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d")
     */
    public function getField3(): void
    {
    }
}
