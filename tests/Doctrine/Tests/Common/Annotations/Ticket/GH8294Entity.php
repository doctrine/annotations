<?php

namespace Doctrine\Tests\Common\Annotations\Ticket;

use Doctrine\Tests\Common\Annotations\DummyIndex;
use Doctrine\Tests\Common\Annotations\DummyTable;

/**
 * @DummyTable(name="GH8294_entity", {@DummyIndex(name="name_idx", columns={"name"})})
 */
class GH8294Entity
{
}
