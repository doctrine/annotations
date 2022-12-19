# Upgrade from 1.0.x to 2.0.x

- The `NamedArgumentConstructorAnnotation` has been removed. Use the `@NamedArgumentConstructor`
  annotation instead.
- `SimpleAnnotationReader` has been removed.
- `DocLexer::peek()` and `DocLexer::glimpse` now return
`Doctrine\Common\Lexer\Token` objects. When using `doctrine/lexer` 2, these
implement `ArrayAccess` as a way for you to still be able to treat them as
arrays in some ways.
- `CachedReader` and `FileCacheReader` have been removed.
- `AnnotationRegistry` methods related to registering annotations instead of
  using autoloading have been removed.
- Parameter type declarations have been added to all methods of all classes. If
you have classes inheriting from classes inside this package, you should add
parameter and return type declarations.
- Support for PHP < 7.2 has been removed
- `PhpParser::parseClass()` has been removed. Use
  `PhpParser::parseUseStatements()` instead.
