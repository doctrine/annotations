<?php

// namespace Doctrine\Tests\Annotations\Fixtures;
namespace Doctrine\Tests\Annotations\Fixtures\Foo {

    use Doctrine\Tests\Annotations\Fixtures\Annotation\Secure;

    // class NamespaceAndClassCommentedOut {}
}

namespace Doctrine\Tests\Annotations\Fixtures {

    // class NamespaceAndClassCommentedOut {}
    use Doctrine\Tests\Annotations\Fixtures\Annotation\Route;

    // namespace Doctrine\Tests\Annotations\Fixtures;
    use Doctrine\Tests\Annotations\Fixtures\Annotation\Template;

    class NamespaceAndClassCommentedOut {}
}
