<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Traits;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\Autoload as In;

trait TraitThatUsesAnotherTrait
{
    use EmptyTrait;
    use SecretRouteTrait;
    use ConflictTraitA, ConflictTraitB, ConflictTraitC {
        ConflictTraitA::conflict insteadof ConflictTraitB;
        ConflictTraitA::conflict insteadOf ConflictTraitC;
        ConflictTraitC::conflict as protected noConflict;
    }

    /**
     * @In
     */
    private $intermediate;
}
