<?php

namespace Doctrine\Tests\Annotations\Fixtures\Foo {
    use Doctrine\Tests\Annotations\Fixtures\Annotation\Secure;
}

namespace {
    use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;
}

namespace Doctrine\Tests\Annotations\Fixtures {
    use Doctrine\Tests\Annotations\Fixtures\Annotation\Template;

    class DifferentNamespacesPerFileWithClassAsLast {}
}
