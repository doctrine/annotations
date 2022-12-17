Deprecation notice
==================

PHP 8 introduced `attributes
<https://www.php.net/manual/en/language.attributes.overview.php>`_,
which are a native replacement for annotations. As such, this library is
considered feature complete, and should receive exclusively bugfixes and
security fixes.

Introduction
============

Doctrine Annotations allows to implement custom annotation
functionality for PHP classes and functions.

.. code-block:: php

    class Foo
    {
        /**
         * @MyAnnotation(myProperty="value")
         */
        private $bar;
    }

Annotations aren't implemented in PHP itself which is why this component
offers a way to use the PHP doc-blocks as a place for the well known
annotation syntax using the ``@`` char.

Annotations in Doctrine are used for the ORM configuration to build the
class mapping, but it can be used in other projects for other purposes
too.

Installation
============

You can install the Annotation component with composer:

.. code-block::

    $ composer require doctrine/annotations

Create an annotation class
==========================

An annotation class is a representation of the later used annotation
configuration in classes. The annotation class of the previous example
looks like this:

.. code-block:: php

    /**
     * @Annotation
     */
    final class MyAnnotation
    {
        public $myProperty;
    }

The annotation class is declared as an annotation by ``@Annotation``.

:ref:`Read more about custom annotations. <custom>`

Reading annotations
===================

The access to the annotations happens by reflection of the class or function
containing them. There are multiple reader-classes implementing the
``Doctrine\Common\Annotations\Reader`` interface, that can access the
annotations of a class. A common one is
``Doctrine\Common\Annotations\AnnotationReader``:

.. code-block:: php

    use Doctrine\Common\Annotations\AnnotationReader;

    $reflectionClass = new ReflectionClass(Foo::class);
    $property = $reflectionClass->getProperty('bar');

    $reader = new AnnotationReader();
    $myAnnotation = $reader->getPropertyAnnotation(
        $property,
        MyAnnotation::class
    );

    echo $myAnnotation->myProperty; // result: "value"

A reader has multiple methods to access the annotations of a class or
function.

:ref:`Read more about handling annotations. <annotations>`

IDE Support
-----------

Some IDEs already provide support for annotations:

- Eclipse via the `Symfony2 Plugin <https://github.com/pulse00/Symfony-2-Eclipse-Plugin>`_
- PhpStorm via the `PHP Annotations Plugin <https://plugins.jetbrains.com/plugin/7320-php-annotations>`_ or the `Symfony Plugin <https://plugins.jetbrains.com/plugin/7219-symfony-support>`_

.. _Read more about handling annotations.: annotations
.. _Read more about custom annotations.: custom
