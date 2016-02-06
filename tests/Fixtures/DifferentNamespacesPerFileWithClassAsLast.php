<?php

namespace Doctrine\AnnotationsTests\Fixtures\Foo {
    use Doctrine\AnnotationsTests\Fixtures\Annotation\Secure;
}

namespace {
    use Doctrine\AnnotationsTests\Fixtures\Annotation\Route;
}

namespace Doctrine\AnnotationsTests\Fixtures {
    use Doctrine\AnnotationsTests\Fixtures\Annotation\Template;

    class DifferentNamespacesPerFileWithClassAsLast {}
}