<?php

namespace Doctrine\Tests\Annotations\Fixtures;

class ClassWithPhpCsSuppressAnnotation
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function foo($parameterWithoutTypehint) {
    }
}
