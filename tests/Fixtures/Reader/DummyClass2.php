<?php

namespace Doctrine\AnnotationsTests\Fixtures\Reader;

/**
 * @ignoreAnnotation({"var"})
 */
class DummyClass2 {
    /**
     * @DummyId @DummyColumn(type="integer") @DummyGeneratedValue
     * @var integer
     */
    private $id;
}