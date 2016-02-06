<?php

// namespace Doctrine\AnnotationsTests\Fixtures;
namespace Doctrine\AnnotationsTests\Fixtures\Foo {

    use Doctrine\AnnotationsTests\Fixtures\Annotation\Secure;

    // class NamespaceAndClassCommentedOut {}
}

namespace Doctrine\AnnotationsTests\Fixtures {

    // class NamespaceAndClassCommentedOut {}
    use Doctrine\AnnotationsTests\Fixtures\Annotation\Route;

    // namespace Doctrine\AnnotationsTests\Fixtures;
    use Doctrine\AnnotationsTests\Fixtures\Annotation\Template;

    class NamespaceAndClassCommentedOut {}
}