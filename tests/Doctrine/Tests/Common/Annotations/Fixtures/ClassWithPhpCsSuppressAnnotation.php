<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

class ClassWithPhpCsSuppressAnnotation
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function foo($parameterWithoutTypehint) {
    }
}
