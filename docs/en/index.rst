Introduction
============

Doctrine Annotations offers to implement custom annotation
functionality for PHP classes.

.. code-block:: php

    class Foo
    {
        /**
         * @MyAnnotation(myProperty="value")
         */
        private $bar;
    }

Annotations aren't implemented in PHP itself which is why
this component offers a way to use the PHP doc-blocks as a
place for the well known annotation syntax using the
``@`` char.

Annotations in Doctrine are used for the ORM
configuration to build the class mapping, but it can
be used in other projects for other purposes too.

Installation
============

You can install the Annotation component with composer:

.. code-block::

    $ composer require doctrine/annotation

Create an annotation class
==========================

An annotation class is a representation of the later
used annotation configuration in classes. The annotation
class of the previous example looks like this:

.. code-block:: php

    /**
     * @Annotation
     */
    final class MyAnnotation
    {
        public $myProperty;
    }

The annotation class is declared as an annotation by
``@Annotation``.

Reading annotations
===================

The access to the annotations happens by reflection of the class
containing them. There are multiple reader-classes implementing the
``Doctrine\Common\Annotations\Reader`` interface, that can
access the annotations of a class. A common one is
``Doctrine\Common\Annotations\AnnotationReader``:

.. code-block:: php

    $reflectionClass = new ReflectionClass(Foo::class);
    $property = $reflectionClass->getProperty('bar');

    $reader = new AnnotationReader();
    $myAnnotation = $reader->getPropertyAnnotation($property, 'bar');

    echo $myAnnotation->myProperty; // result: "value"

A reader has multiple methods to access the annotations
of a class.

Reader API
----------

Access all annotations of a class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getClassAnnotations(\ReflectionClass $class);

Access one annotation of a class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getClassAnnotation(\ReflectionClass $class, $annotationName);

Access all annotations of a method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getMethodAnnotations(\ReflectionMethod $method);

Access one annotation of a method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName);

Access all annotations of a property
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getPropertyAnnotations(\ReflectionProperty $property);

Access one annotation of a property
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName);