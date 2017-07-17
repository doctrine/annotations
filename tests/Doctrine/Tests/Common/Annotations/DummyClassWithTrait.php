<?php

namespace Doctrine\Tests\Common\Annotations;

/**
 * This class has no annotation by itself but it should get
 * all those from its trait
 */
class DummyClassWithTrait
{
    use DummyTrait;
}
