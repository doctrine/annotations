<?php

namespace Doctrine\Tests\Annotations;

use Doctrine\Tests\Annotations\DummyAnnotation;
use Doctrine\Tests\Annotations\Name;
use Doctrine\Tests\Annotations\DummyJoinTable;
use Doctrine\Tests\Annotations\DummyJoinColumn;

/**
 * A description of this class.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 *
 * @author robo
 * @since 2.0
 * @DummyAnnotation(dummyValue="hello")
 */
class DummyClass
{
    /**
     * A nice constant.
     *
     * @DummyAnnotation(dummyValue="constantHello")
     */
    const SOME_CONSTANT = "foo";

    /**
     * A nice property.
     *
     * @var mixed
     * @DummyAnnotation(dummyValue="fieldHello")
     */
    private $field1;

    /**
     * @DummyJoinTable(name="join_table",
     *      joinColumns={@DummyJoinColumn(name="col1", referencedColumnName="col2")},
     *      inverseJoinColumns={
     *          @DummyJoinColumn(name="col3", referencedColumnName="col4")
     *      })
     */
    private $field2;

    /**
     * Gets the value of field1.
     *
     * @return mixed
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
    public function getField3()
    {
    }
}
