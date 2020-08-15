<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithPhpCsSuppressAnnotation
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     */
    public function foo($parameterWithoutTypehint): void
    {
    }
}
