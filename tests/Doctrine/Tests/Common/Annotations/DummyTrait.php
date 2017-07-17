<?php

namespace Doctrine\Tests\Common\Annotations;

use Doctrine\Tests\Common\Annotations\DummyAnnotation;
use Doctrine\Tests\Common\Annotations\Name;
use Doctrine\Tests\Common\Annotations\DummyJoinTable;
use Doctrine\Tests\Common\Annotations\DummyJoinColumn;

/**
 * A description of this trait.
 *
 * Let's see if the parser recognizes that this @ is not really referring to an
 * annotation. Also make sure that @var \ is not concated to "@var\is".
 *
 * @author robo
 * @since 2.0
 * @DummyAnnotation(dummyValue="hello")
 */
Trait DummyTrait
{
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
